<?php

namespace App\Services;

use App\Models\CourtReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Court Review Trainee Dispatch — assigns court reviews to trainees for physical collection.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #29.
 */
class CourtReviewDispatchService
{
    /**
     * Dispatch a court review to a user (typically a trainee).
     *
     * @param  array<string, mixed>  $context
     */
    public function dispatch(CourtReview $review, User $assignTo, array $context, User $dispatcher): CourtReview
    {
        $review->update(array_merge($context, [
            'dispatched_to_user_id' => $assignTo->id,
            'dispatched_at' => now(),
            'updated_by_user_id' => $dispatcher->id,
        ]));

        return $review->fresh();
    }

    /**
     * Mark a dispatched court review as completed.
     *
     * @param  array<string, mixed>  $data
     */
    public function complete(CourtReview $review, array $data, User $completer): CourtReview
    {
        $review->update(array_merge($data, [
            'completed_by_user_id' => $completer->id,
            'updated_by_user_id' => $completer->id,
        ]));

        return $review->fresh();
    }

    /**
     * Get all court reviews dispatched to a specific user.
     *
     * @return Collection<int, CourtReview>
     */
    public function getDispatchedToMe(User $user): Collection
    {
        return CourtReview::where('dispatched_to_user_id', $user->id)
            ->with(['hearing', 'matter'])
            ->latest('dispatched_at')
            ->get();
    }
}
