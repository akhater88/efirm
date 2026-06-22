<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Receipt;
use App\Models\User;

class ReceiptPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Receipt $receipt): bool
    {
        return $this->ensureSameWorkspace($user, $receipt);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, Receipt $receipt): bool
    {
        return $this->ensureSameWorkspace($user, $receipt)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, Receipt $receipt): bool
    {
        return $this->ensureSameWorkspace($user, $receipt)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
