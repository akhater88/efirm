<?php

use App\Enums\Role;
use App\Models\Contact;
use App\Models\DocumentShare;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentService;
use Illuminate\Support\Str;
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
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Test content.']]],
    ]];

    $this->document = app(DocumentService::class)->createDocument($matter, 'Test', $body, $this->user);
});

it('creates a share link', function () {
    $response = $this->postJson("/api/v1/documents/{$this->document->id}/shares", [
        'recipient_email' => 'lawyer@acme.com',
        'format' => 'docx',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'token', 'url', 'recipient_email', 'format', 'is_active']]);

    expect(DocumentShare::count())->toBe(1);
});

it('creates a share link with expiry', function () {
    $response = $this->postJson("/api/v1/documents/{$this->document->id}/shares", [
        'expires_at' => now()->addDays(7)->toIso8601String(),
    ]);

    $response->assertCreated();

    $share = DocumentShare::first();
    expect($share->expires_at)->not->toBeNull()
        ->and($share->isActive())->toBeTrue();
});

it('lists active shares for a document', function () {
    DocumentShare::create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'version_id' => $this->document->current_version_id,
        'token' => Str::random(64),
        'format' => 'docx',
        'created_by_user_id' => $this->user->id,
    ]);

    $response = $this->getJson("/api/v1/documents/{$this->document->id}/shares");

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('revokes a share link (soft delete)', function () {
    $share = DocumentShare::create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'version_id' => $this->document->current_version_id,
        'token' => Str::random(64),
        'format' => 'docx',
        'created_by_user_id' => $this->user->id,
    ]);

    $response = $this->deleteJson("/api/v1/documents/{$this->document->id}/shares/{$share->id}");

    $response->assertNoContent();
    expect(DocumentShare::find($share->id))->toBeNull();
    expect(DocumentShare::withTrashed()->find($share->id)->isRevoked())->toBeTrue();
});
