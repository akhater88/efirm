<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentVersion>
 */
class DocumentVersionFactory extends Factory
{
    protected $model = DocumentVersion::class;

    public function definition(): array
    {
        $body = $this->sampleBody();
        $bodyJson = json_encode($body);

        return [
            'workspace_id' => Workspace::factory(),
            'document_id' => Document::factory(),
            'version_number' => 1,
            'body' => $body,
            'body_hash' => hash('sha256', $bodyJson),
            'created_at' => now(),
        ];
    }

    /**
     * A minimal valid TipTap JSON document body.
     */
    private function sampleBody(): array
    {
        return [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 1],
                    'content' => [
                        ['type' => 'text', 'text' => fake()->sentence(3)],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => fake()->paragraph()],
                    ],
                ],
            ],
        ];
    }
}
