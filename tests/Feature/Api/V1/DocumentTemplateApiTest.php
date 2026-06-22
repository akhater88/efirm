<?php

use App\Models\DocumentTemplate;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createDocTemplateTestUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    return [$user, $workspace];
}

// --- F-11.3: Document Template CRUD ---

it('creates a document template', function () {
    [$user, $workspace] = createDocTemplateTestUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/document-templates', [
        'name_ar' => 'قالب عقد',
        'name_en' => 'Contract Template',
        'document_type' => 'contract',
        'body' => [
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Between {{party_a}} and {{party_b}}']]],
            ],
        ],
        'placeholder_schema' => [
            'party_a' => ['type' => 'text', 'required' => true],
            'party_b' => ['type' => 'text', 'required' => true],
        ],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name_en', 'Contract Template');
});

it('lists document templates', function () {
    [$user, $workspace] = createDocTemplateTestUser();

    DocumentTemplate::factory()->create(['workspace_id' => $workspace->id]);
    DocumentTemplate::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/document-templates');

    $response->assertOk();
    $response->assertJsonCount(2, 'data.data');
});

it('updates a document template', function () {
    [$user, $workspace] = createDocTemplateTestUser();

    $template = DocumentTemplate::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/document-templates/{$template->id}", [
        'name_en' => 'Updated Template',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name_en', 'Updated Template');
});

it('deletes a document template', function () {
    [$user, $workspace] = createDocTemplateTestUser();

    $template = DocumentTemplate::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/document-templates/{$template->id}");

    $response->assertNoContent();
    $this->assertSoftDeleted('document_templates', ['id' => $template->id]);
});

// --- F-11.3: Placeholder replacement ---

it('replaces placeholders when creating document from template', function () {
    [$user, $workspace] = createDocTemplateTestUser();

    $template = DocumentTemplate::factory()->create(['workspace_id' => $workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/matters/{$matter->id}/documents/from-template", [
        'document_template_id' => $template->id,
        'replacements' => [
            'title' => 'Service Agreement',
            'party_a' => 'ACME Corp',
            'party_b' => 'Widget Inc',
        ],
        'title' => 'ACME-Widget Service Agreement',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.title', 'ACME-Widget Service Agreement');

    // Verify the body had placeholders replaced
    $version = $response->json('data.current_version');
    $bodyJson = json_encode($version['body']);
    expect($bodyJson)->not->toContain('{{party_a}}');
    expect($bodyJson)->toContain('ACME Corp');
});

// --- F-11.3: Workspace isolation ---

it('scopes document templates to current workspace', function () {
    [$user, $workspace] = createDocTemplateTestUser();
    $otherWorkspace = Workspace::factory()->create();

    DocumentTemplate::factory()->create(['workspace_id' => $workspace->id]);
    DocumentTemplate::factory()->create(['workspace_id' => $otherWorkspace->id]);

    // The global scope + the query should filter. Let's just verify the count indirectly.
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/document-templates');

    $response->assertOk();
    // Only workspace-scoped template shows (global scope filters other workspace)
    $response->assertJsonCount(1, 'data.data');
});
