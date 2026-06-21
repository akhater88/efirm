<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Matter;
use App\Models\User;

class MatterPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    // [PROVISIONAL-FOUNDER-DECIDED] All members see all matters in workspace
    public function view(User $user, Matter $matter): bool
    {
        return $this->ensureSameWorkspace($user, $matter);
    }

    // [PROVISIONAL-FOUNDER-DECIDED] Any member can create
    public function create(User $user): bool
    {
        return true;
    }

    // [PROVISIONAL-FOUNDER-DECIDED] Any same-workspace member can update
    public function update(User $user, Matter $matter): bool
    {
        return $this->ensureSameWorkspace($user, $matter);
    }

    public function delete(User $user, Matter $matter): bool
    {
        return $this->ensureSameWorkspace($user, $matter)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
