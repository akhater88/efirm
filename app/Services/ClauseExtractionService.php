<?php

namespace App\Services;

use App\Enums\ClauseLanguage;
use App\Models\DocumentClause;
use App\Models\DocumentVersion;
use Illuminate\Support\Collection;

class ClauseExtractionService
{
    /**
     * Extract clauses from TipTap JSON by heading boundaries.
     *
     * Each heading (h1/h2/h3) starts a new clause. Content between
     * headings belongs to the preceding clause. Content before the
     * first heading becomes clause at position 0.
     *
     * @return Collection<int, DocumentClause>
     */
    public function extract(DocumentVersion $version, array $body): Collection
    {
        $nodes = $body['content'] ?? [];
        $clauses = [];
        $currentClause = null;
        $position = 0;
        $sectionCounters = [1 => 0, 2 => 0, 3 => 0];

        foreach ($nodes as $node) {
            $type = $node['type'] ?? '';

            if ($type === 'heading') {
                // Save previous clause if it has content
                if ($currentClause !== null) {
                    $clauses[] = $currentClause;
                }

                $level = $node['attrs']['level'] ?? 1;
                $title = $this->extractTextFromNode($node);

                // Update section counters for clause path
                $sectionCounters[$level]++;
                // Reset lower-level counters
                for ($i = $level + 1; $i <= 3; $i++) {
                    $sectionCounters[$i] = 0;
                }

                $clausePath = $this->buildClausePath($sectionCounters, $level);
                $position++;

                $currentClause = [
                    'position' => $position,
                    'clause_path' => $clausePath,
                    'title' => $title,
                    'body_nodes' => [$node],
                    'text_content' => $title,
                ];
            } else {
                // Non-heading node — belongs to current clause or creates preamble
                if ($currentClause === null) {
                    $position++;
                    $currentClause = [
                        'position' => $position,
                        'clause_path' => 'preamble',
                        'title' => null,
                        'body_nodes' => [],
                        'text_content' => '',
                    ];
                }

                $currentClause['body_nodes'][] = $node;
                $currentClause['text_content'] .= ' '.$this->extractTextFromNode($node);
            }
        }

        // Save last clause
        if ($currentClause !== null) {
            $clauses[] = $currentClause;
        }

        // Persist clauses
        $created = new Collection;
        foreach ($clauses as $clauseData) {
            $language = $this->detectLanguage($clauseData['text_content']);

            $clause = DocumentClause::create([
                'workspace_id' => $version->workspace_id,
                'document_version_id' => $version->id,
                'position' => $clauseData['position'],
                'clause_path' => $clauseData['clause_path'],
                'title' => $clauseData['title'],
                'body' => ['type' => 'doc', 'content' => $clauseData['body_nodes']],
                'language' => $language,
            ]);

            $created->push($clause);
        }

        return $created;
    }

    /**
     * Detect the language of a text string based on Arabic Unicode character ratio.
     */
    public function detectLanguage(string $text): ClauseLanguage
    {
        $text = trim($text);
        if ($text === '') {
            return ClauseLanguage::Mixed;
        }

        // Count Arabic Unicode characters
        preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text, $arabicMatches);
        $arabicCount = count($arabicMatches[0]);

        // Count total alphabetic characters (excluding digits, punctuation, whitespace)
        preg_match_all('/[\p{L}]/u', $text, $alphaMatches);
        $totalAlpha = count($alphaMatches[0]);

        if ($totalAlpha === 0) {
            return ClauseLanguage::Mixed;
        }

        $ratio = $arabicCount / $totalAlpha;

        if ($ratio >= 0.7) {
            return ClauseLanguage::Arabic;
        }

        if ($ratio <= 0.1) {
            return ClauseLanguage::English;
        }

        return ClauseLanguage::Mixed;
    }

    /**
     * Extract plain text from a TipTap JSON node recursively.
     */
    private function extractTextFromNode(array $node): string
    {
        if (($node['type'] ?? '') === 'text') {
            return $node['text'] ?? '';
        }

        $text = '';
        foreach ($node['content'] ?? [] as $child) {
            $text .= $this->extractTextFromNode($child);
        }

        return $text;
    }

    /**
     * Build a clause path like "section-1", "section-2.subsection-1".
     */
    private function buildClausePath(array $counters, int $level): string
    {
        $parts = [];
        $labels = [1 => 'section', 2 => 'subsection', 3 => 'clause'];

        for ($i = 1; $i <= $level; $i++) {
            if ($counters[$i] > 0) {
                $parts[] = ($labels[$i] ?? "level-{$i}").'-'.$counters[$i];
            }
        }

        return implode('.', $parts) ?: 'section-1';
    }
}
