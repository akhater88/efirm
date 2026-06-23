<?php

namespace App\Services;

use App\Enums\HearingStatus;
use App\Enums\ObligationStatus;
use App\Enums\ObligationType;
use App\Enums\ResponsibleParty;
use App\Models\Hearing;
use App\Models\HearingActionItem;
use App\Models\Matter;
use App\Models\Obligation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Hearing Session History — records outcomes and manages action items.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #28.
 */
class HearingSessionService
{
    /**
     * Record session outcome content for a held hearing.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function recordOutcome(Hearing $hearing, array $data, User $by): Hearing
    {
        if ($hearing->status !== HearingStatus::Held) {
            throw ValidationException::withMessages([
                'status' => [__('litigation.session_content_requires_held_status')],
            ]);
        }

        $hearing->update(array_merge($data, [
            'updated_by_user_id' => $by->id,
        ]));

        return $hearing->fresh();
    }

    /**
     * Add an action item to a hearing and auto-create a linked Obligation.
     *
     * @param  array<string, mixed>  $data
     */
    public function addActionItem(Hearing $hearing, array $data, User $by): HearingActionItem
    {
        return DB::transaction(function () use ($hearing, $data, $by) {
            // Find first document on the matter for the obligation
            $documentId = $hearing->matter->documents()->first()?->id;

            // Create the linked Obligation first
            $obligation = null;
            if ($documentId) {
                $obligation = Obligation::create([
                    'workspace_id' => $hearing->workspace_id,
                    'document_id' => $documentId,
                    'title' => $data['description_ar'],
                    'obligation_type' => ObligationType::Other,
                    'responsible_party' => ResponsibleParty::Us,
                    'responsible_user_id' => $data['responsible_user_id'] ?? null,
                    'due_date' => $data['due_date'],
                    'status' => ObligationStatus::Pending,
                    'created_by_user_id' => $by->id,
                    'updated_by_user_id' => $by->id,
                ]);
            }

            $actionItem = HearingActionItem::create([
                'workspace_id' => $hearing->workspace_id,
                'hearing_id' => $hearing->id,
                'description_ar' => $data['description_ar'],
                'description_en' => $data['description_en'] ?? null,
                'due_date' => $data['due_date'],
                'responsible_user_id' => $data['responsible_user_id'] ?? null,
                'status' => 'pending',
                'obligation_id' => $obligation?->id,
                'created_by_user_id' => $by->id,
                'updated_by_user_id' => $by->id,
            ]);

            return $actionItem;
        });
    }

    /**
     * Get a chronological timeline of held sessions for a matter.
     *
     * @return Collection<int, Hearing>
     */
    public function getSessionsTimelineForMatter(Matter $matter): Collection
    {
        return Hearing::where('matter_id', $matter->id)
            ->where('status', HearingStatus::Held)
            ->with('actionItems')
            ->orderBy('hearing_date')
            ->get();
    }
}
