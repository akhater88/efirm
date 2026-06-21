<?php

namespace App\Services;

use App\Enums\ClauseLanguage;
use App\Models\DocumentClause;
use App\Models\DocumentVersion;
use App\Models\LibraryClause;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LibraryService
{
    public function __construct(
        private DocumentService $documentService,
    ) {}

    /**
     * Save a document clause into the workspace library.
     */
    public function saveFromDocument(DocumentClause $sourceClause, User $actor, array $attrs = []): LibraryClause
    {
        $version = $sourceClause->version;
        $document = $version->document;

        $bodyField = match ($sourceClause->language) {
            ClauseLanguage::Arabic => 'body_ar',
            ClauseLanguage::English => 'body_en',
            default => 'body_en',
        };

        $data = [
            'workspace_id' => $sourceClause->workspace_id,
            'title' => $attrs['title'] ?? $sourceClause->title ?? __('library.untitled_clause'),
            'clause_type' => $attrs['clause_type'] ?? $sourceClause->clause_type,
            'practice_area' => $attrs['practice_area'] ?? $document->matter?->practice_area?->value,
            'language' => $attrs['language'] ?? $sourceClause->language,
            $bodyField => $sourceClause->body,
            'risk_position' => $attrs['risk_position'] ?? null,
            'tags' => $attrs['tags'] ?? null,
            'source_document_id' => $document->id,
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ];

        // If bilingual, try to set both bodies
        if (($attrs['language'] ?? $sourceClause->language) === ClauseLanguage::Mixed) {
            $data['body_ar'] = $attrs['body_ar'] ?? $sourceClause->body;
            $data['body_en'] = $attrs['body_en'] ?? null;
        }

        return LibraryClause::create($data);
    }

    /**
     * Insert a library clause into a document, creating a new version.
     */
    public function insertIntoDocument(LibraryClause $libClause, DocumentVersion $targetVersion, User $actor, ?int $position = null): DocumentVersion
    {
        return DB::transaction(function () use ($libClause, $targetVersion, $actor, $position) {
            $document = $targetVersion->document;
            $body = $targetVersion->body;
            $content = $body['content'] ?? [];

            // Determine which body to insert (prefer user's locale, fallback to whatever exists)
            $locale = $actor->preferred_locale ?? 'ar';
            $clauseBody = match ($locale) {
                'ar' => $libClause->body_ar ?? $libClause->body_en,
                default => $libClause->body_en ?? $libClause->body_ar,
            };

            if (! $clauseBody) {
                throw new \RuntimeException('Library clause has no body content.');
            }

            // Extract the clause content nodes
            $clauseNodes = $clauseBody['content'] ?? [];

            // Add a heading for the clause title
            $insertNodes = [];
            if ($libClause->title) {
                $insertNodes[] = [
                    'type' => 'heading',
                    'attrs' => ['level' => 2],
                    'content' => [['type' => 'text', 'text' => $libClause->title]],
                ];
            }
            foreach ($clauseNodes as $node) {
                $insertNodes[] = $node;
            }

            // Insert at position or append
            if ($position !== null && $position < count($content)) {
                array_splice($content, $position, 0, $insertNodes);
            } else {
                $content = array_merge($content, $insertNodes);
            }

            $body['content'] = $content;

            // Create new version
            $newVersion = $this->documentService->createVersion(
                $document,
                $body,
                $actor,
                __('library.inserted_clause', ['title' => $libClause->title]),
            );

            // Record usage
            $libClause->recordUsage();

            return $newVersion;
        });
    }
}
