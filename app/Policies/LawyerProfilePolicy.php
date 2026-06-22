<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\LawyerProfile;
use App\Models\User;

class LawyerProfilePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LawyerProfile $lawyerProfile): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, LawyerProfile $lawyerProfile): bool
    {
        // Own profile — allowed (controller handles field restriction)
        if ($lawyerProfile->user_id === $user->id) {
            return true;
        }

        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, LawyerProfile $lawyerProfile): bool
    {
        return $this->hasRole($user, Role::Owner);
    }
}
