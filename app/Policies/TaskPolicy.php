<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Task;
use App\Models\User;

class TaskPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $this->ensureSameWorkspace($user, $task);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $this->ensureSameWorkspace($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->ensureSameWorkspace($user, $task)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
