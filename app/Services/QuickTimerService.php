<?php

namespace App\Services;

use App\Models\Hearing;
use App\Models\Matter;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Contextual Quick Timer per advisor input from Khaldoun Khater,
 * docs/02_advisor_meeting_log.md Conversation 3.5, Decision #31.
 *
 * Single-active-timer constraint: at most ONE active TimeEntry per user.
 * Race conditions handled via DB row lock (lockForUpdate).
 */
class QuickTimerService
{
    /**
     * Start a timer in the context of a Matter.
     */
    public function startForMatter(Matter $matter, User $user): TimeEntry
    {
        return $this->start($matter->id, 'matter', $user);
    }

    /**
     * Start a timer in the context of a Hearing (links to the hearing's matter).
     */
    public function startForHearing(Hearing $hearing, User $user): TimeEntry
    {
        return $this->start($hearing->matter_id, 'hearing', $user);
    }

    /**
     * Stop an active timer, calculating or applying the duration.
     */
    public function stop(TimeEntry $entry, User $user, ?string $description = null, ?int $adjustedMinutes = null): TimeEntry
    {
        $entry->ended_at = now();

        $calculatedMinutes = (int) $entry->started_at->diffInMinutes($entry->ended_at);
        $entry->duration_minutes = $adjustedMinutes ?? max($calculatedMinutes, 1);

        if ($description !== null) {
            $entry->description = $description;
        }

        $entry->updated_by_user_id = $user->id;
        $entry->save();

        return $entry->fresh();
    }

    /**
     * Get the currently active (running) timer for a user, if any.
     */
    public function getActiveTimerForUser(User $user): ?TimeEntry
    {
        return TimeEntry::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();
    }

    /**
     * Internal: create a new timer entry with single-active constraint.
     */
    private function start(string $matterId, string $context, User $user): TimeEntry
    {
        return DB::transaction(function () use ($matterId, $context, $user) {
            $active = TimeEntry::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->first();

            if ($active) {
                throw new ConflictHttpException(__('litigation.timer_already_active'));
            }

            return TimeEntry::create([
                'workspace_id' => $user->current_workspace_id,
                'user_id' => $user->id,
                'matter_id' => $matterId,
                'started_at' => now(),
                'is_billable' => true,
                'started_via_context' => $context,
                'duration_minutes' => 0,
                'created_by_user_id' => $user->id,
                'updated_by_user_id' => $user->id,
            ]);
        });
    }
}
