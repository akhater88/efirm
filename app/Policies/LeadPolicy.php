<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Lead;
use App\Models\User;

class LeadPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->ensureSameWorkspace($user, $lead);
    }

    // [PROVISIONAL-FOUNDER-DECIDED] Member can create leads
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->ensureSameWorkspace($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->ensureSameWorkspace($user, $lead)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $this->ensureSameWorkspace($user, $lead);
    }
}
