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

    $body = ['type' => 'doc', 'content' => [
        ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Agreement']]],
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'This is a test.']]],
    ]];

    $this->document = app(DocumentService::class)->createDocument($matter, 'Test Agreement', $body, $this->user);
});

it('exports current version as .docx download', function () {
    $response = $this->get("/api/v1/documents/{$this->document->id}/export");

    $response->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
        ->assertHeader('content-disposition');

    // Verify response body starts with PK (ZIP/docx signature)
    expect(substr($response->getContent(), 0, 2))->toBe('PK');
});

it('exports a specific version', function () {
    // Create V2
    $body2 = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'V2 content']]]]];
    app(DocumentService::class)->createVersion($this->document, $body2, $this->user);

    $v1 = $this->document->versions()->where('version_number', 1)->first();

    $response = $this->get("/api/v1/documents/{$this->document->id}/export?version_id={$v1->id}");

    $response->assertOk();
    expect(substr($response->getContent(), 0, 2))->toBe('PK');
});

it('returns 404 for non-existent version_id', function () {
    $response = $this->get("/api/v1/documents/{$this->document->id}/export?version_id=nonexistent");

    $response->assertNotFound();
});
