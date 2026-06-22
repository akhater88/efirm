<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Pipeline;
use App\Models\User;

class PipelinePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pipeline $pipeline): bool
    {
        return $this->ensureSameWorkspace($user, $pipeline);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, Pipeline $pipeline): bool
    {
        return $this->ensureSameWorkspace($user, $pipeline)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, Pipeline $pipeline): bool
    {
        return $this->ensureSameWorkspace($user, $pipeline)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
