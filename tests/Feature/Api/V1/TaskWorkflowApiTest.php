<?php

use App\Enums\Role;
use App\Enums\TaskStatus;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Task;
use App\Models\TaskWorkflow;
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

    $this->client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
    ]);
});

it('creates a workflow with stages and transitions', function () {
    $response = $this->postJson('/api/v1/task-workflows', [
        'name' => 'My Custom Workflow',
        'description' => 'A custom workflow',
        'stages' => [
            ['name_ar' => 'بداية', 'name_en' => 'Start', 'key' => 'start', 'sort_order' => 1, 'is_initial' => true],
            ['name_ar' => 'نهاية', 'name_en' => 'End', 'key' => 'end', 'sort_order' => 2, 'is_terminal' => true],
        ],
        'transitions' => [
            ['from_stage_key' => 'start', 'to_stage_key' => 'end'],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'My Custom Workflow')
        ->assertJsonCount(2, 'data.stages')
        ->assertJsonCount(1, 'data.transitions');
});

it('lists workflows for the workspace', function () {
    // 3 default workflows from observer + list them
    $response = $this->getJson('/api/v1/task-workflows');

    $response->assertOk();
    // Should have at least the 3 seeded defaults
    expect($response->json('data.data'))->toHaveCount(3);
});

it('sets one default workflow per workspace', function () {
    // The observer already set Generic as default
    $nonDefault = TaskWorkflow::where('is_default', false)->first();

    $this->putJson("/api/v1/task-workflows/{$nonDefault->id}", [
        'is_default' => true,
    ])->assertOk();

    // The old default should now be false
    $oldDefault = TaskWorkflow::where('name', 'Generic Workflow')->first();
    expect($oldDefault->is_default)->toBeFalse();
    expect($nonDefault->fresh()->is_default)->toBeTrue();
});

it('prevents deleting a workflow with active tasks', function () {
    $workflow = TaskWorkflow::where('is_default', true)->first();
    $stage = $workflow->initialStage();

    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $workflow->id,
        'current_stage_id' => $stage->id,
        'status' => TaskStatus::InProgress,
    ]);

    $response = $this->deleteJson("/api/v1/task-workflows/{$workflow->id}");

    $response->assertForbidden();
});

it('enforces workspace isolation for workflows', function () {
    $otherWorkspace = Workspace::factory()->create();
    $otherWorkflow = TaskWorkflow::withoutGlobalScopes()
        ->where('workspace_id', $otherWorkspace->id)
        ->first();

    $response = $this->getJson("/api/v1/task-workflows/{$otherWorkflow->id}");

    $response->assertNotFound();
});

it('only allows owner or admin to create workflows', function () {
    $member = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $member->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Member,
    ]);
    $member->switchWorkspace($this->workspace);
    Sanctum::actingAs($member);

    $response = $this->postJson('/api/v1/task-workflows', [
        'name' => 'Member Workflow',
        'stages' => [
            ['name_ar' => 'بداية', 'name_en' => 'Start', 'key' => 'start', 'sort_order' => 1, 'is_initial' => true],
        ],
    ]);

    $response->assertForbidden();
});

it('seeds default workflows on workspace creation', function () {
    $newWorkspace = Workspace::factory()->create();

    $workflows = TaskWorkflow::withoutGlobalScopes()
        ->where('workspace_id', $newWorkspace->id)
        ->get();

    expect($workflows)->toHaveCount(3);
    expect($workflows->pluck('name')->toArray())->toContain('Generic Workflow', 'Contract Review Workflow', 'Litigation Task Workflow');

    $defaultWorkflow = $workflows->firstWhere('is_default', true);
    expect($defaultWorkflow->name)->toBe('Generic Workflow');
});

it('filters workflows by applies_to_task_type', function () {
    // Create a workflow with a specific task type
    $this->postJson('/api/v1/task-workflows', [
        'name' => 'Typed Workflow',
        'applies_to_task_type' => 'matter',
        'stages' => [
            ['name_ar' => 'بداية', 'name_en' => 'Start', 'key' => 'start', 'sort_order' => 1, 'is_initial' => true],
        ],
    ])->assertCreated();

    $response = $this->getJson('/api/v1/task-workflows?applies_to_task_type=matter');

    $response->assertOk();
    foreach ($response->json('data.data') as $workflow) {
        expect($workflow['applies_to_task_type'])->toBe('matter');
    }
});
