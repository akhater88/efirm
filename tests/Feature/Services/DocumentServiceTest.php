<?php

use App\Models\Contact;
use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentService;

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

    $this->service = app(DocumentService::class);

    $this->sampleBody = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'heading',
                'attrs' => ['level' => 1],
                'content' => [['type' => 'text', 'text' => 'Agreement Title']],
            ],
            [
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'This agreement is entered into...']],
            ],
            [
                'type' => 'heading',
                'attrs' => ['level' => 2],
                'content' => [['type' => 'text', 'text' => 'Section 1. Definitions']],
            ],
            [
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'The following terms shall have the meanings...']],
            ],
        ],
    ];
});

it('creates a document with version 1', function () {
    $document = $this->service->createDocument(
        $this->matter,
        'Test Contract',
        $this->sampleBody,
        $this->user,
    );

    expect($document)->toBeInstanceOf(Document::class)
        ->and($document->title)->toBe('Test Contract')
        ->and($document->versions)->toHaveCount(1)
        ->and($document->versions->first()->version_number)->toBe(1);
});

it('sets current_version_id on create', function () {
    $document = $this->service->createDocument(
        $this->matter,
        'Test Contract',
        $this->sampleBody,
        $this->user,
    );

    expect($document->current_version_id)->not->toBeNull()
        ->and($document->currentVersion)->not->toBeNull()
        ->and($document->currentVersion->version_number)->toBe(1);
});

it('creates version 2 on save with different body', function () {
    $document = $this->service->createDocument(
        $this->matter,
        'Test Contract',
        $this->sampleBody,
        $this->user,
    );

    $modifiedBody = $this->sampleBody;
    $modifiedBody['content'][1]['content'][0]['text'] = 'Modified agreement text.';

    $version2 = $this->service->createVersion($document, $modifiedBody, $this->user, 'Updated intro');

    expect($version2)->not->toBeNull()
        ->and($version2->version_number)->toBe(2)
        ->and($document->fresh()->currentVersion->id)->toBe($version2->id);
});

it('preserves version 1 when version 2 is created', function () {
    $document = $this->service->createDocument(
        $this->matter,
        'Test Contract',
        $this->sampleBody,
        $this->user,
    );

    $v1Body = $document->currentVersion->body;

    $modifiedBody = $this->sampleBody;
    $modifiedBody['content'][1]['content'][0]['text'] = 'Modified text.';

    $this->service->createVersion($document, $modifiedBody, $this->user);

    $document->refresh();

    expect($document->versions)->toHaveCount(2);

    $v1 = $document->versions->firstWhere('version_number', 1);
    expect($v1->body)->toEqual($v1Body);
});

it('skips save when body_hash matches current version', function () {
    $document = $this->service->createDocument(
        $this->matter,
        'Test Contract',
        $this->sampleBody,
        $this->user,
    );

    // Save with same body
    $result = $this->service->createVersion($document, $this->sampleBody, $this->user);

    expect($result)->toBeNull()
        ->and($document->fresh()->versions)->toHaveCount(1);
});

it('increments version_number sequentially', function () {
    $document = $this->service->createDocument(
        $this->matter,
        'Test Contract',
        $this->sampleBody,
        $this->user,
    );

    for ($i = 2; $i <= 5; $i++) {
        $body = $this->sampleBody;
        $body['content'][1]['content'][0]['text'] = "Version {$i} text";
        $this->service->createVersion($document, $body, $this->user);
    }

    $document->refresh();
    $versionNumbers = $document->versions->pluck('version_number')->sort()->values()->all();

    expect($versionNumbers)->toBe([1, 2, 3, 4, 5]);
});

it('sets created_by_user_id on version', function () {
    $document = $this->service->createDocument(
        $this->matter,
        'Test Contract',
        $this->sampleBody,
        $this->user,
    );

    expect($document->currentVersion->created_by_user_id)->toBe($this->user->id);
});

it('extracts clauses on document creation', function () {
    $document = $this->service->createDocument(
        $this->matter,
        'Test Contract',
        $this->sampleBody,
        $this->user,
    );

    // sampleBody has 2 headings, so should extract 2 heading-based clauses + possible preamble
    $clauses = $document->currentVersion->clauses;
    expect($clauses->count())->toBeGreaterThanOrEqual(2);
});

it('extracts clauses on version save', function () {
    $document = $this->service->createDocument(
        $this->matter,
        'Test Contract',
        $this->sampleBody,
        $this->user,
    );

    $modifiedBody = $this->sampleBody;
    $modifiedBody['content'][] = [
        'type' => 'heading',
        'attrs' => ['level' => 2],
        'content' => [['type' => 'text', 'text' => 'Section 2. New Section']],
    ];
    $modifiedBody['content'][] = [
        'type' => 'paragraph',
        'content' => [['type' => 'text', 'text' => 'New section content.']],
    ];

    $version2 = $this->service->createVersion($document, $modifiedBody, $this->user);

    expect($version2->clauses->count())->toBeGreaterThan(
        $document->versions->firstWhere('version_number', 1)->clauses->count()
    );
});
