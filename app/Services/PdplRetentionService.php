<?php

namespace App\Services;

use App\Enums\AdminActivityEventType;
use App\Enums\SubscriptionEventType;
use App\Enums\SubscriptionState;
use App\Models\AdminUser;
use App\Models\Subscription;
use App\Models\SubscriptionEvent;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PdplRetentionService
{
    /**
     * PDPL-compliant retention window in days.
     * Jordan PDPL Law No. 24/2023, Article 8: data must not be retained
     * longer than necessary. 90 days is a reasonable SaaS default that
     * allows recovery while respecting the principle of minimization.
     *
     * [NEEDS-KHALDOUN-REVIEW] Retention window pending advisor confirmation.
     */
    public const RETENTION_DAYS = 90;

    /**
     * PDPL cross-border consent text version.
     * [NEEDS-KHALDOUN-REVIEW] Consent text pending legal review.
     */
    public const CONSENT_TEXT_VERSION = 'v1.0-draft';

    /**
     * Initiate PDPL-compliant cancellation with retention window.
     */
    public function initiateCancellation(Subscription $subscription, ?AdminUser $admin = null, string $reason = 'voluntary'): Subscription
    {
        return DB::transaction(function () use ($subscription, $admin, $reason) {
            $lifecycle = app(SubscriptionLifecycleService::class);

            // Transition to cancelled if not already
            if ($subscription->state !== SubscriptionState::Cancelled) {
                // Walk through valid state transitions
                if ($subscription->state === SubscriptionState::Active) {
                    $subscription = $lifecycle->transition($subscription, SubscriptionState::PastDue, $admin, ['reason' => $reason]);
                    $subscription = $lifecycle->transition($subscription, SubscriptionState::Suspended, $admin, ['reason' => $reason]);
                } elseif ($subscription->state === SubscriptionState::PastDue) {
                    $subscription = $lifecycle->transition($subscription, SubscriptionState::Suspended, $admin, ['reason' => $reason]);
                }

                if ($subscription->state === SubscriptionState::Suspended || $subscription->state === SubscriptionState::Trial) {
                    $subscription = $lifecycle->transition($subscription, SubscriptionState::Cancelled, $admin, ['reason' => $reason]);
                }
            }

            // Set retention expiry
            $subscription->update([
                'data_retention_expires_at' => now()->addDays(self::RETENTION_DAYS),
            ]);

            // Record retention event
            SubscriptionEvent::create([
                'subscription_id' => $subscription->id,
                'event_type' => SubscriptionEventType::Cancelled,
                'from_state' => null,
                'to_state' => 'retention',
                'payload' => [
                    'retention_days' => self::RETENTION_DAYS,
                    'retention_expires_at' => $subscription->data_retention_expires_at->toIso8601String(),
                    'reason' => $reason,
                    'pdpl_reference' => 'Jordan PDPL Law No. 24/2023, Article 8',
                ],
                'triggered_by_admin_id' => $admin?->id,
                'created_at' => now(),
            ]);

            if ($admin) {
                AdminActivityLogService::log(
                    AdminActivityEventType::CancellationInitiated,
                    $admin,
                    [
                        'workspace_id' => $subscription->workspace_id,
                        'retention_days' => self::RETENTION_DAYS,
                        'reason' => $reason,
                    ],
                    $subscription->workspace,
                );
            }

            return $subscription->fresh();
        });
    }

    /**
     * Purge workspace data after retention window expires.
     * Called by scheduled job — never directly by admin action.
     */
    public function purgeExpiredWorkspace(Subscription $subscription): void
    {
        if (! $subscription->isCancelled()) {
            throw new \RuntimeException('Cannot purge: subscription is not cancelled.');
        }

        if (! $subscription->data_retention_expires_at?->isPast()) {
            throw new \RuntimeException('Cannot purge: retention window has not expired.');
        }

        if ($subscription->data_purged) {
            return; // Already purged — idempotent
        }

        DB::transaction(function () use ($subscription) {
            $workspace = $subscription->workspace;

            if ($workspace) {
                // Soft-delete all workspace data (preserves audit trail)
                $this->softDeleteWorkspaceData($workspace);
            }

            $subscription->update([
                'data_purged' => true,
                'data_purged_at' => now(),
            ]);

            SubscriptionEvent::create([
                'subscription_id' => $subscription->id,
                'event_type' => SubscriptionEventType::Cancelled,
                'from_state' => 'retention',
                'to_state' => 'purged',
                'payload' => [
                    'purged_at' => now()->toIso8601String(),
                    'pdpl_reference' => 'Jordan PDPL Law No. 24/2023, Article 8',
                ],
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Get all subscriptions with expired retention windows pending purge.
     *
     * @return Collection<int, Subscription>
     */
    public function getExpiredRetentions(): Collection
    {
        return Subscription::where('state', SubscriptionState::Cancelled)
            ->where('data_purged', false)
            ->whereNotNull('data_retention_expires_at')
            ->where('data_retention_expires_at', '<=', now())
            ->get();
    }

    /**
     * Record PDPL cross-border consent for a workspace.
     */
    public function recordConsent(Workspace $workspace): void
    {
        $workspace->update([
            'pdpl_consent_obtained' => true,
            'pdpl_consent_date' => now(),
            'pdpl_consent_text_version' => self::CONSENT_TEXT_VERSION,
        ]);
    }

    /**
     * Check if workspace has valid PDPL consent.
     */
    public function hasValidConsent(Workspace $workspace): bool
    {
        return $workspace->pdpl_consent_obtained
            && $workspace->pdpl_consent_text_version === self::CONSENT_TEXT_VERSION;
    }

    private function softDeleteWorkspaceData(Workspace $workspace): void
    {
        // Soft-delete tenant-scoped entities
        // Using withoutGlobalScopes to ensure we can access workspace data
        $workspace->matters()->each(fn ($m) => $m->delete());
        $workspace->contacts()->each(fn ($c) => $c->delete());

        // Soft-delete the workspace itself
        $workspace->delete();
    }
}
