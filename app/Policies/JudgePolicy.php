<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Judge;
use App\Models\User;

class JudgePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Judge $judge): bool
    {
        return $this->ensureSameWorkspace($user, $judge);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Judge $judge): bool
    {
        return $this->ensureSameWorkspace($user, $judge);
    }

    public function delete(User $user, Judge $judge): bool
    {
        return $this->ensureSameWorkspace($user, $judge)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
