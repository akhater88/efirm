<?php

namespace Database\Factories;

use App\Models\Hearing;
use App\Models\HearingActionItem;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HearingActionItem>
 */
class HearingActionItemFactory extends Factory
{
    protected $model = HearingActionItem::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'hearing_id' => Hearing::factory(),
            'description_ar' => fake()->sentence(6),
            'description_en' => fake()->sentence(6),
            'due_date' => now()->addDays(fake()->numberBetween(3, 30)),
            'status' => 'pending',
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
        ]);
    }

    public function waived(): static
    {
        return $this->state([
            'status' => 'waived',
        ]);
    }
}
