<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Hearing;
use App\Models\User;

class HearingPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Hearing $hearing): bool
    {
        return $this->ensureSameWorkspace($user, $hearing);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Hearing $hearing): bool
    {
        return $this->ensureSameWorkspace($user, $hearing);
    }

    public function delete(User $user, Hearing $hearing): bool
    {
        return $this->ensureSameWorkspace($user, $hearing)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
