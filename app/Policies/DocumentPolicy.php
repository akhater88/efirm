<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        return $this->ensureSameWorkspace($user, $document);
    }

    // [PROVISIONAL-FOUNDER-DECIDED] Any member can create documents
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Document $document): bool
    {
        return $this->ensureSameWorkspace($user, $document);
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->ensureSameWorkspace($user, $document)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
