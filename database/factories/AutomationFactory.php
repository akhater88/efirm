<?php

namespace Database\Factories;

use App\Models\Automation;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Automation>
 */
class AutomationFactory extends Factory
{
    protected $model = Automation::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name_ar' => 'أتمتة '.fake()->word(),
            'name_en' => fake()->words(3, true).' Automation',
            'trigger_event' => 'matter.created',
            'conditions' => ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withActions(int $count = 1): static
    {
        return $this->afterCreating(function (Automation $automation) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $automation->actions()->create([
                    'sort_order' => $i,
                    'action_type' => 'notify_user',
                    'action_payload' => ['user_id' => 'auto', 'message' => 'Auto-notification '.($i + 1)],
                    'stop_on_error' => true,
                ]);
            }
        });
    }
}
