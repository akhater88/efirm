<?php

use App\Enums\Role;
use App\Llm\LlmProvider;
use App\Llm\MockProvider;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\AiDocumentGenerationService;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);

    app()->instance(LlmProvider::class, new MockProvider);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);
});

it('blocks update on AiDocumentGeneration (append-only)', function () {
    $service = app(AiDocumentGenerationService::class);
    $gen = $service->generate('nda_levant', ['parties' => ['A']], $this->matter, $this->user);

    expect(fn () => $gen->update(['template_key' => 'changed']))
        ->toThrow(LogicException::class, 'Append-only');
});

it('blocks delete on AiDocumentGeneration (append-only)', function () {
    $service = app(AiDocumentGenerationService::class);
    $gen = $service->generate('nda_levant', ['parties' => ['A']], $this->matter, $this->user);

    expect(fn () => $gen->delete())
        ->toThrow(LogicException::class, 'Append-only');
});
