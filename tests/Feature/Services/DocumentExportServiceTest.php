<?php

use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentExportService;
use App\Services\DocumentImportService;
use App\Services\DocumentService;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    $this->exportService = app(DocumentExportService::class);
    $this->documentService = app(DocumentService::class);
    $this->importService = app(DocumentImportService::class);
});

it('exports a document to valid .docx binary', function () {
    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Test Title']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Test paragraph content.']]],
        ],
    ];

    $document = $this->documentService->createDocument($this->matter, 'Test', $body, $this->user);

    $content = $this->exportService->exportToDocx($document);

    // Valid .docx starts with PK (ZIP signature)
    expect(substr($content, 0, 2))->toBe('PK')
        ->and(strlen($content))->toBeGreaterThan(1000);
});

it('exports with correct filename', function () {
    $body = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Test']]]]];
    $document = $this->documentService->createDocument($this->matter, 'اتفاقية شراء أسهم', $body, $this->user);

    $filename = $this->exportService->getFilename($document);

    expect($filename)->toBe('اتفاقية شراء أسهم.docx');
});

it('exports a specific version with version number in filename', function () {
    $body = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'V1']]]]];
    $document = $this->documentService->createDocument($this->matter, 'Contract', $body, $this->user);

    $body2 = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'V2']]]]];
    $this->documentService->createVersion($document, $body2, $this->user);

    $v1 = $document->versions()->where('version_number', 1)->first();
    $filename = $this->exportService->getFilename($document, $v1);

    expect($filename)->toBe('Contract_V1.docx');
});

it('preserves headings in exported .docx', function () {
    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Main Title']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section 1']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Content here.']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section 2']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'More content.']]],
        ],
    ];

    $document = $this->documentService->createDocument($this->matter, 'Test', $body, $this->user);
    $content = $this->exportService->exportToDocx($document);

    // Write to temp file and re-import to verify structure
    $tempFile = tempnam(sys_get_temp_dir(), 'roundtrip_');
    file_put_contents($tempFile, $content);

    $reimported = $this->importService->parseDocxToTiptapJson($tempFile);
    @unlink($tempFile);

    // Count headings in re-imported content
    $headingCount = collect($reimported['content'])
        ->where('type', 'heading')
        ->count();

    expect($headingCount)->toBe(3); // 1 H1 + 2 H2
});

it('preserves bold and italic formatting in round-trip', function () {
    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [
                ['type' => 'text', 'text' => 'Normal text '],
                ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold text'],
                ['type' => 'text', 'text' => ' and '],
                ['type' => 'text', 'marks' => [['type' => 'italic']], 'text' => 'italic text'],
                ['type' => 'text', 'text' => '.'],
            ]],
        ],
    ];

    $document = $this->documentService->createDocument($this->matter, 'Formatting Test', $body, $this->user);
    $content = $this->exportService->exportToDocx($document);

    $tempFile = tempnam(sys_get_temp_dir(), 'roundtrip_');
    file_put_contents($tempFile, $content);

    $reimported = $this->importService->parseDocxToTiptapJson($tempFile);
    @unlink($tempFile);

    // Check for bold marks in reimported content
    $hasBold = false;
    $hasItalic = false;
    $walk = function ($node) use (&$walk, &$hasBold, &$hasItalic) {
        if (($node['type'] ?? '') === 'text' && ! empty($node['marks'])) {
            foreach ($node['marks'] as $mark) {
                if ($mark['type'] === 'bold') {
                    $hasBold = true;
                }
                if ($mark['type'] === 'italic') {
                    $hasItalic = true;
                }
            }
        }
        foreach ($node['content'] ?? [] as $child) {
            $walk($child);
        }
    };
    $walk($reimported);

    expect($hasBold)->toBeTrue()
        ->and($hasItalic)->toBeTrue();
});

it('round-trips a bilingual document with Arabic paragraphs', function () {
    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'اتفاقية']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'أبرمت هذه الاتفاقية بين الطرفين.']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Governing Law']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'This agreement shall be governed by the laws of Jordan.']]],
        ],
    ];

    $document = $this->documentService->createDocument($this->matter, 'Bilingual', $body, $this->user);
    $content = $this->exportService->exportToDocx($document);

    $tempFile = tempnam(sys_get_temp_dir(), 'roundtrip_');
    file_put_contents($tempFile, $content);

    $reimported = $this->importService->parseDocxToTiptapJson($tempFile);
    @unlink($tempFile);

    // Verify paragraph count preserved
    $originalParas = collect($body['content'])->where('type', 'paragraph')->count();
    $reimportedParas = collect($reimported['content'])->where('type', 'paragraph')->count();

    expect($reimportedParas)->toBe($originalParas);

    // Verify Arabic text is present
    $fullText = '';
    $extractText = function ($node) use (&$extractText, &$fullText) {
        if (($node['type'] ?? '') === 'text') {
            $fullText .= $node['text'] ?? '';
        }
        foreach ($node['content'] ?? [] as $child) {
            $extractText($child);
        }
    };
    $extractText($reimported);

    expect($fullText)->toContain('اتفاقية')
        ->and($fullText)->toContain('Jordan');
});

it('round-trips the NDA fixture without structural loss', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/01-bilateral-nda-ar-en.docx'),
        '01-bilateral-nda-ar-en.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    // Import
    $document = $this->importService->importDocx($file, $this->matter, $this->user);
    $importedBody = $document->currentVersion->body;

    // Count original structure
    $originalHeadings = collect($importedBody['content'])->where('type', 'heading')->count();
    $originalParas = collect($importedBody['content'])->where('type', 'paragraph')->count();

    // Export
    $exported = $this->exportService->exportToDocx($document);
    $tempFile = tempnam(sys_get_temp_dir(), 'roundtrip_');
    file_put_contents($tempFile, $exported);

    // Re-import
    $reimported = $this->importService->parseDocxToTiptapJson($tempFile);
    @unlink($tempFile);

    $reimportedHeadings = collect($reimported['content'])->where('type', 'heading')->count();
    $reimportedParas = collect($reimported['content'])->where('type', 'paragraph')->count();

    // Structure should be preserved (allow some variance for empty paragraphs)
    expect($reimportedHeadings)->toBe($originalHeadings)
        ->and($reimportedParas)->toBeGreaterThanOrEqual($originalParas - 2);
});
