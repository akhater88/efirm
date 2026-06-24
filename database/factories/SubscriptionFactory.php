<?php

namespace Database\Factories;

use App\Enums\SubscriptionState;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'plan_id' => Plan::factory(),
            'state' => SubscriptionState::Trial,
            'seat_count' => 1,
            'stripe_customer_id' => null,
            'stripe_subscription_id' => null,
            'trial_ends_at' => now()->addDays(14),
            'grace_period_ends_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'state' => SubscriptionState::Active,
            'trial_ends_at' => null,
        ]);
    }

    public function withStripeCustomer(string $customerId = 'cus_test123'): static
    {
        return $this->state(fn () => [
            'stripe_customer_id' => $customerId,
        ]);
    }

    public function withStripeSubscription(string $subscriptionId = 'sub_test123'): static
    {
        return $this->state(fn () => [
            'stripe_subscription_id' => $subscriptionId,
        ]);
    }
}
