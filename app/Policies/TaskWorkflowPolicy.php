<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\TaskWorkflow;
use App\Models\User;

class TaskWorkflowPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TaskWorkflow $workflow): bool
    {
        return $this->ensureSameWorkspace($user, $workflow);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, TaskWorkflow $workflow): bool
    {
        return $this->ensureSameWorkspace($user, $workflow)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, TaskWorkflow $workflow): bool
    {
        if (! $this->ensureSameWorkspace($user, $workflow)) {
            return false;
        }

        if (! $this->hasRole($user, Role::Owner, Role::Admin)) {
            return false;
        }

        // Cannot delete if workflow has active (non-done/cancelled) tasks
        $hasActiveTasks = $workflow->tasks()
            ->whereNotIn('status', ['done', 'cancelled'])
            ->exists();

        return ! $hasActiveTasks;
    }
}
