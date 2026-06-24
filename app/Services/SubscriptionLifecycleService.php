<?php

namespace App\Services;

use App\Enums\SubscriptionEventType;
use App\Enums\SubscriptionState;
use App\Models\AdminUser;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionEvent;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class SubscriptionLifecycleService
{
    /**
     * Start a trial subscription for a workspace.
     */
    public function startTrial(Workspace $workspace, Plan $plan, ?AdminUser $admin = null): Subscription
    {
        return DB::transaction(function () use ($workspace, $plan, $admin) {
            $subscription = Subscription::create([
                'workspace_id' => $workspace->id,
                'plan_id' => $plan->id,
                'state' => SubscriptionState::Trial,
                'seat_count' => 1,
                'trial_ends_at' => now()->addDays(14),
            ]);

            $this->recordEvent($subscription, SubscriptionEventType::TrialStarted, null, SubscriptionState::Trial, $admin, [
                'plan_slug' => $plan->slug,
                'trial_days' => 14,
            ]);

            return $subscription;
        });
    }

    /**
     * Transition a subscription to a new state.
     */
    public function transition(Subscription $subscription, SubscriptionState $targetState, ?AdminUser $admin = null, array $payload = []): Subscription
    {
        if (! $subscription->state->canTransitionTo($targetState)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$subscription->state->value} to {$targetState->value}"
            );
        }

        return DB::transaction(function () use ($subscription, $targetState, $admin, $payload) {
            $fromState = $subscription->state;

            $subscription->update(['state' => $targetState]);

            $eventType = $this->resolveEventType($fromState, $targetState);

            // Set grace/cancellation timestamps
            if ($targetState === SubscriptionState::PastDue) {
                $subscription->update(['grace_period_ends_at' => now()->addDays(7)]);
            }

            if ($targetState === SubscriptionState::Cancelled) {
                $subscription->update(['cancelled_at' => now()]);
            }

            $this->recordEvent($subscription, $eventType, $fromState, $targetState, $admin, $payload);

            return $subscription->fresh();
        });
    }

    /**
     * Change the plan on a subscription.
     */
    public function changePlan(Subscription $subscription, Plan $newPlan, ?AdminUser $admin = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $newPlan, $admin) {
            $oldPlanId = $subscription->plan_id;

            $subscription->update(['plan_id' => $newPlan->id]);

            $this->recordEvent($subscription, SubscriptionEventType::PlanChanged, $subscription->state, $subscription->state, $admin, [
                'old_plan_id' => $oldPlanId,
                'new_plan_id' => $newPlan->id,
                'new_plan_slug' => $newPlan->slug,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Update seat count on a subscription.
     */
    public function changeSeats(Subscription $subscription, int $newCount, ?AdminUser $admin = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $newCount, $admin) {
            $oldCount = $subscription->seat_count;

            $subscription->update(['seat_count' => $newCount]);

            $this->recordEvent($subscription, SubscriptionEventType::SeatsChanged, $subscription->state, $subscription->state, $admin, [
                'old_seat_count' => $oldCount,
                'new_seat_count' => $newCount,
            ]);

            return $subscription->fresh();
        });
    }

    private function resolveEventType(SubscriptionState $from, SubscriptionState $to): SubscriptionEventType
    {
        return match (true) {
            $to === SubscriptionState::Active && $from === SubscriptionState::Trial => SubscriptionEventType::Activated,
            $to === SubscriptionState::Active && $from === SubscriptionState::PastDue => SubscriptionEventType::Reactivated,
            $to === SubscriptionState::Active && $from === SubscriptionState::Suspended => SubscriptionEventType::Reactivated,
            $to === SubscriptionState::PastDue => SubscriptionEventType::PastDueEntered,
            $to === SubscriptionState::Suspended => SubscriptionEventType::Suspended,
            $to === SubscriptionState::Cancelled => SubscriptionEventType::Cancelled,
            default => SubscriptionEventType::Activated,
        };
    }

    private function recordEvent(
        Subscription $subscription,
        SubscriptionEventType $eventType,
        ?SubscriptionState $fromState,
        ?SubscriptionState $toState,
        ?AdminUser $admin,
        array $payload = [],
    ): void {
        SubscriptionEvent::create([
            'subscription_id' => $subscription->id,
            'event_type' => $eventType,
            'from_state' => $fromState?->value,
            'to_state' => $toState?->value,
            'payload' => $payload ?: null,
            'triggered_by_admin_id' => $admin?->id,
            'created_at' => now(),
        ]);
    }
}
