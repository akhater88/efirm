<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentVersion;
use App\Models\Matter;
use App\Models\User;

class DocumentTemplateService
{
    /**
     * Create a Document from a template, replacing {{placeholder}} tokens in the body.
     *
     * @param  array<string, string>  $replacements  Key-value pairs for placeholder replacement.
     */
    public function createFromTemplate(
        DocumentTemplate $template,
        Matter $matter,
        User $user,
        array $replacements = [],
        ?string $title = null,
    ): Document {
        $body = $template->body;
        $resolvedBody = $this->replacePlaceholders($body, $replacements);

        $document = Document::create([
            'workspace_id' => $matter->workspace_id,
            'matter_id' => $matter->id,
            'title' => $title ?? ($replacements['title'] ?? $template->name_en),
            'document_type' => $template->document_type,
            'language_primary' => $template->language,
            'status' => 'draft',
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]);

        $bodyJson = json_encode($resolvedBody);

        $version = DocumentVersion::create([
            'workspace_id' => $matter->workspace_id,
            'document_id' => $document->id,
            'version_number' => 1,
            'body' => $resolvedBody,
            'body_hash' => hash('sha256', $bodyJson ?: ''),
            'change_summary' => 'Created from template: '.$template->name_en,
            'created_by_user_id' => $user->id,
            'created_at' => now(),
        ]);

        $document->update(['current_version_id' => $version->id]);

        return $document->fresh();
    }

    /**
     * Recursively replace {{placeholder}} tokens in TipTap JSON body.
     *
     * @param  array<string, mixed>  $body
     * @param  array<string, string>  $replacements
     * @return array<string, mixed>
     */
    public function replacePlaceholders(array $body, array $replacements): array
    {
        $json = json_encode($body);

        if ($json === false) {
            return $body;
        }

        foreach ($replacements as $key => $value) {
            $json = str_replace('{{'.$key.'}}', addslashes($value), $json);
        }

        $result = json_decode($json, true);

        return is_array($result) ? $result : $body;
    }
}
