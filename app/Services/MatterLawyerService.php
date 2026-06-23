<?php

namespace App\Services;

use App\Enums\MatterLawyerRole;
use App\Models\Matter;
use App\Models\MatterLawyer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MatterLawyerService
{
    public function assignLawyer(Matter $matter, User $user, MatterLawyerRole $role, User $assignedBy): MatterLawyer
    {
        return DB::transaction(function () use ($matter, $user, $role, $assignedBy) {
            // If assigning as lead, unassign the current lead first
            if ($role === MatterLawyerRole::Lead) {
                $currentLead = MatterLawyer::where('matter_id', $matter->id)
                    ->lead()
                    ->first();

                if ($currentLead) {
                    $currentLead->update([
                        'unassigned_at' => now(),
                        'unassigned_by_user_id' => $assignedBy->id,
                    ]);
                }
            }

            $matterLawyer = MatterLawyer::create([
                'matter_id' => $matter->id,
                'user_id' => $user->id,
                'role' => $role,
                'assigned_at' => now(),
                'assigned_by_user_id' => $assignedBy->id,
            ]);

            // Keep legacy column in sync
            if ($role === MatterLawyerRole::Lead) {
                $matter->update(['lead_lawyer_id' => $user->id]);
            }

            return $matterLawyer;
        });
    }

    public function unassignLawyer(Matter $matter, User $user, User $unassignedBy): void
    {
        DB::transaction(function () use ($matter, $user, $unassignedBy) {
            $assignment = MatterLawyer::where('matter_id', $matter->id)
                ->where('user_id', $user->id)
                ->active()
                ->firstOrFail();

            $wasLead = $assignment->role === MatterLawyerRole::Lead;

            $assignment->update([
                'unassigned_at' => now(),
                'unassigned_by_user_id' => $unassignedBy->id,
            ]);

            if ($wasLead) {
                $matter->update(['lead_lawyer_id' => null]);
            }
        });
    }

    public function changeLeadLawyer(Matter $matter, User $newLead, User $changedBy): MatterLawyer
    {
        return DB::transaction(function () use ($matter, $newLead, $changedBy) {
            // Unassign current lead if exists
            $currentLead = MatterLawyer::where('matter_id', $matter->id)
                ->lead()
                ->first();

            if ($currentLead) {
                $currentLead->update([
                    'unassigned_at' => now(),
                    'unassigned_by_user_id' => $changedBy->id,
                ]);
            }

            // Assign new lead
            $matterLawyer = MatterLawyer::create([
                'matter_id' => $matter->id,
                'user_id' => $newLead->id,
                'role' => MatterLawyerRole::Lead,
                'assigned_at' => now(),
                'assigned_by_user_id' => $changedBy->id,
            ]);

            $matter->update(['lead_lawyer_id' => $newLead->id]);

            return $matterLawyer;
        });
    }
}
