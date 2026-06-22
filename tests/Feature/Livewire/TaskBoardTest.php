<?php

use App\Enums\Role;
use App\Enums\TaskPriority;
use App\Livewire\Tasks\TaskBoard;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Task;
use App\Models\TaskWorkflow;
use App\Models\TaskWorkflowApproval;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Livewire\Livewire;

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

    // The observer seeds default workflows on workspace creation
    $this->workflow = TaskWorkflow::where('workspace_id', $this->workspace->id)
        ->where('is_default', true)
        ->first();

    $this->stages = $this->workflow->stages->sortBy('sort_order');
    $this->todoStage = $this->stages->firstWhere('key', 'todo');
    $this->inProgressStage = $this->stages->firstWhere('key', 'in_progress');
    $this->doneStage = $this->stages->firstWhere('key', 'done');

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);
});

it('renders the board component with columns', function () {
    $name = app()->getLocale() === 'ar' ? $this->todoStage->name_ar : $this->todoStage->name_en;
    Livewire::test(TaskBoard::class)
        ->assertSee($name);
});

it('loads tasks into correct columns', function () {
    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
        'title' => 'Task in Todo',
    ]);

    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->inProgressStage->id,
        'title' => 'Task in Progress',
    ]);

    $component = Livewire::test(TaskBoard::class);

    $columns = $component->get('columns');
    $todoColumn = collect($columns)->firstWhere('key', 'todo');
    $inProgressColumn = collect($columns)->firstWhere('key', 'in_progress');

    expect($todoColumn['task_count'])->toBe(1)
        ->and($inProgressColumn['task_count'])->toBe(1);
});

it('moves a task via moveTask method', function () {
    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
    ]);

    Livewire::test(TaskBoard::class)
        ->call('moveTask', $task->id, $this->inProgressStage->id);

    $task->refresh();
    expect($task->current_stage_id)->toBe($this->inProgressStage->id);
});

it('dispatches rejection event for invalid transition', function () {
    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
    ]);

    // Todo → Done is not a direct transition in the generic workflow
    Livewire::test(TaskBoard::class)
        ->call('moveTask', $task->id, $this->doneStage->id)
        ->assertDispatched('board-transition-rejected');

    $task->refresh();
    expect($task->current_stage_id)->toBe($this->todoStage->id);
});

it('dispatches approval event for approval-gated transition', function () {
    // Use Contract Review workflow which has approval stages
    $contractWorkflow = TaskWorkflow::where('workspace_id', $this->workspace->id)
        ->where('name', 'Contract Review Workflow')
        ->first();

    if (! $contractWorkflow) {
        $this->markTestSkipped('Contract Review Workflow not seeded');
    }

    $draftingStage = $contractWorkflow->stages->firstWhere('key', 'drafting');
    $reviewStage = $contractWorkflow->stages->firstWhere('key', 'internal_review');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $contractWorkflow->id,
        'current_stage_id' => $draftingStage->id,
    ]);

    Livewire::test(TaskBoard::class, ['workflowId' => $contractWorkflow->id])
        ->set('workflowId', $contractWorkflow->id)
        ->call('moveTask', $task->id, $reviewStage->id);

    // Task should NOT have moved (approval pending)
    $task->refresh();
    if ($reviewStage->requires_approval) {
        expect($task->current_stage_id)->toBe($draftingStage->id);
        expect(TaskWorkflowApproval::where('task_id', $task->id)->where('status', 'pending')->exists())->toBeTrue();
    } else {
        // If no approval required, it should have moved
        expect($task->current_stage_id)->toBe($reviewStage->id);
    }
});

it('filters by priority', function () {
    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
        'priority' => TaskPriority::Urgent,
    ]);

    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
        'priority' => TaskPriority::Low,
    ]);

    $component = Livewire::test(TaskBoard::class)
        ->set('priorityFilter', 'urgent');

    $columns = $component->get('columns');
    $todoColumn = collect($columns)->firstWhere('key', 'todo');

    expect($todoColumn['task_count'])->toBe(1);
});

it('scopes board to taskable when provided', function () {
    $otherMatter = Matter::factory()->create(['workspace_id' => $this->workspace->id]);

    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
    ]);

    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $otherMatter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
    ]);

    $component = Livewire::test(TaskBoard::class, [
        'taskableType' => 'matter',
        'taskableId' => $this->matter->id,
    ]);

    $columns = $component->get('columns');
    $totalTasks = collect($columns)->sum('task_count');

    expect($totalTasks)->toBe(1);
});

it('switches workflow and reloads board', function () {
    $otherWorkflow = TaskWorkflow::where('workspace_id', $this->workspace->id)
        ->where('is_default', false)
        ->first();

    if (! $otherWorkflow) {
        $this->markTestSkipped('No non-default workflow available');
    }

    $component = Livewire::test(TaskBoard::class)
        ->set('workflowId', $otherWorkflow->id);

    $columns = $component->get('columns');

    expect(count($columns))->toBeGreaterThan(0);
});
