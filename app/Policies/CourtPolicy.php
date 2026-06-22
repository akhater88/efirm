<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Court;
use App\Models\User;

class CourtPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Court $court): bool
    {
        return $this->ensureSameWorkspace($user, $court);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Court $court): bool
    {
        return $this->ensureSameWorkspace($user, $court);
    }

    public function delete(User $user, Court $court): bool
    {
        return $this->ensureSameWorkspace($user, $court)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
