<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\DocumentVersion;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DocumentShare>
 */
class DocumentShareFactory extends Factory
{
    protected $model = DocumentShare::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'document_id' => Document::factory(),
            'version_id' => DocumentVersion::factory(),
            'token' => Str::random(64),
            'format' => 'docx',
        ];
    }

    public function withExpiry(int $days = 7): static
    {
        return $this->state(['expires_at' => now()->addDays($days)]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay()]);
    }
}
