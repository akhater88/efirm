<?php

use App\Models\Contact;
use App\Models\DocumentShare;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentService;
use Illuminate\Support\Str;

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
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    $body = ['type' => 'doc', 'content' => [
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Shared content.']]],
    ]];

    $this->document = app(DocumentService::class)->createDocument($matter, 'Shared Doc', $body, $this->user);
});

it('downloads a shared document via public token', function () {
    $share = DocumentShare::create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'version_id' => $this->document->current_version_id,
        'token' => Str::random(64),
        'format' => 'docx',
        'created_by_user_id' => $this->user->id,
    ]);

    // Public access — no auth needed
    auth()->logout();

    $response = $this->get("/share/{$share->token}");

    $response->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    // Verify download count incremented
    $share->refresh();
    expect($share->download_count)->toBe(1)
        ->and($share->last_accessed_at)->not->toBeNull();
});

it('increments download count on each access', function () {
    $share = DocumentShare::create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'version_id' => $this->document->current_version_id,
        'token' => Str::random(64),
        'format' => 'docx',
        'created_by_user_id' => $this->user->id,
    ]);

    auth()->logout();

    $this->get("/share/{$share->token}");
    $this->get("/share/{$share->token}");
    $this->get("/share/{$share->token}");

    $share->refresh();
    expect($share->download_count)->toBe(3);
});

it('returns 410 for revoked share', function () {
    $share = DocumentShare::create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'version_id' => $this->document->current_version_id,
        'token' => Str::random(64),
        'format' => 'docx',
        'created_by_user_id' => $this->user->id,
    ]);

    $share->delete(); // soft delete = revoked
    auth()->logout();

    $response = $this->get("/share/{$share->token}");

    $response->assertStatus(410);
});

it('returns 410 for expired share', function () {
    $share = DocumentShare::create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'version_id' => $this->document->current_version_id,
        'token' => Str::random(64),
        'format' => 'docx',
        'expires_at' => now()->subDay(),
        'created_by_user_id' => $this->user->id,
    ]);

    auth()->logout();

    $response = $this->get("/share/{$share->token}");

    $response->assertStatus(410);
});

it('returns 404 for non-existent token', function () {
    auth()->logout();

    $response = $this->get('/share/nonexistent-token-12345');

    $response->assertNotFound();
});
