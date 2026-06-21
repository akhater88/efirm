<?php

namespace App\Services;

use App\Enums\DocumentLanguage;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\ListItemRun;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Title;
use PhpOffice\PhpWord\IOFactory;

class DocumentImportService
{
    public function __construct(
        private DocumentService $documentService,
    ) {}

    /**
     * Import a .docx file and create a Document with Version 1.
     *
     * @param  array<string, mixed>  $options  Optional: title, document_type, language_primary
     */
    public function importDocx(UploadedFile $file, Matter $matter, User $actor, array $options = []): Document
    {
        // Parse .docx to TipTap JSON
        $body = $this->parseDocxToTiptapJson($file->getRealPath());

        // Auto-detect title from filename or first heading
        $title = $options['title'] ?? $this->extractTitle($body, $file->getClientOriginalName());

        // Auto-detect language
        $language = $options['language_primary'] ?? $this->detectDocumentLanguage($body);

        // Create document via DocumentService
        $document = $this->documentService->createDocument($matter, $title, $body, $actor, [
            'document_type' => $options['document_type'] ?? DocumentType::Contract,
            'language_primary' => $language,
            'change_summary' => __('documents.imported_from_docx'),
        ]);

        // Store original .docx blob
        $storagePath = "{$matter->workspace_id}/documents/{$document->id}/original.docx";
        Storage::disk($this->storageDisk())->put($storagePath, file_get_contents($file->getRealPath()));
        $document->update(['original_file_url' => $storagePath]);

        return $document;
    }

    /**
     * Parse a .docx file into TipTap JSON document structure.
     */
    public function parseDocxToTiptapJson(string $filePath): array
    {
        $phpWord = IOFactory::load($filePath, 'Word2007');
        $content = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $nodes = $this->convertElement($element);
                if ($nodes !== null) {
                    if (is_array($nodes) && isset($nodes[0])) {
                        // Multiple nodes returned (e.g., list items)
                        foreach ($nodes as $node) {
                            $content[] = $node;
                        }
                    } else {
                        $content[] = $nodes;
                    }
                }
            }
        }

        // Filter out empty paragraphs at the very end
        while (! empty($content) && $this->isEmptyParagraph(end($content))) {
            array_pop($content);
        }

        return [
            'type' => 'doc',
            'content' => ! empty($content) ? $content : [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '']]],
            ],
        ];
    }

    /**
     * Convert a PHPWord element to TipTap JSON node(s).
     */
    private function convertElement(mixed $element): ?array
    {
        if ($element instanceof Title) {
            return $this->convertTitle($element);
        }

        if ($element instanceof TextRun) {
            return $this->convertTextRun($element);
        }

        if ($element instanceof Text) {
            return $this->convertTextToNode($element);
        }

        if ($element instanceof ListItem) {
            return $this->convertListItem($element);
        }

        if ($element instanceof ListItemRun) {
            return $this->convertListItemRun($element);
        }

        if ($element instanceof Table) {
            return $this->convertTable($element);
        }

        if ($element instanceof TextBreak) {
            return ['type' => 'paragraph', 'content' => []];
        }

        return null;
    }

    private function convertTitle(Title $title): array
    {
        $level = min($title->getDepth() ?: 1, 6);
        $textContent = $this->extractInlineContent($title);

        return [
            'type' => 'heading',
            'attrs' => ['level' => $level],
            'content' => ! empty($textContent) ? $textContent : [['type' => 'text', 'text' => $title->getText() ?: '']],
        ];
    }

    private function convertTextRun(TextRun $textRun): array
    {
        $content = $this->extractInlineContent($textRun);

        return [
            'type' => 'paragraph',
            'content' => ! empty($content) ? $content : [],
        ];
    }

    private function convertTextToNode(Text $text): array
    {
        $runs = $this->textToRuns($text);

        return [
            'type' => 'paragraph',
            'content' => ! empty($runs) ? $runs : [],
        ];
    }

    private function convertListItem(ListItem $listItem): array
    {
        $textContent = $listItem->getTextObject();
        $content = [];

        if (is_string($textContent)) {
            $content = [['type' => 'text', 'text' => $textContent]];
        } elseif ($textContent instanceof TextRun) {
            $content = $this->extractInlineContent($textContent);
        } elseif ($textContent !== null) {
            $content = [['type' => 'text', 'text' => (string) $textContent]];
        }

        $depth = $listItem->getDepth();
        $listType = $listItem->getStyle()?->getNumStyle();

        $itemNode = [
            'type' => 'listItem',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => ! empty($content) ? $content : [['type' => 'text', 'text' => '']],
                ],
            ],
        ];

        // Wrap in the appropriate list type
        $isOrdered = $listType && str_contains(strtolower($listType), 'num');

        return [
            'type' => $isOrdered ? 'orderedList' : 'bulletList',
            'content' => [$itemNode],
        ];
    }

    private function convertListItemRun(ListItemRun $listItemRun): array
    {
        $content = $this->extractInlineContent($listItemRun);

        $itemNode = [
            'type' => 'listItem',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => ! empty($content) ? $content : [['type' => 'text', 'text' => '']],
                ],
            ],
        ];

        return [
            'type' => 'bulletList',
            'content' => [$itemNode],
        ];
    }

    private function convertTable(Table $table): array
    {
        $rows = [];
        foreach ($table->getRows() as $row) {
            $cells = [];
            foreach ($row->getCells() as $cell) {
                $cellContent = [];
                foreach ($cell->getElements() as $cellElement) {
                    $node = $this->convertElement($cellElement);
                    if ($node !== null) {
                        if (isset($node[0])) {
                            foreach ($node as $n) {
                                $cellContent[] = $n;
                            }
                        } else {
                            $cellContent[] = $node;
                        }
                    }
                }

                $cells[] = [
                    'type' => 'tableCell',
                    'content' => ! empty($cellContent) ? $cellContent : [
                        ['type' => 'paragraph', 'content' => []],
                    ],
                ];
            }

            $rows[] = [
                'type' => 'tableRow',
                'content' => $cells,
            ];
        }

        return [
            'type' => 'table',
            'content' => $rows,
        ];
    }

    /**
     * Extract inline content (text runs with marks) from a compound element.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractInlineContent(mixed $element): array
    {
        $runs = [];

        $elements = method_exists($element, 'getElements') ? $element->getElements() : [];

        foreach ($elements as $child) {
            if ($child instanceof Text) {
                $textRuns = $this->textToRuns($child);
                foreach ($textRuns as $run) {
                    $runs[] = $run;
                }
            } elseif ($child instanceof TextBreak) {
                $runs[] = ['type' => 'hardBreak'];
            }
        }

        return $runs;
    }

    /**
     * Convert a PHPWord Text element to TipTap text node(s) with marks.
     *
     * @return array<int, array<string, mixed>>
     */
    private function textToRuns(Text $text): array
    {
        $textStr = $text->getText();
        if ($textStr === null || $textStr === '') {
            return [];
        }

        $node = ['type' => 'text', 'text' => $textStr];

        $marks = [];
        $font = $text->getFontStyle();

        if ($font && ! is_string($font)) {
            if ($font->isBold()) {
                $marks[] = ['type' => 'bold'];
            }
            if ($font->isItalic()) {
                $marks[] = ['type' => 'italic'];
            }
            if ($font->getUnderline() && $font->getUnderline() !== 'none') {
                $marks[] = ['type' => 'underline'];
            }
            if ($font->isStrikethrough()) {
                $marks[] = ['type' => 'strike'];
            }
        }

        if (! empty($marks)) {
            $node['marks'] = $marks;
        }

        return [$node];
    }

    /**
     * Extract document title from TipTap JSON body or filename.
     */
    private function extractTitle(array $body, string $filename): string
    {
        // Try to find the first heading
        foreach ($body['content'] ?? [] as $node) {
            if (($node['type'] ?? '') === 'heading') {
                $text = $this->extractTextFromNode($node);
                if (! empty(trim($text))) {
                    return trim($text);
                }
            }
        }

        // Fallback to filename without extension
        return pathinfo($filename, PATHINFO_FILENAME);
    }

    /**
     * Auto-detect the primary language of a document body.
     */
    private function detectDocumentLanguage(array $body): DocumentLanguage
    {
        $fullText = '';
        foreach ($body['content'] ?? [] as $node) {
            $fullText .= ' '.$this->extractTextFromNode($node);
        }

        $fullText = trim($fullText);
        if (empty($fullText)) {
            return DocumentLanguage::Bilingual;
        }

        preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $fullText, $arabicMatches);
        $arabicCount = count($arabicMatches[0]);

        preg_match_all('/[\p{L}]/u', $fullText, $alphaMatches);
        $totalAlpha = count($alphaMatches[0]);

        if ($totalAlpha === 0) {
            return DocumentLanguage::Bilingual;
        }

        $ratio = $arabicCount / $totalAlpha;

        if ($ratio >= 0.9) {
            return DocumentLanguage::Arabic;
        }
        if ($ratio <= 0.1) {
            return DocumentLanguage::English;
        }

        return DocumentLanguage::Bilingual;
    }

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

    private function isEmptyParagraph(array $node): bool
    {
        return ($node['type'] ?? '') === 'paragraph' && empty($node['content']);
    }

    private function storageDisk(): string
    {
        return config('filesystems.documents_disk', 'local');
    }
}
