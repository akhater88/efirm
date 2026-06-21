<?php

use App\Enums\Role;
use App\Models\Contact;
use App\Models\LibraryClause;
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
});

it('lists library clauses', function () {
    LibraryClause::factory()->count(3)->create(['workspace_id' => $this->workspace->id]);

    $response = $this->getJson('/api/v1/library/clauses');

    $response->assertOk()
        ->assertJsonCount(3, 'data.data');
});

it('creates a library clause', function () {
    $response = $this->postJson('/api/v1/library/clauses', [
        'title' => 'Standard NDA Clause',
        'clause_type' => 'confidentiality',
        'language' => 'en',
        'body_en' => ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'The parties agree to maintain confidentiality.']]]]],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Standard NDA Clause');
});

it('shows a single library clause', function () {
    $clause = LibraryClause::factory()->create(['workspace_id' => $this->workspace->id]);

    $response = $this->getJson("/api/v1/library/clauses/{$clause->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $clause->id);
});

it('updates a library clause', function () {
    $clause = LibraryClause::factory()->create(['workspace_id' => $this->workspace->id]);

    $response = $this->putJson("/api/v1/library/clauses/{$clause->id}", [
        'title' => 'Updated Title',
        'risk_position' => 'favourable',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated Title')
        ->assertJsonPath('data.risk_position', 'favourable');
});

it('deletes a library clause (soft delete)', function () {
    $clause = LibraryClause::factory()->create(['workspace_id' => $this->workspace->id]);

    $response = $this->deleteJson("/api/v1/library/clauses/{$clause->id}");

    $response->assertNoContent();
    expect(LibraryClause::find($clause->id))->toBeNull();
    expect(LibraryClause::withTrashed()->find($clause->id))->not->toBeNull();
});

it('saves a document clause to the library', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);

    $body = ['type' => 'doc', 'content' => [
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Governing Law']]],
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'This agreement shall be governed by Jordan law.']]],
    ]];

    $document = app(DocumentService::class)->createDocument($matter, 'Test', $body, $this->user);
    $clause = $document->currentVersion->clauses->first();

    $response = $this->postJson("/api/v1/library/clauses/from-document-clause/{$clause->id}", [
        'title' => 'Governing Law - Jordan',
        'clause_type' => 'governing_law',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Governing Law - Jordan')
        ->assertJsonPath('data.source_document_id', $document->id);
});

it('inserts a library clause into a document', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);

    $body = ['type' => 'doc', 'content' => [
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Existing content.']]],
    ]];
    $document = app(DocumentService::class)->createDocument($matter, 'Test', $body, $this->user);

    $libClause = LibraryClause::factory()->create([
        'workspace_id' => $this->workspace->id,
        'title' => 'Confidentiality',
    ]);

    $response = $this->postJson("/api/v1/documents/{$document->id}/insert-library-clause", [
        'library_clause_id' => $libClause->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.version_number', 2)
        ->assertJsonPath('data.usage_count', 1);
});

it('stores bilingual clause with both AR and EN bodies', function () {
    $response = $this->postJson('/api/v1/library/clauses', [
        'title' => 'بند السرية / Confidentiality',
        'language' => 'mixed',
        'body_ar' => ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'يلتزم الطرفان بالحفاظ على سرية المعلومات.']]]]],
        'body_en' => ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'The parties agree to maintain confidentiality.']]]]],
    ]);

    $response->assertCreated();

    $clause = LibraryClause::first();
    expect($clause->body_ar)->toBeArray()
        ->and($clause->body_en)->toBeArray();
});

it('workspace isolation prevents cross-workspace access', function () {
    $otherWorkspace = Workspace::factory()->create();
    LibraryClause::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->getJson('/api/v1/library/clauses');

    $response->assertOk()
        ->assertJsonCount(0, 'data.data');
});

it('filters by clause_type', function () {
    LibraryClause::factory()->create(['workspace_id' => $this->workspace->id, 'clause_type' => 'governing_law']);
    LibraryClause::factory()->create(['workspace_id' => $this->workspace->id, 'clause_type' => 'termination']);

    $response = $this->getJson('/api/v1/library/clauses?clause_type=governing_law');

    $response->assertOk()
        ->assertJsonCount(1, 'data.data');
});
