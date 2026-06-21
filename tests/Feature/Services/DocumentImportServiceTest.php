<?php

use App\Enums\DocumentLanguage;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentImportService;
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

    $this->service = app(DocumentImportService::class);
});

it('imports a bilingual .docx and creates document with version 1', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/01-bilateral-nda-ar-en.docx'),
        '01-bilateral-nda-ar-en.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    $document = $this->service->importDocx($file, $this->matter, $this->user);

    expect($document)->toBeInstanceOf(Document::class)
        ->and($document->versions)->toHaveCount(1)
        ->and($document->currentVersion)->not->toBeNull()
        ->and($document->currentVersion->version_number)->toBe(1)
        ->and($document->currentVersion->body)->toBeArray()
        ->and($document->currentVersion->body['type'])->toBe('doc');
});

it('extracts clauses from imported document', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/01-bilateral-nda-ar-en.docx'),
        '01-bilateral-nda-ar-en.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    $document = $this->service->importDocx($file, $this->matter, $this->user);

    expect($document->currentVersion->clauses->count())->toBeGreaterThanOrEqual(2);
});

it('auto-detects title from first heading', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/02-spa-ar.docx'),
        '02-spa-ar.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    $document = $this->service->importDocx($file, $this->matter, $this->user);

    // Should extract Arabic title from first H1
    expect($document->title)->not->toBe('02-spa-ar');
});

it('uses provided title over auto-detected', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/02-spa-ar.docx'),
        '02-spa-ar.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    $document = $this->service->importDocx($file, $this->matter, $this->user, [
        'title' => 'Custom Title',
    ]);

    expect($document->title)->toBe('Custom Title');
});

it('auto-detects Arabic language for Arabic-only document', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/02-spa-ar.docx'),
        '02-spa-ar.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    $document = $this->service->importDocx($file, $this->matter, $this->user);

    expect($document->language_primary)->toBe(DocumentLanguage::Arabic);
});

it('auto-detects English language for English-only document', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/03-supply-agreement-en.docx'),
        '03-supply-agreement-en.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    $document = $this->service->importDocx($file, $this->matter, $this->user);

    expect($document->language_primary)->toBe(DocumentLanguage::English);
});

it('auto-detects bilingual language for mixed document', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/01-bilateral-nda-ar-en.docx'),
        '01-bilateral-nda-ar-en.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    $document = $this->service->importDocx($file, $this->matter, $this->user);

    expect($document->language_primary)->toBe(DocumentLanguage::Bilingual);
});

it('preserves bold and italic formatting from docx', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/02-spa-ar.docx'),
        '02-spa-ar.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    $document = $this->service->importDocx($file, $this->matter, $this->user);
    $body = $document->currentVersion->body;

    // Walk the JSON tree and check for bold marks
    $hasBold = false;
    $walk = function ($node) use (&$walk, &$hasBold) {
        if (($node['type'] ?? '') === 'text' && ! empty($node['marks'])) {
            foreach ($node['marks'] as $mark) {
                if ($mark['type'] === 'bold') {
                    $hasBold = true;
                }
            }
        }
        foreach ($node['content'] ?? [] as $child) {
            $walk($child);
        }
    };
    $walk($body);

    expect($hasBold)->toBeTrue();
});

it('parses .docx to valid TipTap JSON structure', function () {
    $body = $this->service->parseDocxToTiptapJson(
        base_path('tests/fixtures/docx/01-bilateral-nda-ar-en.docx')
    );

    expect($body)->toBeArray()
        ->and($body['type'])->toBe('doc')
        ->and($body['content'])->toBeArray()
        ->and($body['content'])->not->toBeEmpty();

    // All top-level nodes should have a valid type
    foreach ($body['content'] as $node) {
        expect($node['type'])->toBeIn(['heading', 'paragraph', 'bulletList', 'orderedList', 'table', 'blockquote', 'horizontalRule']);
    }
});
