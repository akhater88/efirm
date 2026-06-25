<?php

namespace Database\Factories;

use App\Models\TaskType;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TaskType>
 */
class TaskTypeFactory extends Factory
{
    protected $model = TaskType::class;

    public function definition(): array
    {
        $nameEn = fake()->unique()->words(2, true);

        return [
            'workspace_id' => Workspace::factory(),
            'name_en' => $nameEn,
            'name_ar' => $nameEn,
            'slug' => Str::slug($nameEn),
            'icon' => 'clipboard',
            'color' => '#0D5C2E',
            'custom_fields' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withCustomFields(): static
    {
        return $this->state([
            'custom_fields' => [
                [
                    'key' => 'contract_value',
                    'label_en' => 'Contract Value',
                    'label_ar' => "\u0642\u064a\u0645\u0629 \u0627\u0644\u0639\u0642\u062f",
                    'type' => 'number',
                    'required' => false,
                    'options' => null,
                ],
            ],
        ]);
    }
}
