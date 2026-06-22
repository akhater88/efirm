<?php

namespace App\Policies;

use App\Models\CalendarIntegration;
use App\Models\User;

class CalendarIntegrationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CalendarIntegration $calendarIntegration): bool
    {
        return $this->ensureSameWorkspace($user, $calendarIntegration);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CalendarIntegration $calendarIntegration): bool
    {
        return $this->ensureSameWorkspace($user, $calendarIntegration);
    }

    public function delete(User $user, CalendarIntegration $calendarIntegration): bool
    {
        return $this->ensureSameWorkspace($user, $calendarIntegration);
    }
}
