<?php

namespace App\Policies;

use App\Models\DocumentShare;
use App\Models\User;

class DocumentSharePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DocumentShare $share): bool
    {
        return $this->ensureSameWorkspace($user, $share);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, DocumentShare $share): bool
    {
        return $this->ensureSameWorkspace($user, $share);
    }
}
