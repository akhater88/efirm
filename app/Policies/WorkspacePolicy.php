<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy extends BasePolicy
{
    public function view(User $user, Workspace $workspace): bool
    {
        return $user->belongsToWorkspace($workspace);
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Workspace $workspace): bool
    {
        return $user->belongsToWorkspace($workspace)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    /**
     * Only Owner can delete (soft-delete) a workspace.
     * [STUB — refine in S-06 when workspace deletion UX is built]
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $user->belongsToWorkspace($workspace)
            && $this->hasRole($user, Role::Owner);
    }

    /**
     * [STUB — actual invite flow deferred to S-02]
     */
    public function inviteMember(User $user, Workspace $workspace): bool
    {
        return $user->belongsToWorkspace($workspace)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    /**
     * [STUB — actual removal flow deferred to S-02]
     */
    public function removeMember(User $user, Workspace $workspace): bool
    {
        return $user->belongsToWorkspace($workspace)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
