<?php

namespace Database\Factories;

use App\Enums\ClauseLanguage;
use App\Enums\PracticeArea;
use App\Models\LibraryClause;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibraryClause>
 */
class LibraryClauseFactory extends Factory
{
    protected $model = LibraryClause::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'title' => fake()->sentence(3),
            'clause_type' => fake()->randomElement(['limitation_of_liability', 'governing_law', 'indemnification', 'termination', 'confidentiality']),
            'practice_area' => PracticeArea::CommercialContracts,
            'language' => ClauseLanguage::English,
            'body_en' => [
                'type' => 'doc',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => fake()->paragraph()]]],
                ],
            ],
        ];
    }

    public function bilingual(): static
    {
        return $this->state([
            'language' => ClauseLanguage::Mixed,
            'body_ar' => [
                'type' => 'doc',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'بند قانوني نموذجي باللغة العربية.']]],
                ],
            ],
        ]);
    }
}
