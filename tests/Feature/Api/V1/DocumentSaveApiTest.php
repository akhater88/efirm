<?php

use App\Enums\Role;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentService;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    Sanctum::actingAs($this->user);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Test']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Initial content.']]],
        ],
    ];

    $this->document = app(DocumentService::class)->createDocument($matter, 'Test Doc', $body, $this->user);
});

it('saves a new version via API', function () {
    $newBody = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Test']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Modified content.']]],
        ],
    ];

    $response = $this->postJson("/api/v1/documents/{$this->document->id}/save", [
        'current_version_id' => $this->document->current_version_id,
        'body' => $newBody,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.version_number', 2);

    expect($this->document->fresh()->currentVersion->version_number)->toBe(2);
});

it('returns 409 on version conflict', function () {
    $staleVersionId = $this->document->current_version_id;

    // Simulate another save happening first
    $otherBody = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Other user edit.']]],
        ],
    ];
    app(DocumentService::class)->createVersion($this->document, $otherBody, $this->user, 'other edit');

    // Now try to save with the stale version ID
    $myBody = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'My edit.']]],
        ],
    ];

    $response = $this->postJson("/api/v1/documents/{$this->document->id}/save", [
        'current_version_id' => $staleVersionId,
        'body' => $myBody,
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'conflict');
});

it('skips save when body has not changed', function () {
    $currentBody = $this->document->currentVersion->body;

    $response = $this->postJson("/api/v1/documents/{$this->document->id}/save", [
        'current_version_id' => $this->document->current_version_id,
        'body' => $currentBody,
    ]);

    $response->assertOk();
    expect($this->document->fresh()->versions->count())->toBe(1);
});

it('lists versions for a document', function () {
    // Create additional versions
    $body2 = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'V2']]]]];
    $body3 = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'V3']]]]];
    app(DocumentService::class)->createVersion($this->document, $body2, $this->user);
    app(DocumentService::class)->createVersion($this->document, $body3, $this->user);

    $response = $this->getJson("/api/v1/documents/{$this->document->id}/versions");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single version with body', function () {
    $versionId = $this->document->current_version_id;

    $response = $this->getJson("/api/v1/documents/{$this->document->id}/versions/{$versionId}");

    $response->assertOk()
        ->assertJsonPath('data.version_number', 1)
        ->assertJsonStructure(['data' => ['body']]);
});

it('extracts clauses on every save', function () {
    $newBody = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Title']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Intro.']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section 1']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Section 1 content.']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section 2']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Section 2 content.']]],
        ],
    ];

    $this->postJson("/api/v1/documents/{$this->document->id}/save", [
        'current_version_id' => $this->document->current_version_id,
        'body' => $newBody,
    ]);

    $latestVersion = $this->document->fresh()->currentVersion;
    expect($latestVersion->clauses->count())->toBe(3); // Title + Section 1 + Section 2
});
