<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Obligation;
use App\Models\User;

class ObligationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Obligation $obligation): bool
    {
        return $this->ensureSameWorkspace($user, $obligation);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Obligation $obligation): bool
    {
        return $this->ensureSameWorkspace($user, $obligation);
    }

    public function delete(User $user, Obligation $obligation): bool
    {
        return $this->ensureSameWorkspace($user, $obligation)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
