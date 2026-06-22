<?php

namespace Database\Factories;

use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationRun>
 */
class AutomationRunFactory extends Factory
{
    protected $model = AutomationRun::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'automation_id' => Automation::factory(),
            'trigger_payload' => ['event' => 'matter.created'],
            'status' => 'completed',
            'duration_ms' => fake()->numberBetween(10, 5000),
            'created_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state([
            'status' => 'failed',
            'error_message' => 'Action failed: connection timeout',
        ]);
    }
}
