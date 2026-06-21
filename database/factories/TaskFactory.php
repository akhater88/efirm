<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Matter;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'title' => fake()->sentence(4),
            'taskable_type' => 'matter',
            'taskable_id' => Matter::factory(),
            'priority' => TaskPriority::Normal,
            'status' => TaskStatus::Todo,
            'due_date' => now()->addDays(fake()->numberBetween(1, 30)),
        ];
    }

    public function done(): static
    {
        return $this->state([
            'status' => TaskStatus::Done,
            'completed_at' => now(),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(['priority' => TaskPriority::Urgent]);
    }
}
