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

    $documentService = app(DocumentService::class);

    $bodyV1 = ['type' => 'doc', 'content' => [
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Original text.']]],
    ]];
    $this->document = $documentService->createDocument($matter, 'Test', $bodyV1, $this->user);

    $bodyV2 = ['type' => 'doc', 'content' => [
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Modified text with additions.']]],
    ]];
    $documentService->createVersion($this->document, $bodyV2, $this->user, 'Added more text');

    $this->document->refresh();
});

it('returns diff between two versions', function () {
    $v1 = $this->document->versions()->where('version_number', 1)->first();
    $v2 = $this->document->versions()->where('version_number', 2)->first();

    $response = $this->getJson("/api/v1/documents/{$this->document->id}/versions/{$v1->id}/diff?against={$v2->id}");

    $response->assertOk()
        ->assertJsonStructure(['data' => ['old_version', 'new_version', 'blocks', 'stats']]);
});

it('returns 422 when against parameter is missing', function () {
    $v1 = $this->document->versions()->where('version_number', 1)->first();

    $response = $this->getJson("/api/v1/documents/{$this->document->id}/versions/{$v1->id}/diff");

    $response->assertStatus(422);
});

it('restores an older version as a new latest version', function () {
    $v1 = $this->document->versions()->where('version_number', 1)->first();

    $response = $this->postJson("/api/v1/documents/{$this->document->id}/versions/{$v1->id}/restore");

    $response->assertCreated()
        ->assertJsonPath('data.version_number', 3);

    $this->document->refresh();
    expect($this->document->currentVersion->version_number)->toBe(3)
        ->and($this->document->versions->count())->toBe(3);
});

it('restored version has the same body as the original', function () {
    $v1 = $this->document->versions()->where('version_number', 1)->first();
    $originalBody = $v1->body;

    $this->postJson("/api/v1/documents/{$this->document->id}/versions/{$v1->id}/restore");

    $this->document->refresh();
    $restoredBody = $this->document->currentVersion->body;

    expect($restoredBody)->toEqual($originalBody);
});
