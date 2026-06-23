<?php

namespace App\Services;

use App\Models\MatterLawyer;
use App\Models\User;

class LawyerPickerService
{
    /**
     * Get grouped user options for task assignment on a Matter.
     * Returns lawyers assigned to the Matter first, then other workspace members.
     *
     * @return array{matter_lawyers: array<string, string>, other_members: array<string, string>}
     */
    public function getGroupedOptionsForMatter(string $matterId, string $workspaceId): array
    {
        // Get Matter's active assigned lawyers (Lead first, then Supporting)
        $assignedLawyerIds = MatterLawyer::where('matter_id', $matterId)
            ->active()
            ->orderByRaw("CASE WHEN role = 'lead' THEN 0 ELSE 1 END")
            ->pluck('user_id')
            ->toArray();

        $assignedLawyers = [];
        if (! empty($assignedLawyerIds)) {
            $assignedLawyers = User::whereIn('id', $assignedLawyerIds)
                ->get()
                ->sortBy(fn (User $user) => array_search($user->id, $assignedLawyerIds))
                ->pluck('name', 'id')
                ->toArray();
        }

        // Get other workspace members not in the assigned list
        $otherMembers = User::whereHas('workspaceMembers', fn ($q) => $q->where('workspace_id', $workspaceId))
            ->whereNotIn('id', $assignedLawyerIds)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        return [
            'matter_lawyers' => $assignedLawyers,
            'other_members' => $otherMembers,
        ];
    }

    /**
     * Get flat user options (no grouping) for non-Matter contexts.
     *
     * @return array<string, string>
     */
    public function getFlatOptionsForWorkspace(string $workspaceId): array
    {
        return User::whereHas('workspaceMembers', fn ($q) => $q->where('workspace_id', $workspaceId))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
