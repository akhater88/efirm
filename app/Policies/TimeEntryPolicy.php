<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TimeEntry $timeEntry): bool
    {
        return $this->ensureSameWorkspace($user, $timeEntry);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TimeEntry $timeEntry): bool
    {
        if (! $this->ensureSameWorkspace($user, $timeEntry)) {
            return false;
        }

        // Users can edit their own entries; admins can edit any
        return $timeEntry->user_id === $user->id
            || $this->hasRole($user, Role::Admin);
    }

    public function delete(User $user, TimeEntry $timeEntry): bool
    {
        return $this->ensureSameWorkspace($user, $timeEntry)
            && ($timeEntry->user_id === $user->id || $this->hasRole($user, Role::Owner, Role::Admin));
    }
}
