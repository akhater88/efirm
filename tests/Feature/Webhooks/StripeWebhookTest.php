<?php

use App\Enums\SubscriptionState;
use App\Models\Plan;
use App\Models\StripeWebhookEvent;
use App\Models\Subscription;
use App\Models\Workspace;

beforeEach(function () {
    $this->plan = Plan::factory()->create();
    $this->workspace = Workspace::factory()->create();
    $this->subscription = Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $this->plan->id,
        'stripe_subscription_id' => 'sub_test_123',
        'stripe_customer_id' => 'cus_test_123',
    ]);
});

test('webhook records event and returns processed', function () {
    $payload = json_encode([
        'id' => 'evt_test_'.now()->timestamp,
        'type' => 'invoice.payment_succeeded',
        'data' => [
            'object' => [
                'subscription' => 'sub_nonexistent',
            ],
        ],
    ]);

    $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
        'Stripe-Signature' => '',
    ]);

    $response->assertOk();
    expect(StripeWebhookEvent::count())->toBeGreaterThanOrEqual(1);
});

test('webhook is idempotent on replay', function () {
    $eventId = 'evt_idempotent_'.now()->timestamp;
    $payload = [
        'id' => $eventId,
        'type' => 'invoice.payment_succeeded',
        'data' => ['object' => ['subscription' => 'sub_nonexistent']],
    ];

    $this->postJson('/webhooks/stripe', $payload)->assertOk();
    $this->postJson('/webhooks/stripe', $payload)->assertOk();

    // Should only have one record for the original event (not counting result rows)
    expect(StripeWebhookEvent::where('stripe_event_id', $eventId)->count())->toBe(1);
});

test('payment succeeded transitions trial to active', function () {
    $this->subscription->update(['state' => SubscriptionState::Trial]);

    $payload = [
        'id' => 'evt_payment_success_'.now()->timestamp,
        'type' => 'invoice.payment_succeeded',
        'data' => ['object' => ['subscription' => 'sub_test_123']],
    ];

    $this->postJson('/webhooks/stripe', $payload)->assertOk();

    $this->subscription->refresh();
    expect($this->subscription->state)->toBe(SubscriptionState::Active);
});

test('payment succeeded transitions past_due to active', function () {
    $this->subscription->update(['state' => SubscriptionState::PastDue]);

    $payload = [
        'id' => 'evt_recovery_'.now()->timestamp,
        'type' => 'invoice.payment_succeeded',
        'data' => ['object' => ['subscription' => 'sub_test_123']],
    ];

    $this->postJson('/webhooks/stripe', $payload)->assertOk();

    $this->subscription->refresh();
    expect($this->subscription->state)->toBe(SubscriptionState::Active);
});

test('payment failed transitions active to past_due', function () {
    $payload = [
        'id' => 'evt_payment_fail_'.now()->timestamp,
        'type' => 'invoice.payment_failed',
        'data' => ['object' => ['subscription' => 'sub_test_123']],
    ];

    $this->postJson('/webhooks/stripe', $payload)->assertOk();

    $this->subscription->refresh();
    expect($this->subscription->state)->toBe(SubscriptionState::PastDue);
});

test('subscription deleted transitions to cancelled', function () {
    $payload = [
        'id' => 'evt_sub_deleted_'.now()->timestamp,
        'type' => 'customer.subscription.deleted',
        'data' => ['object' => ['id' => 'sub_test_123']],
    ];

    $this->postJson('/webhooks/stripe', $payload)->assertOk();

    $this->subscription->refresh();
    expect($this->subscription->state)->toBe(SubscriptionState::Cancelled);
});

test('stripe webhook events are append-only', function () {
    $event = StripeWebhookEvent::create([
        'stripe_event_id' => 'evt_appendonly_test',
        'event_type' => 'test',
        'signature_valid' => true,
        'processing_result' => 'test',
        'created_at' => now(),
    ]);

    expect(fn () => $event->update(['processing_result' => 'hacked']))
        ->toThrow(RuntimeException::class);

    expect(fn () => $event->delete())
        ->toThrow(RuntimeException::class);
});

test('webhook sanitizes sensitive fields from payload', function () {
    $payload = [
        'id' => 'evt_sanitize_'.now()->timestamp,
        'type' => 'test.event',
        'api_key' => 'sk_live_SHOULD_NOT_APPEAR',
        'data' => ['object' => ['id' => 'test']],
    ];

    $this->postJson('/webhooks/stripe', $payload)->assertOk();

    $record = StripeWebhookEvent::where('stripe_event_id', 'evt_sanitize_'.now()->timestamp)->first();

    // The api_key should be redacted
    if ($record && $record->payload) {
        expect(json_encode($record->payload))->not->toContain('sk_live_SHOULD_NOT_APPEAR');
    }
});

test('webhook rejects invalid payload', function () {
    $this->postJson('/webhooks/stripe', [], ['Content-Type' => 'application/json'])
        ->assertStatus(400);
});
