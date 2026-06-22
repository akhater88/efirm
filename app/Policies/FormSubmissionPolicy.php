<?php

namespace App\Policies;

use App\Models\FormSubmission;
use App\Models\User;

class FormSubmissionPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FormSubmission $formSubmission): bool
    {
        return $this->ensureSameWorkspace($user, $formSubmission);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FormSubmission $formSubmission): bool
    {
        return $this->ensureSameWorkspace($user, $formSubmission);
    }

    public function delete(User $user, FormSubmission $formSubmission): bool
    {
        return $this->ensureSameWorkspace($user, $formSubmission);
    }
}
