<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return $this->ensureSameWorkspace($user, $auditLog)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    /**
     * Audit logs cannot be created via policy (system-only).
     * But we allow it for the API endpoint.
     */
    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    /**
     * Audit logs are APPEND-ONLY. Updates are always denied.
     */
    public function update(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Audit logs are APPEND-ONLY. Deletes are always denied.
     */
    public function delete(User $user, AuditLog $auditLog): bool
    {
        return false;
    }
}
