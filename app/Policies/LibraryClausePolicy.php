<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\LibraryClause;
use App\Models\User;

class LibraryClausePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LibraryClause $clause): bool
    {
        return $this->ensureSameWorkspace($user, $clause);
    }

    // [PROVISIONAL-FOUNDER-DECIDED] Any member can create library clauses
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, LibraryClause $clause): bool
    {
        return $this->ensureSameWorkspace($user, $clause);
    }

    public function delete(User $user, LibraryClause $clause): bool
    {
        return $this->ensureSameWorkspace($user, $clause)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
