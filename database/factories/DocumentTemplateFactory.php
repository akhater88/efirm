<?php

namespace Database\Factories;

use App\Models\DocumentTemplate;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentTemplate>
 */
class DocumentTemplateFactory extends Factory
{
    protected $model = DocumentTemplate::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name_ar' => 'قالب '.fake()->word(),
            'name_en' => fake()->words(3, true).' Template',
            'document_type' => 'contract',
            'language' => 'bilingual',
            'body' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => 1],
                        'content' => [
                            ['type' => 'text', 'text' => '{{title}}'],
                        ],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'Between {{party_a}} and {{party_b}}'],
                        ],
                    ],
                ],
            ],
            'placeholder_schema' => [
                'title' => ['type' => 'text', 'required' => true],
                'party_a' => ['type' => 'text', 'required' => true],
                'party_b' => ['type' => 'text', 'required' => true],
            ],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function global(): static
    {
        return $this->state(['workspace_id' => null]);
    }
}
