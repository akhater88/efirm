<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\FormTemplate;
use App\Models\User;

class FormTemplatePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FormTemplate $formTemplate): bool
    {
        return $this->ensureSameWorkspace($user, $formTemplate);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function update(User $user, FormTemplate $formTemplate): bool
    {
        return $this->ensureSameWorkspace($user, $formTemplate)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }

    public function delete(User $user, FormTemplate $formTemplate): bool
    {
        return $this->ensureSameWorkspace($user, $formTemplate)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
