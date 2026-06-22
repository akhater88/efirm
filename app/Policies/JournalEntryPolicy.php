<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $this->ensureSameWorkspace($user, $journalEntry);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, JournalEntry $journalEntry): bool
    {
        return $this->ensureSameWorkspace($user, $journalEntry)
            && $this->hasRole($user, Role::Owner, Role::Admin)
            && ! $journalEntry->is_posted;
    }

    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        return $this->ensureSameWorkspace($user, $journalEntry)
            && $this->hasRole($user, Role::Owner, Role::Admin)
            && ! $journalEntry->is_posted;
    }

    public function post(User $user, JournalEntry $journalEntry): bool
    {
        return $this->ensureSameWorkspace($user, $journalEntry)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
