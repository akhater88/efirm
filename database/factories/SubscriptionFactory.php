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

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'plan_id' => Plan::factory(),
            'state' => SubscriptionState::Active,
            'seat_count' => 1,
            'stripe_customer_id' => null,
            'stripe_subscription_id' => null,
            'trial_ends_at' => null,
            'grace_period_ends_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function trial(): static
    {
        return $this->state([
            'state' => SubscriptionState::Trial,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function active(): static
    {
        return $this->state([
            'state' => SubscriptionState::Active,
        ]);
    }

    public function pastDue(): static
    {
        return $this->state([
            'state' => SubscriptionState::PastDue,
            'grace_period_ends_at' => now()->addDays(7),
        ]);
    }

    public function suspended(): static
    {
        return $this->state([
            'state' => SubscriptionState::Suspended,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'state' => SubscriptionState::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function withStripeCustomer(string $customerId = 'cus_test123'): static
    {
        return $this->state([
            'stripe_customer_id' => $customerId,
        ]);
    }

    public function withStripeSubscription(string $subscriptionId = 'sub_test123'): static
    {
        return $this->state([
            'stripe_subscription_id' => $subscriptionId,
        ]);
    }
}
