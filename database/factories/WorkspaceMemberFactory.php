<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkspaceMember>
 */
class WorkspaceMemberFactory extends Factory
{
    protected $model = WorkspaceMember::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => User::factory(),
            'role' => Role::Member,
            'joined_at' => now(),
        ];
    }

    public function owner(): static
    {
        return $this->state(['role' => Role::Owner]);
    }

    public function admin(): static
    {
        return $this->state(['role' => Role::Admin]);
    }

    public function member(): static
    {
        return $this->state(['role' => Role::Member]);
    }
}
