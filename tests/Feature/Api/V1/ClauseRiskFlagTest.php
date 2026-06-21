<?php

use App\Enums\RiskPosition;
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

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    $body = ['type' => 'doc', 'content' => [
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Liability']]],
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Limited to 10%.']]],
    ]];

    $this->document = app(DocumentService::class)->createDocument($matter, 'Test', $body, $this->user);
    $this->clause = $this->document->currentVersion->clauses->first();
});

it('sets a risk flag on a document clause', function () {
    $response = $this->patchJson("/api/v1/document-clauses/{$this->clause->id}/risk", [
        'risk_position' => 'adverse',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.risk_position', 'adverse');

    expect($this->clause->fresh()->risk_position)->toBe(RiskPosition::Adverse);
});

it('clears a risk flag by setting null', function () {
    $this->clause->update(['risk_position' => RiskPosition::Favourable]);

    $response = $this->patchJson("/api/v1/document-clauses/{$this->clause->id}/risk", [
        'risk_position' => null,
    ]);

    $response->assertOk();
    expect($this->clause->fresh()->risk_position)->toBeNull();
});

it('rejects invalid risk position value', function () {
    $response = $this->patchJson("/api/v1/document-clauses/{$this->clause->id}/risk", [
        'risk_position' => 'invalid',
    ]);

    $response->assertUnprocessable();
});

it('traverses fallback chain on library clauses', function () {
    $parent = LibraryClause::factory()->create([
        'workspace_id' => $this->workspace->id,
        'title' => 'Standard Liability',
        'risk_position' => RiskPosition::Balanced,
    ]);

    $fallback1 = LibraryClause::factory()->create([
        'workspace_id' => $this->workspace->id,
        'title' => 'Favourable Liability',
        'risk_position' => RiskPosition::Favourable,
        'is_fallback_of_id' => $parent->id,
    ]);

    $fallback2 = LibraryClause::factory()->create([
        'workspace_id' => $this->workspace->id,
        'title' => 'Last Resort Liability',
        'risk_position' => RiskPosition::Adverse,
        'is_fallback_of_id' => $parent->id,
    ]);

    $parent->refresh();

    expect($parent->fallbacks)->toHaveCount(2)
        ->and($parent->fallbacks->pluck('title')->toArray())->toContain('Favourable Liability', 'Last Resort Liability');

    expect($fallback1->fallbackOf->id)->toBe($parent->id);
});

it('stores risk position on library clause', function () {
    $response = $this->postJson('/api/v1/library/clauses', [
        'title' => 'Favourable Liability Clause',
        'clause_type' => 'limitation_of_liability',
        'risk_position' => 'favourable',
        'body_en' => ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Liability limited to 100%.']]]]],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.risk_position', 'favourable');
});
