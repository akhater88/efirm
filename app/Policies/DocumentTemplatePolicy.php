<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\DocumentTemplate;
use App\Models\User;

class DocumentTemplatePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DocumentTemplate $documentTemplate): bool
    {
        // Global templates (workspace_id=null) are visible to all
        if ($documentTemplate->workspace_id === null) {
            return true;
        }

        return $this->ensureSameWorkspace($user, $documentTemplate);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, DocumentTemplate $documentTemplate): bool
    {
        if ($documentTemplate->workspace_id === null) {
            return false; // Global templates cannot be edited by workspace users
        }

        return $this->ensureSameWorkspace($user, $documentTemplate)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, DocumentTemplate $documentTemplate): bool
    {
        if ($documentTemplate->workspace_id === null) {
            return false;
        }

        return $this->ensureSameWorkspace($user, $documentTemplate)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
