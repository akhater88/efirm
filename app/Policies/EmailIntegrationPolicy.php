<?php

namespace App\Policies;

use App\Models\EmailIntegration;
use App\Models\User;

class EmailIntegrationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EmailIntegration $emailIntegration): bool
    {
        return $this->ensureSameWorkspace($user, $emailIntegration);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, EmailIntegration $emailIntegration): bool
    {
        return $this->ensureSameWorkspace($user, $emailIntegration);
    }

    public function delete(User $user, EmailIntegration $emailIntegration): bool
    {
        return $this->ensureSameWorkspace($user, $emailIntegration);
    }
}
