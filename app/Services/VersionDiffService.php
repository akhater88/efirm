<?php

namespace App\Services;

use App\Models\DocumentVersion;

class VersionDiffService
{
    /**
     * Generate a word-level diff between two document versions.
     *
     * Returns an array of diff blocks, each with type (unchanged/added/removed)
     * and the text content.
     *
     * @return array{old_version: int, new_version: int, blocks: array<int, array{type: string, text: string}>}
     */
    public function diff(DocumentVersion $oldVersion, DocumentVersion $newVersion): array
    {
        $oldText = $this->extractPlainText($oldVersion->body);
        $newText = $this->extractPlainText($newVersion->body);

        $oldWords = $this->tokenize($oldText);
        $newWords = $this->tokenize($newText);

        $diffBlocks = $this->computeWordDiff($oldWords, $newWords);

        return [
            'old_version' => $oldVersion->version_number,
            'new_version' => $newVersion->version_number,
            'old_version_id' => $oldVersion->id,
            'new_version_id' => $newVersion->id,
            'blocks' => $diffBlocks,
            'stats' => $this->computeStats($diffBlocks),
        ];
    }

    /**
     * Extract plain text from TipTap JSON body, preserving paragraph boundaries.
     */
    public function extractPlainText(array $body): string
    {
        $lines = [];
        $this->walkNodes($body['content'] ?? [], $lines);

        return implode("\n", $lines);
    }

    private function walkNodes(array $nodes, array &$lines, int $depth = 0): void
    {
        foreach ($nodes as $node) {
            $type = $node['type'] ?? '';

            if ($type === 'text') {
                // Append text to the current line
                if (empty($lines)) {
                    $lines[] = '';
                }
                $lines[count($lines) - 1] .= $node['text'] ?? '';

                continue;
            }

            if (in_array($type, ['paragraph', 'heading', 'listItem'])) {
                // Start a new line for block-level elements
                $prefix = '';
                if ($type === 'heading') {
                    $level = $node['attrs']['level'] ?? 1;
                    $prefix = str_repeat('#', $level).' ';
                }

                $lines[] = $prefix;

                if (isset($node['content'])) {
                    $this->walkNodes($node['content'], $lines, $depth + 1);
                }

                continue;
            }

            // Recurse into children for containers (bulletList, orderedList, table, etc.)
            if (isset($node['content'])) {
                $this->walkNodes($node['content'], $lines, $depth + 1);
            }
        }
    }

    /**
     * Tokenize text into words, preserving whitespace and newlines as tokens.
     *
     * @return string[]
     */
    private function tokenize(string $text): array
    {
        // Split on word boundaries, keeping whitespace and newlines
        $tokens = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return $tokens ?: [];
    }

    /**
     * Compute a word-level diff using the Myers algorithm via longest common subsequence.
     *
     * @param  string[]  $oldWords
     * @param  string[]  $newWords
     * @return array<int, array{type: string, text: string}>
     */
    private function computeWordDiff(array $oldWords, array $newWords): array
    {
        $lcs = $this->longestCommonSubsequence($oldWords, $newWords);

        $blocks = [];
        $oldIdx = 0;
        $newIdx = 0;
        $lcsIdx = 0;

        while ($oldIdx < count($oldWords) || $newIdx < count($newWords)) {
            if ($lcsIdx < count($lcs)) {
                // Collect removed words (in old but not in LCS)
                $removed = '';
                while ($oldIdx < count($oldWords) && $oldWords[$oldIdx] !== $lcs[$lcsIdx]) {
                    $removed .= $oldWords[$oldIdx];
                    $oldIdx++;
                }
                if ($removed !== '') {
                    $blocks[] = ['type' => 'removed', 'text' => $removed];
                }

                // Collect added words (in new but not in LCS)
                $added = '';
                while ($newIdx < count($newWords) && $newWords[$newIdx] !== $lcs[$lcsIdx]) {
                    $added .= $newWords[$newIdx];
                    $newIdx++;
                }
                if ($added !== '') {
                    $blocks[] = ['type' => 'added', 'text' => $added];
                }

                // Collect unchanged words (matching LCS)
                $unchanged = '';
                while ($lcsIdx < count($lcs) && $oldIdx < count($oldWords) && $newIdx < count($newWords)
                    && $oldWords[$oldIdx] === $lcs[$lcsIdx] && $newWords[$newIdx] === $lcs[$lcsIdx]) {
                    $unchanged .= $lcs[$lcsIdx];
                    $oldIdx++;
                    $newIdx++;
                    $lcsIdx++;
                }
                if ($unchanged !== '') {
                    $blocks[] = ['type' => 'unchanged', 'text' => $unchanged];
                }
            } else {
                // Remaining old words are removed
                $removed = '';
                while ($oldIdx < count($oldWords)) {
                    $removed .= $oldWords[$oldIdx];
                    $oldIdx++;
                }
                if ($removed !== '') {
                    $blocks[] = ['type' => 'removed', 'text' => $removed];
                }

                // Remaining new words are added
                $added = '';
                while ($newIdx < count($newWords)) {
                    $added .= $newWords[$newIdx];
                    $newIdx++;
                }
                if ($added !== '') {
                    $blocks[] = ['type' => 'added', 'text' => $added];
                }
            }
        }

        // Merge adjacent blocks of the same type
        return $this->mergeAdjacentBlocks($blocks);
    }

    /**
     * Compute the longest common subsequence of two arrays.
     *
     * @param  string[]  $a
     * @param  string[]  $b
     * @return string[]
     */
    private function longestCommonSubsequence(array $a, array $b): array
    {
        $m = count($a);
        $n = count($b);

        // Build LCS table
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($a[$i - 1] === $b[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Backtrack to find LCS
        $lcs = [];
        $i = $m;
        $j = $n;
        while ($i > 0 && $j > 0) {
            if ($a[$i - 1] === $b[$j - 1]) {
                array_unshift($lcs, $a[$i - 1]);
                $i--;
                $j--;
            } elseif ($dp[$i - 1][$j] > $dp[$i][$j - 1]) {
                $i--;
            } else {
                $j--;
            }
        }

        return $lcs;
    }

    /**
     * Merge adjacent blocks of the same type.
     *
     * @param  array<int, array{type: string, text: string}>  $blocks
     * @return array<int, array{type: string, text: string}>
     */
    private function mergeAdjacentBlocks(array $blocks): array
    {
        if (empty($blocks)) {
            return [];
        }

        $merged = [$blocks[0]];

        for ($i = 1; $i < count($blocks); $i++) {
            $last = &$merged[count($merged) - 1];
            if ($last['type'] === $blocks[$i]['type']) {
                $last['text'] .= $blocks[$i]['text'];
            } else {
                $merged[] = $blocks[$i];
            }
        }

        return $merged;
    }

    /**
     * Compute diff statistics.
     *
     * @param  array<int, array{type: string, text: string}>  $blocks
     * @return array{added_words: int, removed_words: int, unchanged_words: int}
     */
    private function computeStats(array $blocks): array
    {
        $stats = ['added_words' => 0, 'removed_words' => 0, 'unchanged_words' => 0];

        foreach ($blocks as $block) {
            $wordCount = count(preg_split('/\s+/u', trim($block['text']), -1, PREG_SPLIT_NO_EMPTY));
            $key = $block['type'].'_words';
            if (isset($stats[$key])) {
                $stats[$key] += $wordCount;
            }
        }

        return $stats;
    }
}
