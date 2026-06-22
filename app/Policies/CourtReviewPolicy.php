<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\CourtReview;
use App\Models\User;

class CourtReviewPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CourtReview $courtReview): bool
    {
        return $this->ensureSameWorkspace($user, $courtReview);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CourtReview $courtReview): bool
    {
        return $this->ensureSameWorkspace($user, $courtReview);
    }

    public function delete(User $user, CourtReview $courtReview): bool
    {
        return $this->ensureSameWorkspace($user, $courtReview)
            && $this->hasRole($user, Role::Owner, Role::Admin);
    }
}
