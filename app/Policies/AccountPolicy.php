<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Account;
use App\Models\User;

class AccountPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Account $account): bool
    {
        return $this->ensureSameWorkspace($user, $account);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, Account $account): bool
    {
        return $this->ensureSameWorkspace($user, $account)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, Account $account): bool
    {
        return $this->ensureSameWorkspace($user, $account)
            && $this->hasRole($user, Role::Owner, Role::Admin)
            && ! $account->is_system;
    }
}
