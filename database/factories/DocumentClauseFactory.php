<?php

namespace Database\Factories;

use App\Enums\ClauseLanguage;
use App\Models\DocumentClause;
use App\Models\DocumentVersion;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentClause>
 */
class DocumentClauseFactory extends Factory
{
    protected $model = DocumentClause::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'document_version_id' => DocumentVersion::factory(),
            'position' => fake()->numberBetween(1, 20),
            'clause_path' => 'section-'.fake()->numberBetween(1, 10),
            'title' => fake()->sentence(3),
            'body' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => fake()->paragraph()],
                        ],
                    ],
                ],
            ],
            'language' => ClauseLanguage::English,
        ];
    }

    public function arabic(): static
    {
        return $this->state(['language' => ClauseLanguage::Arabic]);
    }

    public function mixed(): static
    {
        return $this->state(['language' => ClauseLanguage::Mixed]);
    }
}
