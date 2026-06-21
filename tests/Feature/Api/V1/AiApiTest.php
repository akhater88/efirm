<?php

use App\Enums\Role;
use App\Llm\LlmProvider;
use App\Llm\MockProvider;
use App\Models\AiInteraction;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentService;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create(['preferred_locale' => 'ar']);
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    Sanctum::actingAs($this->user);

    // Use MockProvider
    app()->instance(LlmProvider::class, new MockProvider);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    $body = ['type' => 'doc', 'content' => [
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Liability Clause']]],
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'The seller liability is limited to 10% of the purchase price.']]],
    ]];

    $this->document = app(DocumentService::class)->createDocument($matter, 'Test Agreement', $body, $this->user);
    $this->clause = $this->document->currentVersion->clauses->first();
});

it('drafts a clause via API', function () {
    $response = $this->postJson("/api/v1/documents/{$this->document->id}/ai/draft", [
        'intent' => 'Draft a confidentiality clause for an NDA',
        'language' => 'en',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'interaction_type', 'response', 'model', 'input_tokens', 'output_tokens']]);

    expect(AiInteraction::count())->toBe(1);
});

it('reviews a clause via API', function () {
    $response = $this->postJson("/api/v1/documents/{$this->document->id}/ai/review", [
        'clause_id' => $this->clause->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.interaction_type', 'review');
});

it('suggests a revision via API', function () {
    $response = $this->postJson("/api/v1/documents/{$this->document->id}/ai/suggest", [
        'clause_id' => $this->clause->id,
        'instruction' => 'Make it more favourable to the buyer',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.interaction_type', 'suggest');
});

it('translates a clause via API', function () {
    $response = $this->postJson("/api/v1/documents/{$this->document->id}/ai/translate", [
        'clause_id' => $this->clause->id,
        'target_language' => 'ar',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.interaction_type', 'translate');
});

it('explains a clause via API', function () {
    $response = $this->postJson("/api/v1/documents/{$this->document->id}/ai/explain", [
        'clause_id' => $this->clause->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.interaction_type', 'explain');
});

it('accepts an AI interaction', function () {
    $draftResponse = $this->postJson("/api/v1/documents/{$this->document->id}/ai/draft", [
        'intent' => 'Test draft',
        'language' => 'en',
    ]);

    $interactionId = $draftResponse->json('data.id');

    $response = $this->postJson("/api/v1/ai-interactions/{$interactionId}/accept");

    $response->assertOk()
        ->assertJsonPath('data.was_accepted', true);

    expect(AiInteraction::find($interactionId)->was_accepted)->toBeTrue();
});

it('rejects an AI interaction', function () {
    $draftResponse = $this->postJson("/api/v1/documents/{$this->document->id}/ai/draft", [
        'intent' => 'Test draft',
        'language' => 'en',
    ]);

    $interactionId = $draftResponse->json('data.id');

    $response = $this->postJson("/api/v1/ai-interactions/{$interactionId}/reject");

    $response->assertOk()
        ->assertJsonPath('data.was_accepted', false);
});

it('records full audit detail for every AI call', function () {
    $this->postJson("/api/v1/documents/{$this->document->id}/ai/review", [
        'clause_id' => $this->clause->id,
    ]);

    $interaction = AiInteraction::first();

    expect($interaction->workspace_id)->toBe($this->workspace->id)
        ->and($interaction->user_id)->toBe($this->user->id)
        ->and($interaction->document_id)->toBe($this->document->id)
        ->and($interaction->document_clause_id)->toBe($this->clause->id)
        ->and($interaction->prompt)->not->toBeEmpty()
        ->and($interaction->response)->not->toBeEmpty()
        ->and($interaction->model)->not->toBeEmpty()
        ->and($interaction->input_tokens)->toBeGreaterThan(0)
        ->and($interaction->output_tokens)->toBeGreaterThan(0)
        ->and($interaction->latency_ms)->toBeGreaterThanOrEqual(0);
});
