<?php

use App\Enums\AiInteractionType;
use App\Llm\LlmProvider;
use App\Llm\MockProvider;
use App\Models\AiInteraction;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\AiOrchestrationService;
use App\Services\DocumentService;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create(['preferred_locale' => 'ar']);
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);

    // Bind MockProvider for tests
    $this->mock = new MockProvider;
    app()->instance(LlmProvider::class, $this->mock);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    $body = ['type' => 'doc', 'content' => [
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Liability']]],
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'The seller liability is limited to 10% of the purchase price.']]],
    ]];

    $this->document = app(DocumentService::class)->createDocument($matter, 'Test', $body, $this->user);
    $this->clause = $this->document->currentVersion->clauses->first();
    $this->service = app(AiOrchestrationService::class);
});

it('completes a draft operation and creates audit row', function () {
    $interaction = $this->service->draft($this->document, 'Draft a confidentiality clause', 'en', $this->user);

    expect($interaction)->toBeInstanceOf(AiInteraction::class)
        ->and($interaction->interaction_type)->toBe(AiInteractionType::Draft)
        ->and($interaction->prompt)->toContain('confidentiality')
        ->and($interaction->response)->not->toBeEmpty()
        ->and($interaction->model)->toBe('mock-model')
        ->and($interaction->input_tokens)->toBeGreaterThan(0)
        ->and($interaction->output_tokens)->toBeGreaterThan(0)
        ->and($interaction->cost_usd)->toBeGreaterThan(0)
        ->and($interaction->user_id)->toBe($this->user->id)
        ->and($interaction->document_id)->toBe($this->document->id);
});

it('completes a review operation', function () {
    $interaction = $this->service->review($this->clause, $this->user);

    expect($interaction->interaction_type)->toBe(AiInteractionType::Review)
        ->and($interaction->document_clause_id)->toBe($this->clause->id)
        ->and($interaction->response)->toContain('risk');
});

it('completes a suggest operation', function () {
    $interaction = $this->service->suggest($this->clause, 'make it more favourable to buyer', $this->user);

    expect($interaction->interaction_type)->toBe(AiInteractionType::Suggest)
        ->and($interaction->prompt)->toContain('favourable');
});

it('completes a translate operation', function () {
    $interaction = $this->service->translate($this->clause, 'ar', $this->user);

    expect($interaction->interaction_type)->toBe(AiInteractionType::Translate)
        ->and($interaction->response)->not->toBeEmpty();
});

it('completes an explain operation', function () {
    $interaction = $this->service->explain($this->clause, $this->user);

    expect($interaction->interaction_type)->toBe(AiInteractionType::Explain)
        ->and($interaction->response)->not->toBeEmpty();
});

it('persists full audit detail on every AI call', function () {
    $this->service->draft($this->document, 'Test intent', 'en', $this->user);

    expect(AiInteraction::count())->toBe(1);

    $interaction = AiInteraction::first();
    expect($interaction->workspace_id)->toBe($this->workspace->id)
        ->and($interaction->prompt)->not->toBeEmpty()
        ->and($interaction->response)->not->toBeEmpty()
        ->and($interaction->model)->not->toBeEmpty()
        ->and($interaction->latency_ms)->toBeGreaterThanOrEqual(0);
});

it('marks interaction as accepted', function () {
    $interaction = $this->service->draft($this->document, 'Test', 'en', $this->user);

    expect($interaction->was_accepted)->toBeNull();

    $this->service->markAccepted($interaction, true);

    expect($interaction->fresh()->was_accepted)->toBeTrue();
});

it('marks interaction as rejected', function () {
    $interaction = $this->service->draft($this->document, 'Test', 'en', $this->user);

    $this->service->markAccepted($interaction, false);

    expect($interaction->fresh()->was_accepted)->toBeFalse();
});

it('uses MockProvider deterministic responses', function () {
    $this->mock->withResponse('Custom mock response for testing.');

    $interaction = $this->service->draft($this->document, 'Test', 'en', $this->user);

    expect($interaction->response)->toBe('Custom mock response for testing.');
});
