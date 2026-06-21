<?php

namespace App\Services;

use App\Enums\KycChecklistStatus;
use App\Enums\KycItemType;
use App\Models\Contact;
use App\Models\KycChecklist;
use App\Models\KycItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KycService
{
    /**
     * Start a KYC checklist for a contact, seeding the appropriate items.
     */
    public function start(Contact $contact, User $actor): KycChecklist
    {
        return DB::transaction(function () use ($contact, $actor) {
            $checklist = KycChecklist::create([
                'workspace_id' => $contact->workspace_id,
                'contact_id' => $contact->id,
                'status' => KycChecklistStatus::InProgress,
                'started_at' => now(),
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $itemTypes = KycItemType::forType($contact->type);

            foreach ($itemTypes as $itemType) {
                KycItem::create([
                    'kyc_checklist_id' => $checklist->id,
                    'item_type' => $itemType,
                ]);
            }

            return $checklist->load('items');
        });
    }
}
