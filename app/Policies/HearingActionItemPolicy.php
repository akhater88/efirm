<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\HearingActionItem;
use App\Models\User;

class HearingActionItemPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, HearingActionItem $item): bool
    {
        return $this->ensureSameWorkspace($user, $item);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, HearingActionItem $item): bool
    {
        return $this->ensureSameWorkspace($user, $item);
    }

    public function delete(User $user, HearingActionItem $item): bool
    {
        return $this->ensureSameWorkspace($user, $item)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
