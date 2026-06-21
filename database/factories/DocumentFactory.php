<?php

namespace Database\Factories;

use App\Enums\DocumentLanguage;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Matter;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'matter_id' => Matter::factory(),
            'title' => fake()->sentence(3),
            'document_type' => DocumentType::Contract,
            'language_primary' => DocumentLanguage::Bilingual,
            'status' => DocumentStatus::Draft,
        ];
    }

    public function signed(): static
    {
        return $this->state(['status' => DocumentStatus::Signed]);
    }

    public function underReview(): static
    {
        return $this->state(['status' => DocumentStatus::UnderReview]);
    }

    public function memo(): static
    {
        return $this->state(['document_type' => DocumentType::Memo]);
    }

    public function arabic(): static
    {
        return $this->state(['language_primary' => DocumentLanguage::Arabic]);
    }

    public function english(): static
    {
        return $this->state(['language_primary' => DocumentLanguage::English]);
    }
}
