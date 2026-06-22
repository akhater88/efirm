<?php

use App\Models\FormTemplate;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\WorkflowBundleService;

function createBundleTestUser(string $role = 'owner'): array
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

// --- F-11.4: List bundles ---

it('lists available workflow bundles', function () {
    [$user, $workspace] = createBundleTestUser();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/workflow-bundles');

    $response->assertOk();
    $bundles = $response->json('data');
    expect($bundles)->toBeArray();
    expect(count($bundles))->toBeGreaterThanOrEqual(3);

    // Check structure
    $keys = collect($bundles)->pluck('key')->toArray();
    expect($keys)->toContain('new_matter_onboarding');
    expect($keys)->toContain('contract_review');
    expect($keys)->toContain('obligation_tracking');
});

// --- F-11.4: Activate bundle ---

it('activates a workflow bundle and creates entities', function () {
    [$user, $workspace] = createBundleTestUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/workflow-bundles/new_matter_onboarding/activate');

    $response->assertCreated();
    $counts = $response->json('data');
    expect($counts['form_templates'])->toBeGreaterThanOrEqual(1);
    expect($counts['automations'])->toBeGreaterThanOrEqual(1);
    expect($counts['document_templates'])->toBeGreaterThanOrEqual(1);

    // Verify entities exist in DB
    $this->assertDatabaseHas('form_templates', [
        'workspace_id' => $workspace->id,
        'name_en' => 'Matter Intake Form',
    ]);
    $this->assertDatabaseHas('automations', [
        'workspace_id' => $workspace->id,
        'name_en' => 'Create task on matter open',
    ]);
});

// --- F-11.4: Re-activate is idempotent ---

it('re-activating a bundle does not duplicate entities', function () {
    [$user, $workspace] = createBundleTestUser();

    // Activate twice
    $this->actingAs($user, 'sanctum')->postJson('/api/v1/workflow-bundles/new_matter_onboarding/activate');
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/workflow-bundles/new_matter_onboarding/activate');

    $response->assertCreated();
    $counts = $response->json('data');
    // Second activation should create 0 new entities
    expect($counts['form_templates'])->toBe(0);
    expect($counts['automations'])->toBe(0);
    expect($counts['document_templates'])->toBe(0);
});

// --- F-11.4: Workspace isolation ---

it('activates bundles scoped to the workspace', function () {
    [$user, $workspace] = createBundleTestUser();
    $otherWorkspace = Workspace::factory()->create();

    $this->actingAs($user, 'sanctum')->postJson('/api/v1/workflow-bundles/contract_review/activate');

    // Verify other workspace has no entities
    $count = FormTemplate::withoutGlobalScope('workspace')
        ->where('workspace_id', $otherWorkspace->id)
        ->count();
    expect($count)->toBe(0);

    $countThis = FormTemplate::withoutGlobalScope('workspace')
        ->where('workspace_id', $workspace->id)
        ->count();
    expect($countThis)->toBeGreaterThanOrEqual(1);
});

// --- F-11.4: Invalid bundle key ---

it('returns error for invalid bundle key', function () {
    [$user, $workspace] = createBundleTestUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/workflow-bundles/nonexistent_bundle/activate');

    $response->assertStatus(500);
});

// --- F-11.4: Service unit test ---

it('lists bundles via service', function () {
    $service = new WorkflowBundleService;
    $bundles = $service->listAvailable();

    expect($bundles)->toBeArray();
    expect(count($bundles))->toBeGreaterThanOrEqual(3);
});
