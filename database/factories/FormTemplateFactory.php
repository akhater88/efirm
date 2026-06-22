<?php

namespace Database\Factories;

use App\Models\FormTemplate;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormTemplate>
 */
class FormTemplateFactory extends Factory
{
    protected $model = FormTemplate::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name_ar' => 'نموذج '.fake()->word(),
            'name_en' => fake()->words(3, true).' Form',
            'description' => fake()->optional()->sentence(),
            'applies_to_entity_type' => fake()->randomElement(['matter', 'contact', null]),
            'is_active' => true,
            'version' => 1,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withFields(int $count = 3): static
    {
        return $this->afterCreating(function (FormTemplate $template) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $template->fields()->create([
                    'key' => 'field_'.($i + 1),
                    'label_ar' => 'حقل '.($i + 1),
                    'label_en' => 'Field '.($i + 1),
                    'field_type' => fake()->randomElement(['text', 'textarea', 'number', 'date', 'boolean', 'select']),
                    'is_required' => $i === 0,
                    'sort_order' => $i,
                ]);
            }
        });
    }
}
