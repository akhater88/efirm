<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ExpertReport;
use App\Models\User;

/**
 * Authorization policy for ExpertReport — same pattern as HearingPolicy.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 and #19.
 */
class ExpertReportPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ExpertReport $expertReport): bool
    {
        return $this->ensureSameWorkspace($user, $expertReport);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ExpertReport $expertReport): bool
    {
        return $this->ensureSameWorkspace($user, $expertReport);
    }

    public function delete(User $user, ExpertReport $expertReport): bool
    {
        return $this->ensureSameWorkspace($user, $expertReport)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
