<?php

namespace App\Observers;

use App\Enums\ObligationStatus;
use App\Enums\ObligationType;
use App\Enums\ResponsibleParty;
use App\Models\HearingActionItem;
use App\Models\Obligation;
use Illuminate\Support\Facades\DB;

/**
 * Observer for HearingActionItem — syncs status changes to linked Obligations.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #28.
 */
class HearingActionItemObserver
{
    public function creating(HearingActionItem $item): void
    {
        // Auto-create Obligation if not already linked (skip if service already created one)
        if ($item->obligation_id !== null) {
            return;
        }

        $hearing = $item->hearing;
        if (! $hearing) {
            return;
        }

        $documentId = $hearing->matter?->documents()->first()?->id;
        if (! $documentId) {
            return;
        }

        DB::transaction(function () use ($item, $hearing, $documentId) {
            $obligation = Obligation::create([
                'workspace_id' => $item->workspace_id ?? $hearing->workspace_id,
                'document_id' => $documentId,
                'title' => $item->description_ar,
                'obligation_type' => ObligationType::Other,
                'responsible_party' => ResponsibleParty::Us,
                'responsible_user_id' => $item->responsible_user_id,
                'due_date' => $item->due_date,
                'status' => ObligationStatus::Pending,
                'created_by_user_id' => $item->created_by_user_id,
                'updated_by_user_id' => $item->updated_by_user_id,
            ]);

            $item->obligation_id = $obligation->id;
        });
    }

    public function updating(HearingActionItem $item): void
    {
        if (! $item->isDirty('status') || ! $item->obligation_id) {
            return;
        }

        $obligation = Obligation::withoutGlobalScopes()->find($item->obligation_id);
        if (! $obligation) {
            return;
        }

        if ($item->status === 'completed') {
            $obligation->update([
                'status' => ObligationStatus::Completed,
                'completed_at' => now(),
            ]);
        } elseif ($item->status === 'waived') {
            $obligation->update([
                'status' => ObligationStatus::Waived,
            ]);
        }
    }

    public function deleting(HearingActionItem $item): void
    {
        if (! $item->obligation_id) {
            return;
        }

        $obligation = Obligation::withoutGlobalScopes()->find($item->obligation_id);
        if ($obligation) {
            $obligation->update([
                'status' => ObligationStatus::Waived,
            ]);
        }
    }
}
