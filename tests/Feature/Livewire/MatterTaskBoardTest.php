<?php

use App\Enums\Role;
use App\Livewire\Tasks\TaskBoard;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Task;
use App\Models\TaskWorkflow;
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

    $this->workflow = TaskWorkflow::where('workspace_id', $this->workspace->id)
        ->where('is_default', true)
        ->first();

    $this->todoStage = $this->workflow->stages->firstWhere('key', 'todo');

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    $this->otherMatter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);
});

it('shows only tasks for the scoped matter', function () {
    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
        'title' => 'My Matter Task',
    ]);

    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->otherMatter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
        'title' => 'Other Matter Task',
    ]);

    $component = Livewire::test(TaskBoard::class, [
        'taskableType' => 'matter',
        'taskableId' => $this->matter->id,
    ]);

    $columns = $component->get('columns');
    $totalTasks = collect($columns)->sum('task_count');

    expect($totalTasks)->toBe(1);
});

it('can move a task on the embedded board', function () {
    $inProgressStage = $this->workflow->stages->firstWhere('key', 'in_progress');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $this->todoStage->id,
    ]);

    Livewire::test(TaskBoard::class, [
        'taskableType' => 'matter',
        'taskableId' => $this->matter->id,
    ])->call('moveTask', $task->id, $inProgressStage->id);

    $task->refresh();
    expect($task->current_stage_id)->toBe($inProgressStage->id);
});
