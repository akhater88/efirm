<?php

namespace App\Services;

use App\Enums\DocumentLanguage;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function __construct(
        private ClauseExtractionService $clauseExtraction,
    ) {}

    /**
     * Create a new document with its first version.
     *
     * @param  array<string, mixed>  $body  TipTap JSON document body
     * @param  array<string, mixed>  $options  Optional: document_type, language_primary, metadata, original_file_url, change_summary
     */
    public function createDocument(Matter $matter, string $title, array $body, User $actor, array $options = []): Document
    {
        return DB::transaction(function () use ($matter, $title, $body, $actor, $options) {
            $document = Document::create([
                'workspace_id' => $matter->workspace_id,
                'matter_id' => $matter->id,
                'title' => $title,
                'document_type' => $options['document_type'] ?? DocumentType::Contract,
                'language_primary' => $options['language_primary'] ?? DocumentLanguage::Bilingual,
                'status' => DocumentStatus::Draft,
                'original_file_url' => $options['original_file_url'] ?? null,
                'metadata' => $options['metadata'] ?? null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $bodyJson = json_encode($body);
            $bodyHash = hash('sha256', $bodyJson);

            $version = DocumentVersion::create([
                'workspace_id' => $matter->workspace_id,
                'document_id' => $document->id,
                'version_number' => 1,
                'body' => $body,
                'body_hash' => $bodyHash,
                'change_summary' => $options['change_summary'] ?? null,
                'created_by_user_id' => $actor->id,
                'created_at' => now(),
            ]);

            $document->update(['current_version_id' => $version->id]);

            $this->clauseExtraction->extract($version, $body);

            return $document->fresh(['currentVersion', 'versions']);
        });
    }

    /**
     * Create a new version of an existing document.
     *
     * Returns null if the body hash matches the current version (no-op save).
     */
    public function createVersion(Document $document, array $body, User $actor, ?string $changeSummary = null): ?DocumentVersion
    {
        $bodyJson = json_encode($body);
        $bodyHash = hash('sha256', $bodyJson);

        // Skip if body hasn't changed
        $currentVersion = $document->currentVersion;
        if ($currentVersion && $currentVersion->body_hash === $bodyHash) {
            return null;
        }

        return DB::transaction(function () use ($document, $body, $bodyHash, $actor, $changeSummary) {
            $nextVersionNumber = ($document->versions()->max('version_number') ?? 0) + 1;

            $version = DocumentVersion::create([
                'workspace_id' => $document->workspace_id,
                'document_id' => $document->id,
                'version_number' => $nextVersionNumber,
                'body' => $body,
                'body_hash' => $bodyHash,
                'change_summary' => $changeSummary,
                'created_by_user_id' => $actor->id,
                'created_at' => now(),
            ]);

            $document->update([
                'current_version_id' => $version->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->clauseExtraction->extract($version, $body);

            return $version;
        });
    }
}
