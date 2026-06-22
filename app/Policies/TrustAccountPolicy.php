<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\TrustAccount;
use App\Models\User;

class TrustAccountPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TrustAccount $trustAccount): bool
    {
        return $this->ensureSameWorkspace($user, $trustAccount);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, TrustAccount $trustAccount): bool
    {
        return $this->ensureSameWorkspace($user, $trustAccount)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, TrustAccount $trustAccount): bool
    {
        return $this->ensureSameWorkspace($user, $trustAccount)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function deposit(User $user, TrustAccount $trustAccount): bool
    {
        return $this->ensureSameWorkspace($user, $trustAccount)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function withdraw(User $user, TrustAccount $trustAccount): bool
    {
        return $this->ensureSameWorkspace($user, $trustAccount)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
