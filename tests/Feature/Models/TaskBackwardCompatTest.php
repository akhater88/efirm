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

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    auth()->login($this->user);

    $this->client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
    ]);
});

it('existing task without workflow_id retains legacy status', function () {
    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'status' => TaskStatus::InProgress,
    ]);

    expect($task->task_workflow_id)->toBeNull()
        ->and($task->current_stage_id)->toBeNull()
        ->and($task->status)->toBe(TaskStatus::InProgress);
});

it('task factory without workflow produces valid task', function () {
    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
    ]);

    expect($task->exists)->toBeTrue()
        ->and($task->task_workflow_id)->toBeNull()
        ->and($task->current_stage_id)->toBeNull()
        ->and($task->status)->toBe(TaskStatus::Todo);
});

it('querying by legacy status works alongside workflow tasks', function () {
    // Create a legacy task
    $legacyTask = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'status' => TaskStatus::InProgress,
    ]);

    // Create a workflow task
    $workflow = TaskWorkflow::where('is_default', true)->first();
    $stage = $workflow->initialStage();

    $workflowTask = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $workflow->id,
        'current_stage_id' => $stage->id,
        'status' => TaskStatus::Todo,
    ]);

    // Query by status should return both types
    $inProgressTasks = Task::where('status', TaskStatus::InProgress)->get();
    expect($inProgressTasks)->toHaveCount(1)
        ->and($inProgressTasks->first()->id)->toBe($legacyTask->id);

    $allTasks = Task::all();
    expect($allTasks)->toHaveCount(2);
});
