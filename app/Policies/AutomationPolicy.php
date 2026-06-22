<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Automation;
use App\Models\User;

class AutomationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Automation $automation): bool
    {
        return $this->ensureSameWorkspace($user, $automation);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, Automation $automation): bool
    {
        return $this->ensureSameWorkspace($user, $automation)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, Automation $automation): bool
    {
        return $this->ensureSameWorkspace($user, $automation)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
