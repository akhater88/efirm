<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Matter;
use App\Models\Subscription;
use App\Models\Workspace;

class SubscriptionEntitlementService
{
    /**
     * Get the subscription for a workspace (or null if none).
     */
    public function getSubscription(Workspace $workspace): ?Subscription
    {
        return Subscription::where('workspace_id', $workspace->id)
            ->with('plan')
            ->first();
    }

    /**
     * Check if workspace can add more seats.
     */
    public function canAddSeat(Workspace $workspace): bool
    {
        $subscription = $this->getSubscription($workspace);

        if (! $this->isWriteAllowed($subscription)) {
            return false;
        }

        $plan = $subscription->plan;

        if ($plan->max_seats === null) {
            return true;
        }

        return $workspace->members()->count() < $plan->max_seats;
    }

    /**
     * Check if workspace can create more matters.
     */
    public function canCreateMatter(Workspace $workspace): bool
    {
        $subscription = $this->getSubscription($workspace);

        if (! $this->isWriteAllowed($subscription)) {
            return false;
        }

        $plan = $subscription->plan;

        if ($plan->max_matters === null) {
            return true;
        }

        $matterCount = Matter::withoutGlobalScope('workspace')
            ->where('workspace_id', $workspace->id)
            ->count();

        return $matterCount < $plan->max_matters;
    }

    /**
     * Check if workspace can create more contacts.
     */
    public function canCreateContact(Workspace $workspace): bool
    {
        $subscription = $this->getSubscription($workspace);

        if (! $this->isWriteAllowed($subscription)) {
            return false;
        }

        $plan = $subscription->plan;

        if ($plan->max_contacts === null) {
            return true;
        }

        $contactCount = Contact::withoutGlobalScope('workspace')
            ->where('workspace_id', $workspace->id)
            ->count();

        return $contactCount < $plan->max_contacts;
    }

    /**
     * Check if a feature flag is enabled for the workspace's plan.
     */
    public function hasFeature(Workspace $workspace, string $feature): bool
    {
        $subscription = $this->getSubscription($workspace);

        if (! $subscription) {
            return false;
        }

        if ($subscription->isCancelled()) {
            return false;
        }

        $features = $subscription->plan->features ?? [];

        return in_array($feature, $features, true);
    }

    /**
     * Get remaining quota for a resource (null = unlimited).
     *
     * @param  string  $resource  One of: seats, matters, contacts
     */
    public function remainingQuota(Workspace $workspace, string $resource): ?int
    {
        $subscription = $this->getSubscription($workspace);

        if (! $subscription) {
            return 0;
        }

        if ($subscription->isCancelled()) {
            return 0;
        }

        $plan = $subscription->plan;

        return match ($resource) {
            'seats' => $this->calculateRemaining(
                $plan->max_seats,
                $workspace->members()->count()
            ),
            'matters' => $this->calculateRemaining(
                $plan->max_matters,
                Matter::withoutGlobalScope('workspace')
                    ->where('workspace_id', $workspace->id)
                    ->count()
            ),
            'contacts' => $this->calculateRemaining(
                $plan->max_contacts,
                Contact::withoutGlobalScope('workspace')
                    ->where('workspace_id', $workspace->id)
                    ->count()
            ),
            default => 0,
        };
    }

    /**
     * Check if write operations are allowed for the subscription.
     */
    private function isWriteAllowed(?Subscription $subscription): bool
    {
        if (! $subscription) {
            return false;
        }

        if ($subscription->isCancelled()) {
            return false;
        }

        if ($subscription->isSuspended()) {
            return false;
        }

        return true;
    }

    /**
     * Calculate remaining quota. Returns null if cap is unlimited (null).
     */
    private function calculateRemaining(?int $cap, int $current): ?int
    {
        if ($cap === null) {
            return null;
        }

        return max(0, $cap - $current);
    }
}
