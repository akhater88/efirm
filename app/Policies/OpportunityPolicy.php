<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Opportunity $opportunity): bool
    {
        return $this->ensureSameWorkspace($user, $opportunity);
    }

    // [PROVISIONAL-FOUNDER-DECIDED] Member can create opportunities
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return $this->ensureSameWorkspace($user, $opportunity);
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $this->ensureSameWorkspace($user, $opportunity)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function convert(User $user, Opportunity $opportunity): bool
    {
        return $this->ensureSameWorkspace($user, $opportunity);
    }
}
