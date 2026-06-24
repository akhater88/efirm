<?php

use App\Models\Subscription;
use App\Services\StripeSyncService;

describe('StripeSyncService', function () {
    it('can be instantiated from the container', function () {
        $service = app(StripeSyncService::class);

        expect($service)->toBeInstanceOf(StripeSyncService::class);
    });

    it('reads stripe config values correctly', function () {
        config([
            'services.stripe.secret' => 'sk_test_fake',
            'services.stripe.api_version' => '2025-08-27.acacia',
        ]);

        expect(config('services.stripe.secret'))->toBe('sk_test_fake');
        expect(config('services.stripe.api_version'))->toBe('2025-08-27.acacia');
        expect(config('services.stripe.webhook_secret'))->toBeNull();
    });

    it('returns existing stripe_customer_id without making API calls', function () {
        $subscription = Subscription::factory()
            ->withStripeCustomer('cus_existing123')
            ->create();

        $workspace = $subscription->workspace;

        $service = app(StripeSyncService::class);

        $customerId = $service->ensureCustomer($workspace);

        expect($customerId)->toBe('cus_existing123');
    });

    it('throws when creating stripe subscription without customer id', function () {
        $subscription = Subscription::factory()->create([
            'stripe_customer_id' => null,
        ]);

        $service = app(StripeSyncService::class);

        expect(fn () => $service->createSubscription($subscription))
            ->toThrow(RuntimeException::class, 'stripe_customer_id');
    });

    it('throws when updating seats with invalid count', function () {
        $subscription = Subscription::factory()
            ->active()
            ->withStripeCustomer()
            ->withStripeSubscription()
            ->create(['seat_count' => 3]);

        $service = app(StripeSyncService::class);

        expect(fn () => $service->updateSeats($subscription, 0))
            ->toThrow(InvalidArgumentException::class, 'at least 1');
    });

    it('throws when cancelling subscription without stripe_subscription_id', function () {
        $subscription = Subscription::factory()
            ->active()
            ->withStripeCustomer()
            ->create(['stripe_subscription_id' => null]);

        $service = app(StripeSyncService::class);

        expect(fn () => $service->cancelSubscription($subscription))
            ->toThrow(RuntimeException::class, 'no stripe_subscription_id');
    });

    it('throws when updating seats without stripe_subscription_id', function () {
        $subscription = Subscription::factory()
            ->active()
            ->withStripeCustomer()
            ->create([
                'seat_count' => 3,
                'stripe_subscription_id' => null,
            ]);

        $service = app(StripeSyncService::class);

        expect(fn () => $service->updateSeats($subscription, 5))
            ->toThrow(RuntimeException::class, 'no stripe_subscription_id');
    });
});
