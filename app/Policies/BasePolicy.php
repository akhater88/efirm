<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class BasePolicy
{
    /**
     * Owner short-circuit: Owners can do everything within their workspace.
     * Returning null falls through to the specific policy method.
     * Returning true grants access unconditionally.
     */
    public function before(User $user, string $ability): ?bool
    {
        $workspace = $user->currentWorkspace();

        if (! $workspace) {
            return false;
        }

        $role = $user->roleInWorkspace($workspace);

        if ($role === Role::Owner) {
            return true;
        }

        return null;
    }

    /**
     * Verify the user and the model belong to the same workspace.
     */
    protected function ensureSameWorkspace(User $user, Model $model): bool
    {
        $currentWorkspace = $user->currentWorkspace();

        if (! $currentWorkspace) {
            return false;
        }

        if (! isset($model->workspace_id)) {
            return false;
        }

        return $model->workspace_id === $currentWorkspace->id;
    }

    /**
     * Check if the user has at least one of the given roles in the current workspace.
     */
    protected function hasRole(User $user, Role ...$roles): bool
    {
        $workspace = $user->currentWorkspace();

        if (! $workspace) {
            return false;
        }

        $userRole = $user->roleInWorkspace($workspace);

        if (! $userRole instanceof Role) {
            return false;
        }

        foreach ($roles as $role) {
            if ($userRole === $role) {
                return true;
            }
        }

        return false;
    }
}
