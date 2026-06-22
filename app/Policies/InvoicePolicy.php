<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->ensureSameWorkspace($user, $invoice);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->ensureSameWorkspace($user, $invoice)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->ensureSameWorkspace($user, $invoice)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
