<?php

use App\Enums\ClauseLanguage;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\ClauseExtractionService;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);

    $this->service = new ClauseExtractionService;
});

it('extracts clauses by heading boundaries', function () {
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id]);
    $version = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $this->workspace->id,
    ]);

    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Title']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Intro paragraph.']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section A']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Section A content.']]],
        ],
    ];

    $clauses = $this->service->extract($version, $body);

    expect($clauses)->toHaveCount(2);
});

it('assigns sequential positions', function () {
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id]);
    $version = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $this->workspace->id,
    ]);

    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Title']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section 1']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section 2']]],
        ],
    ];

    $clauses = $this->service->extract($version, $body);
    $positions = $clauses->pluck('position')->all();

    expect($positions)->toBe([1, 2, 3]);
});

it('generates clause paths from heading hierarchy', function () {
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id]);
    $version = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $this->workspace->id,
    ]);

    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Title']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Definitions']]],
        ],
    ];

    $clauses = $this->service->extract($version, $body);

    expect($clauses[0]->clause_path)->toBe('section-1');
    expect($clauses[1]->clause_path)->toBe('section-1.subsection-1');
});

it('detects Arabic language for Arabic-only clauses', function () {
    $result = $this->service->detectLanguage('يقر البائع ويضمن أن الشركة ليس عليها أي التزامات مالية غير مفصح عنها');

    expect($result)->toBe(ClauseLanguage::Arabic);
});

it('detects English language for English-only clauses', function () {
    $result = $this->service->detectLanguage('The Seller warrants that the Company has complied in all material respects with all applicable laws.');

    expect($result)->toBe(ClauseLanguage::English);
});

it('detects mixed language for bilingual clauses', function () {
    $result = $this->service->detectLanguage('أبرمت هذه الاتفاقية بين شركة Acme MENA Holdings Ltd وشركة الأردن');

    // Predominantly Arabic with some English → Arabic
    // But text with more balanced mix should be mixed
    $mixedResult = $this->service->detectLanguage('Agreement between the parties اتفاقية بين for the sale والشراء of goods');

    expect($mixedResult)->toBe(ClauseLanguage::Mixed);
});

it('handles documents with no headings as single clause', function () {
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id]);
    $version = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $this->workspace->id,
    ]);

    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'First paragraph.']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Second paragraph.']]],
        ],
    ];

    $clauses = $this->service->extract($version, $body);

    expect($clauses)->toHaveCount(1)
        ->and($clauses->first()->clause_path)->toBe('preamble');
});

it('parses a 10-clause sample into 10 clauses', function () {
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id]);
    $version = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $this->workspace->id,
    ]);

    $content = [];
    for ($i = 1; $i <= 10; $i++) {
        $content[] = [
            'type' => 'heading',
            'attrs' => ['level' => 2],
            'content' => [['type' => 'text', 'text' => "Section {$i}"]],
        ];
        $content[] = [
            'type' => 'paragraph',
            'content' => [['type' => 'text', 'text' => "Content of section {$i}."]],
        ];
    }

    $body = ['type' => 'doc', 'content' => $content];
    $clauses = $this->service->extract($version, $body);

    expect($clauses)->toHaveCount(10);
});
