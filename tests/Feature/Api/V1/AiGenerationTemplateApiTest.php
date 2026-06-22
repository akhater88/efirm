<?php

use App\Enums\Role;
use App\Models\AiGenerationTemplate;
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
});

it('lists templates (system + workspace-specific)', function () {
    // System template
    AiGenerationTemplate::create([
        'workspace_id' => null,
        'key' => 'nda_levant',
        'name_ar' => 'اتفاقية عدم إفصاح',
        'name_en' => 'NDA (Levant)',
        'prompt_template' => 'Generate an NDA...',
    ]);

    // Workspace-specific
    AiGenerationTemplate::create([
        'workspace_id' => $this->workspace->id,
        'key' => 'custom_contract',
        'name_ar' => 'عقد مخصص',
        'name_en' => 'Custom Contract',
        'prompt_template' => 'Generate a custom contract...',
    ]);

    $response = $this->getJson('/api/v1/ai-generation-templates');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('workspace-specific template overrides system for same key', function () {
    AiGenerationTemplate::create([
        'workspace_id' => null,
        'key' => 'nda_levant',
        'name_ar' => 'System NDA',
        'name_en' => 'System NDA',
        'prompt_template' => 'System prompt...',
    ]);

    AiGenerationTemplate::create([
        'workspace_id' => $this->workspace->id,
        'key' => 'nda_levant',
        'name_ar' => 'Custom NDA',
        'name_en' => 'Custom NDA',
        'prompt_template' => 'Custom prompt...',
    ]);

    $resolved = AiGenerationTemplate::resolveForKey('nda_levant', $this->workspace->id);

    expect($resolved->workspace_id)->toBe($this->workspace->id)
        ->and($resolved->name_en)->toBe('Custom NDA');
});

it('creates a workspace-specific template', function () {
    $response = $this->postJson('/api/v1/ai-generation-templates', [
        'key' => 'custom_nda',
        'name_ar' => 'اتفاقية مخصصة',
        'name_en' => 'Custom NDA',
        'prompt_template' => 'Generate a custom NDA for {{parties}}...',
        'intent_schema' => ['parties' => 'required', 'governing_law' => 'required'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.key', 'custom_nda')
        ->assertJsonPath('data.legal_review_status', 'pending');
});

it('increments version on prompt edit', function () {
    $template = AiGenerationTemplate::create([
        'workspace_id' => $this->workspace->id,
        'key' => 'test',
        'name_ar' => 'Test',
        'name_en' => 'Test',
        'prompt_template' => 'Original prompt',
        'version' => 1,
    ]);

    $response = $this->putJson("/api/v1/ai-generation-templates/{$template->id}", [
        'prompt_template' => 'Updated prompt content',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.version', 2);
});

it('prevents editing system templates', function () {
    $systemTemplate = AiGenerationTemplate::create([
        'workspace_id' => null,
        'key' => 'system',
        'name_ar' => 'System',
        'name_en' => 'System',
        'prompt_template' => 'System prompt',
    ]);

    $response = $this->putJson("/api/v1/ai-generation-templates/{$systemTemplate->id}", [
        'name_en' => 'Changed',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('error', 'Cannot edit system templates. Clone instead.');
});

it('prevents deleting system templates', function () {
    $systemTemplate = AiGenerationTemplate::create([
        'workspace_id' => null,
        'key' => 'system',
        'name_ar' => 'System',
        'name_en' => 'System',
        'prompt_template' => 'System prompt',
    ]);

    $response = $this->deleteJson("/api/v1/ai-generation-templates/{$systemTemplate->id}");

    $response->assertStatus(422);
});
