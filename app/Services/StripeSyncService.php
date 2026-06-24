<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Workspace;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeSyncService
{
    private ?StripeClient $stripe = null;

    private function client(): StripeClient
    {
        if ($this->stripe === null) {
            $secret = config('services.stripe.secret');

            if (empty($secret)) {
                throw new \RuntimeException('Stripe secret key is not configured. Set STRIPE_SECRET in .env.');
            }

            $this->stripe = new StripeClient([
                'api_key' => $secret,
                'stripe_version' => config('services.stripe.api_version'),
            ]);
        }

        return $this->stripe;
    }

    /**
     * Create or retrieve a Stripe customer for a workspace.
     *
     * If the subscription already has a stripe_customer_id, return it.
     * Otherwise, create a new Stripe customer and persist the ID.
     *
     * @return string The Stripe customer ID
     *
     * @throws \RuntimeException If the Stripe API call fails
     */
    public function ensureCustomer(Workspace $workspace): string
    {
        $subscription = $workspace->subscription;

        if ($subscription?->stripe_customer_id) {
            return $subscription->stripe_customer_id;
        }

        try {
            $customer = $this->client()->customers->create([
                'name' => $workspace->name,
                'metadata' => [
                    'workspace_id' => $workspace->id,
                ],
            ]);

            if ($subscription) {
                $subscription->update(['stripe_customer_id' => $customer->id]);
            }

            return $customer->id;
        } catch (ApiErrorException $e) {
            throw new \RuntimeException(
                'Failed to create Stripe customer for workspace: '.$e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Create a Stripe subscription for a workspace subscription.
     *
     * @return Subscription The updated subscription with stripe_subscription_id
     *
     * @throws \RuntimeException If the Stripe API call fails
     */
    public function createSubscription(Subscription $subscription): Subscription
    {
        if (! $subscription->stripe_customer_id) {
            throw new \RuntimeException(
                'Subscription must have a stripe_customer_id before creating a Stripe subscription. Call ensureCustomer() first.'
            );
        }

        $subscription->loadMissing('plan');

        $priceId = $this->resolvePriceId($subscription->plan->slug);

        try {
            $stripeSubscription = $this->client()->subscriptions->create([
                'customer' => $subscription->stripe_customer_id,
                'items' => [
                    [
                        'price' => $priceId,
                        'quantity' => $subscription->seat_count,
                    ],
                ],
                'metadata' => [
                    'workspace_id' => $subscription->workspace_id,
                    'subscription_id' => $subscription->id,
                ],
            ]);

            $subscription->update(['stripe_subscription_id' => $stripeSubscription->id]);

            return $subscription->fresh();
        } catch (ApiErrorException $e) {
            throw new \RuntimeException(
                'Failed to create Stripe subscription: '.$e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Update seat quantity on the Stripe subscription.
     *
     * @throws \RuntimeException If the Stripe API call fails or subscription is not synced
     */
    public function updateSeats(Subscription $subscription, int $newCount): void
    {
        if (! $subscription->stripe_subscription_id) {
            throw new \RuntimeException(
                'Cannot update seats: subscription has no stripe_subscription_id.'
            );
        }

        if ($newCount < 1) {
            throw new \InvalidArgumentException('Seat count must be at least 1.');
        }

        try {
            $stripeSubscription = $this->client()->subscriptions->retrieve(
                $subscription->stripe_subscription_id,
            );

            $itemId = $stripeSubscription->items->data[0]->id ?? null;

            if (! $itemId) {
                throw new \RuntimeException('No subscription item found on the Stripe subscription.');
            }

            $this->client()->subscriptionItems->update($itemId, [
                'quantity' => $newCount,
            ]);
        } catch (ApiErrorException $e) {
            throw new \RuntimeException(
                'Failed to update seat quantity on Stripe: '.$e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Cancel a Stripe subscription.
     *
     * @throws \RuntimeException If the Stripe API call fails or subscription is not synced
     */
    public function cancelSubscription(Subscription $subscription): void
    {
        if (! $subscription->stripe_subscription_id) {
            throw new \RuntimeException(
                'Cannot cancel: subscription has no stripe_subscription_id.'
            );
        }

        try {
            $this->client()->subscriptions->cancel(
                $subscription->stripe_subscription_id,
            );
        } catch (ApiErrorException $e) {
            throw new \RuntimeException(
                'Failed to cancel Stripe subscription: '.$e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Resolve the Stripe price ID for a given plan slug.
     *
     * Uses the STRIPE_PRICE_ID_PREFIX env var as a placeholder
     * until actual Stripe prices are created and mapped.
     */
    private function resolvePriceId(string $planSlug): string
    {
        $prefix = config('services.stripe.price_id_prefix', 'price');

        return $prefix.'_'.$planSlug;
    }
}
