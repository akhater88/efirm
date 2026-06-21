<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Contact;
use App\Models\User;

class ContactPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Contact $contact): bool
    {
        return $this->ensureSameWorkspace($user, $contact);
    }

    // [PROVISIONAL-FOUNDER-DECIDED] Member can create
    public function create(User $user): bool
    {
        return true;
    }

    // [PROVISIONAL-FOUNDER-DECIDED] Member can update
    public function update(User $user, Contact $contact): bool
    {
        return $this->ensureSameWorkspace($user, $contact);
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $this->ensureSameWorkspace($user, $contact)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
