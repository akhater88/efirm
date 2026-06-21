<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\ListItem as ListItemStyle;

class DocumentExportService
{
    /**
     * Export a document version to .docx binary string.
     */
    public function exportToDocx(Document $document, ?DocumentVersion $version = null): string
    {
        $version ??= $document->currentVersion;

        if (! $version) {
            throw new \RuntimeException('Document has no version to export.');
        }

        $phpWord = new PhpWord;

        // Set default font
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);

        // Define heading styles so they're recognized on re-import
        $phpWord->addTitleStyle(1, ['size' => 22, 'bold' => true], ['spaceAfter' => 120, 'spaceBefore' => 240]);
        $phpWord->addTitleStyle(2, ['size' => 18, 'bold' => true], ['spaceAfter' => 100, 'spaceBefore' => 160]);
        $phpWord->addTitleStyle(3, ['size' => 15, 'bold' => true], ['spaceAfter' => 80, 'spaceBefore' => 120]);

        $section = $phpWord->addSection();

        $this->renderNodes($section, $version->body['content'] ?? []);

        // Write to temp file and return contents
        $tempFile = tempnam(sys_get_temp_dir(), 'docx_export_');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    /**
     * Get the filename for a document export.
     */
    public function getFilename(Document $document, ?DocumentVersion $version = null): string
    {
        $title = preg_replace('/[^\p{L}\p{N}\s\-_.]/u', '', $document->title);
        $title = trim($title) ?: 'document';

        if ($version && $version->id !== $document->current_version_id) {
            return "{$title}_V{$version->version_number}.docx";
        }

        return "{$title}.docx";
    }

    /**
     * Render TipTap JSON nodes into a PHPWord section.
     */
    private function renderNodes($container, array $nodes): void
    {
        foreach ($nodes as $node) {
            $type = $node['type'] ?? '';

            match ($type) {
                'heading' => $this->renderHeading($container, $node),
                'paragraph' => $this->renderParagraph($container, $node),
                'bulletList' => $this->renderList($container, $node, 'bullet'),
                'orderedList' => $this->renderList($container, $node, 'ordered'),
                'table' => $this->renderTable($container, $node),
                default => null,
            };
        }
    }

    private function renderHeading($container, array $node): void
    {
        $level = $node['attrs']['level'] ?? 1;
        $text = $this->extractText($node);

        // Use PHPWord's addTitle for proper heading styles (recognized on re-import)
        $container->addTitle($text, $level);
    }

    private function renderParagraph($container, array $node): void
    {
        $text = $this->extractText($node);
        $isRtl = $this->isArabicText($text);

        $alignment = $node['attrs']['textAlign'] ?? null;
        if (! $alignment) {
            $alignment = $isRtl ? 'right' : 'left';
        }

        $jc = match ($alignment) {
            'right' => Jc::END,
            'center' => Jc::CENTER,
            default => Jc::START,
        };

        $textRun = $container->addTextRun([
            'alignment' => $jc,
            'bidirectional' => $isRtl,
            'spaceAfter' => 100,
        ]);

        $this->renderInlineContent($textRun, $node['content'] ?? []);
    }

    private function renderList($container, array $node, string $listType): void
    {
        $items = $node['content'] ?? [];

        foreach ($items as $listItem) {
            if (($listItem['type'] ?? '') !== 'listItem') {
                continue;
            }

            foreach ($listItem['content'] ?? [] as $itemContent) {
                $text = $this->extractText($itemContent);
                $isRtl = $this->isArabicText($text);

                $listStyleType = $listType === 'ordered'
                    ? ListItemStyle::TYPE_NUMBER
                    : ListItemStyle::TYPE_BULLET_FILLED;

                $listItemRun = $container->addListItemRun(0, $listStyleType, [
                    'alignment' => $isRtl ? Jc::END : Jc::START,
                    'bidirectional' => $isRtl,
                ]);

                $this->renderInlineContent($listItemRun, $itemContent['content'] ?? []);
            }
        }
    }

    private function renderTable($container, array $node): void
    {
        $rows = $node['content'] ?? [];
        if (empty($rows)) {
            return;
        }

        // Count max cells to set column widths
        $maxCols = 0;
        foreach ($rows as $row) {
            $cellCount = count($row['content'] ?? []);
            $maxCols = max($maxCols, $cellCount);
        }

        $tableWidth = 9000; // twips (~6.25 inches)
        $cellWidth = $maxCols > 0 ? intval($tableWidth / $maxCols) : $tableWidth;

        $table = $container->addTable([
            'borderSize' => 4,
            'borderColor' => 'cccccc',
            'cellMargin' => 80,
        ]);

        foreach ($rows as $row) {
            $table->addRow();

            foreach ($row['content'] ?? [] as $cell) {
                $tableCell = $table->addCell($cellWidth);

                foreach ($cell['content'] ?? [] as $cellContent) {
                    $type = $cellContent['type'] ?? '';
                    if ($type === 'paragraph') {
                        $this->renderParagraph($tableCell, $cellContent);
                    } elseif ($type === 'heading') {
                        $this->renderHeading($tableCell, $cellContent);
                    }
                }
            }
        }
    }

    /**
     * Render inline content (text nodes with marks) into a PHPWord TextRun.
     *
     * @param  array<string, mixed>  $defaultFontStyle
     */
    private function renderInlineContent($textRun, array $content, array $defaultFontStyle = []): void
    {
        if (empty($content)) {
            // Empty paragraph — add a zero-width space so PHPWord doesn't collapse it
            $textRun->addText('', array_merge(['size' => 12], $defaultFontStyle));

            return;
        }

        foreach ($content as $node) {
            $type = $node['type'] ?? '';

            if ($type === 'text') {
                $fontStyle = array_merge([
                    'size' => 12,
                    'name' => 'Arial',
                ], $defaultFontStyle);

                $marks = $node['marks'] ?? [];
                foreach ($marks as $mark) {
                    match ($mark['type'] ?? '') {
                        'bold' => $fontStyle['bold'] = true,
                        'italic' => $fontStyle['italic'] = true,
                        'underline' => $fontStyle['underline'] = 'single',
                        'strike' => $fontStyle['strikethrough'] = true,
                        default => null,
                    };
                }

                // Set RTL for Arabic text
                $text = $node['text'] ?? '';
                if ($this->isArabicText($text)) {
                    $fontStyle['rtl'] = true;
                }

                $textRun->addText($text, $fontStyle);
            } elseif ($type === 'hardBreak') {
                $textRun->addTextBreak();
            }
        }
    }

    /**
     * Extract plain text from a node recursively.
     */
    private function extractText(array $node): string
    {
        if (($node['type'] ?? '') === 'text') {
            return $node['text'] ?? '';
        }

        $text = '';
        foreach ($node['content'] ?? [] as $child) {
            $text .= $this->extractText($child);
        }

        return $text;
    }

    /**
     * Detect if text is predominantly Arabic.
     */
    private function isArabicText(string $text): bool
    {
        if (empty($text)) {
            return false;
        }

        preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text, $arabicMatches);
        $arabicCount = count($arabicMatches[0]);

        preg_match_all('/[\p{L}]/u', $text, $alphaMatches);
        $totalAlpha = count($alphaMatches[0]);

        return $totalAlpha > 0 && ($arabicCount / $totalAlpha) > 0.3;
    }
}
