<?php

namespace App\Policies;

use App\Models\DocumentVersion;
use App\Models\User;

class DocumentVersionPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DocumentVersion $version): bool
    {
        return $this->ensureSameWorkspace($user, $version);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function restore(User $user, DocumentVersion $version): bool
    {
        return $this->ensureSameWorkspace($user, $version);
    }
}
