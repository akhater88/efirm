<?php

use App\Enums\ObligationStatus;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Obligation;
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
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);

    $body = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Test.']]]]];
    $this->document = app(DocumentService::class)->createDocument($matter, 'Contract', $body, $this->user);
});

it('creates an obligation', function () {
    $response = $this->postJson("/api/v1/documents/{$this->document->id}/obligations", [
        'title' => 'First payment due',
        'obligation_type' => 'payment',
        'responsible_party' => 'counterparty',
        'due_date' => now()->addDays(30)->toDateString(),
        'monetary_amount' => 50000,
        'monetary_currency' => 'USD',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'First payment due');
});

it('lists obligations for a document', function () {
    Obligation::factory()->count(3)->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
    ]);

    $response = $this->getJson("/api/v1/documents/{$this->document->id}/obligations");

    $response->assertOk()
        ->assertJsonCount(3, 'data.data');
});

it('marks an obligation as completed', function () {
    $obligation = Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
    ]);

    $response = $this->postJson("/api/v1/obligations/{$obligation->id}/complete");

    $response->assertOk()
        ->assertJsonPath('data.status', 'completed');

    $obligation->refresh();
    expect($obligation->status)->toBe(ObligationStatus::Completed)
        ->and($obligation->completed_at)->not->toBeNull()
        ->and($obligation->completed_by_id)->toBe($this->user->id);
});

it('updates an obligation', function () {
    $obligation = Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
    ]);

    $response = $this->patchJson("/api/v1/obligations/{$obligation->id}", [
        'title' => 'Updated title',
        'status' => 'in_progress',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated title')
        ->assertJsonPath('data.status', 'in_progress');
});

it('deletes an obligation', function () {
    $obligation = Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
    ]);

    $response = $this->deleteJson("/api/v1/obligations/{$obligation->id}");

    $response->assertNoContent();
    expect(Obligation::find($obligation->id))->toBeNull();
});

it('scopes upcoming obligations correctly', function () {
    // Due in 5 days (upcoming)
    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'due_date' => now()->addDays(5),
        'status' => ObligationStatus::Pending,
    ]);

    // Due in 30 days (not within 14-day window)
    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'due_date' => now()->addDays(30),
        'status' => ObligationStatus::Pending,
    ]);

    // Already completed
    Obligation::factory()->completed()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'due_date' => now()->addDays(3),
    ]);

    expect(Obligation::upcoming(14)->count())->toBe(1);
});
