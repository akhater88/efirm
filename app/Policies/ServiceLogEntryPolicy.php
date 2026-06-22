<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ServiceLogEntry;
use App\Models\User;

class ServiceLogEntryPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceLogEntry $serviceLogEntry): bool
    {
        return $this->ensureSameWorkspace($user, $serviceLogEntry);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceLogEntry $serviceLogEntry): bool
    {
        return $this->ensureSameWorkspace($user, $serviceLogEntry);
    }

    public function delete(User $user, ServiceLogEntry $serviceLogEntry): bool
    {
        return $this->ensureSameWorkspace($user, $serviceLogEntry)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
