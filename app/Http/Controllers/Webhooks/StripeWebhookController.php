<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\SubscriptionState;
use App\Http\Controllers\Controller;
use App\Models\StripeWebhookEvent;
use App\Models\Subscription;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $webhookSecret = config('services.stripe.webhook_secret');

        // Verify signature
        $signatureValid = false;
        $event = null;

        try {
            if (empty($webhookSecret)) {
                // In dev/test without webhook secret, parse raw payload
                $event = json_decode($payload, false);
                $signatureValid = false;
            } else {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
                $signatureValid = true;
            }
        } catch (SignatureVerificationException) {
            $this->recordEvent($request, false, 'signature_failed');

            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\UnexpectedValueException) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $stripeEventId = $event->id ?? ($event->id ?? null);
        $eventType = $event->type ?? ($event->type ?? 'unknown');

        if (! $stripeEventId) {
            return response()->json(['error' => 'Missing event ID'], 400);
        }

        // Idempotency: record BEFORE any side effect
        $existing = StripeWebhookEvent::where('stripe_event_id', $stripeEventId)->first();

        if ($existing) {
            return response()->json(['status' => 'already_processed']);
        }

        // Sanitize payload — strip any sensitive fields
        $sanitizedPayload = $this->sanitizePayload((array) json_decode($payload, true));

        $webhookRecord = StripeWebhookEvent::create([
            'stripe_event_id' => $stripeEventId,
            'event_type' => $eventType,
            'signature_valid' => $signatureValid,
            'processing_result' => 'processing',
            'payload' => $sanitizedPayload,
            'created_at' => now(),
        ]);

        // Process the event
        try {
            $this->handleEvent($eventType, $event);

            // Record success via new row (append-only — can't update)
            StripeWebhookEvent::create([
                'stripe_event_id' => $stripeEventId.'_result',
                'event_type' => $eventType,
                'signature_valid' => $signatureValid,
                'processing_result' => 'success',
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            StripeWebhookEvent::create([
                'stripe_event_id' => $stripeEventId.'_result',
                'event_type' => $eventType,
                'signature_valid' => $signatureValid,
                'processing_result' => 'failed: '.$e->getMessage(),
                'created_at' => now(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }

        return response()->json(['status' => 'processed']);
    }

    private function handleEvent(string $eventType, object $event): void
    {
        $lifecycle = app(SubscriptionLifecycleService::class);

        match ($eventType) {
            'invoice.payment_succeeded' => $this->handlePaymentSucceeded($event, $lifecycle),
            'invoice.payment_failed' => $this->handlePaymentFailed($event, $lifecycle),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event, $lifecycle),
            default => null, // Unhandled events are silently ignored
        };
    }

    private function handlePaymentSucceeded(object $event, SubscriptionLifecycleService $lifecycle): void
    {
        $stripeSubscriptionId = $event->data->object->subscription ?? null;

        if (! $stripeSubscriptionId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();

        if (! $subscription) {
            return;
        }

        if ($subscription->state === SubscriptionState::Trial) {
            $lifecycle->transition($subscription, SubscriptionState::Active, payload: ['source' => 'stripe_webhook']);
        } elseif ($subscription->state === SubscriptionState::PastDue) {
            $lifecycle->transition($subscription, SubscriptionState::Active, payload: ['source' => 'stripe_webhook']);
        }
    }

    private function handlePaymentFailed(object $event, SubscriptionLifecycleService $lifecycle): void
    {
        $stripeSubscriptionId = $event->data->object->subscription ?? null;

        if (! $stripeSubscriptionId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();

        if (! $subscription || $subscription->state !== SubscriptionState::Active) {
            return;
        }

        $lifecycle->transition($subscription, SubscriptionState::PastDue, payload: ['source' => 'stripe_webhook']);
    }

    private function handleSubscriptionDeleted(object $event, SubscriptionLifecycleService $lifecycle): void
    {
        $stripeSubscriptionId = $event->data->object->id ?? null;

        if (! $stripeSubscriptionId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();

        if (! $subscription || $subscription->state === SubscriptionState::Cancelled) {
            return;
        }

        // Force transition through valid path
        if ($subscription->state === SubscriptionState::Active) {
            $subscription = $lifecycle->transition($subscription, SubscriptionState::PastDue, payload: ['source' => 'stripe_webhook']);
            $subscription = $lifecycle->transition($subscription, SubscriptionState::Suspended, payload: ['source' => 'stripe_webhook']);
        } elseif ($subscription->state === SubscriptionState::PastDue) {
            $subscription = $lifecycle->transition($subscription, SubscriptionState::Suspended, payload: ['source' => 'stripe_webhook']);
        }

        if ($subscription->state === SubscriptionState::Suspended) {
            $lifecycle->transition($subscription, SubscriptionState::Cancelled, payload: ['source' => 'stripe_webhook']);
        }
    }

    private function recordEvent(Request $request, bool $signatureValid, string $result): void
    {
        StripeWebhookEvent::create([
            'stripe_event_id' => 'sig_fail_'.now()->timestamp.'_'.substr(md5($request->getContent()), 0, 8),
            'event_type' => 'unknown',
            'signature_valid' => $signatureValid,
            'processing_result' => $result,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sanitizePayload(array $payload): array
    {
        $sensitiveKeys = ['api_key', 'secret', 'webhook_secret', 'password', 'token'];

        array_walk_recursive($payload, function (&$value, $key) use ($sensitiveKeys) {
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $value = '[REDACTED]';
            }
        });

        return $payload;
    }
}
