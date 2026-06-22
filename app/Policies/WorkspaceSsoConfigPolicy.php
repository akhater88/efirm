<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\WorkspaceSsoConfig;

class WorkspaceSsoConfigPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function view(User $user, WorkspaceSsoConfig $config): bool
    {
        return $this->ensureSameWorkspace($user, $config)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner);
    }

    public function update(User $user, WorkspaceSsoConfig $config): bool
    {
        return $this->ensureSameWorkspace($user, $config)
            && $this->hasRole($user, Role::Owner);
    }

    public function delete(User $user, WorkspaceSsoConfig $config): bool
    {
        return $this->ensureSameWorkspace($user, $config)
            && $this->hasRole($user, Role::Owner);
    }
}
