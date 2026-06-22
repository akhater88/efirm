<?php

use App\Enums\AiDocGenerationStatus;
use App\Enums\Role;
use App\Llm\LlmProvider;
use App\Llm\LlmRequestOptions;
use App\Llm\LlmResponse;
use App\Llm\MockProvider;
use App\Models\AiDocumentGeneration;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
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

    app()->instance(LlmProvider::class, new MockProvider);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);
});

it('generates a document from a template via API', function () {
    $response = $this->postJson("/api/v1/matters/{$this->matter->id}/ai/generate-document", [
        'template_key' => 'nda_levant',
        'intent_payload' => [
            'parties' => ['شركة الأردن', 'Acme MENA'],
            'governing_law' => 'Jordan',
            'language' => 'bilingual',
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'complete')
        ->assertJsonPath('data.template_key', 'nda_levant');

    expect(AiDocumentGeneration::count())->toBe(1);
    expect(Document::count())->toBeGreaterThanOrEqual(1);
});

it('creates audit row with all fields populated', function () {
    $this->postJson("/api/v1/matters/{$this->matter->id}/ai/generate-document", [
        'template_key' => 'spa_jordan',
        'intent_payload' => ['parties' => ['Party A', 'Party B']],
    ]);

    $gen = AiDocumentGeneration::first();

    expect($gen->workspace_id)->toBe($this->workspace->id)
        ->and($gen->matter_id)->toBe($this->matter->id)
        ->and($gen->user_id)->toBe($this->user->id)
        ->and($gen->template_key)->toBe('spa_jordan')
        ->and($gen->intent_payload)->toBeArray()
        ->and($gen->prompt_used)->not->toBeEmpty()
        ->and($gen->model_used)->not->toBeNull()
        ->and($gen->input_tokens)->toBeGreaterThan(0)
        ->and($gen->generated_document_id)->not->toBeNull();
});

it('creates Document with Version 1 from generated content', function () {
    $this->postJson("/api/v1/matters/{$this->matter->id}/ai/generate-document", [
        'template_key' => 'nda_levant',
        'intent_payload' => ['title' => 'Custom NDA Title'],
    ]);

    $gen = AiDocumentGeneration::first();
    $doc = Document::find($gen->generated_document_id);

    expect($doc)->not->toBeNull()
        ->and($doc->title)->toBe('Custom NDA Title')
        ->and($doc->currentVersion)->not->toBeNull()
        ->and($doc->currentVersion->body)->toBeArray()
        ->and($doc->currentVersion->body['type'])->toBe('doc');
});

it('handles LLM failure gracefully', function () {
    $mock = new MockProvider;
    $mock->withResponse(''); // Empty response will still work
    app()->instance(LlmProvider::class, $mock);

    // Force an actual failure by making the mock throw
    $failingMock = new class implements LlmProvider
    {
        public function complete(string $prompt, LlmRequestOptions $options): LlmResponse
        {
            throw new RuntimeException('LLM service unavailable');
        }

        public function name(): string
        {
            return 'failing-mock';
        }
    };
    app()->instance(LlmProvider::class, $failingMock);

    $this->postJson("/api/v1/matters/{$this->matter->id}/ai/generate-document", [
        'template_key' => 'nda_levant',
        'intent_payload' => ['parties' => ['A', 'B']],
    ]);

    $gen = AiDocumentGeneration::first();

    expect($gen->status)->toBe(AiDocGenerationStatus::Failed)
        ->and($gen->error_message)->toContain('unavailable')
        ->and($gen->generated_document_id)->toBeNull();
});

it('validates required fields', function () {
    $response = $this->postJson("/api/v1/matters/{$this->matter->id}/ai/generate-document", []);

    $response->assertUnprocessable();
});

it('workspace-isolates generated documents', function () {
    $this->postJson("/api/v1/matters/{$this->matter->id}/ai/generate-document", [
        'template_key' => 'nda_levant',
        'intent_payload' => ['parties' => ['A']],
    ]);

    $gen = AiDocumentGeneration::first();
    expect($gen->workspace_id)->toBe($this->workspace->id);

    $doc = Document::find($gen->generated_document_id);
    expect($doc->workspace_id)->toBe($this->workspace->id);
});
