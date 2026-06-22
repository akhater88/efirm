<?php

use App\Enums\ApprovalStatus;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Task;
use App\Models\TaskWorkflow;
use App\Models\TaskWorkflowApproval;
use App\Models\TaskWorkflowTransition;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\TaskTransitionService;
use Illuminate\Validation\ValidationException;

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

    $this->service = app(TaskTransitionService::class);

    // Use the seeded Generic Workflow
    $this->workflow = TaskWorkflow::where('is_default', true)->first();
    $this->stages = $this->workflow->stages;
});

it('transitions a task to an allowed next stage', function () {
    $todoStage = $this->stages->firstWhere('key', 'todo');
    $inProgressStage = $this->stages->firstWhere('key', 'in_progress');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $todoStage->id,
    ]);

    $result = $this->service->transition($task, $inProgressStage, $this->user);

    expect($result)->toBeInstanceOf(Task::class)
        ->and($result->current_stage_id)->toBe($inProgressStage->id);
});

it('rejects transition to a disallowed stage', function () {
    $todoStage = $this->stages->firstWhere('key', 'todo');
    $doneStage = $this->stages->firstWhere('key', 'done');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $todoStage->id,
    ]);

    // todo -> done has no transition in Generic Workflow
    $this->service->transition($task, $doneStage, $this->user);
})->throws(ValidationException::class);

it('respects requires_role on transition', function () {
    $todoStage = $this->stages->firstWhere('key', 'todo');
    $inProgressStage = $this->stages->firstWhere('key', 'in_progress');

    // Modify transition to require admin role
    $transition = TaskWorkflowTransition::where('task_workflow_id', $this->workflow->id)
        ->where('from_stage_id', $todoStage->id)
        ->where('to_stage_id', $inProgressStage->id)
        ->first();
    $transition->update(['requires_role' => 'admin']);

    $member = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $member->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Member,
    ]);
    $member->switchWorkspace($this->workspace);

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $todoStage->id,
    ]);

    $this->service->transition($task, $inProgressStage, $member);
})->throws(ValidationException::class);

it('creates pending approval when target stage requires approval', function () {
    // Use Contract Review Workflow which has approval stages
    $contractWorkflow = TaskWorkflow::where('name', 'Contract Review Workflow')->first();
    $stages = $contractWorkflow->stages;
    $draftingStage = $stages->firstWhere('key', 'drafting');
    $internalReviewStage = $stages->firstWhere('key', 'internal_review');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $contractWorkflow->id,
        'current_stage_id' => $draftingStage->id,
    ]);

    $result = $this->service->transition($task, $internalReviewStage, $this->user);

    expect($result)->toBeInstanceOf(TaskWorkflowApproval::class)
        ->and($result->status)->toBe(ApprovalStatus::Pending)
        ->and($result->from_stage_id)->toBe($draftingStage->id)
        ->and($result->to_stage_id)->toBe($internalReviewStage->id);
});

it('does not advance task when approval is pending', function () {
    $contractWorkflow = TaskWorkflow::where('name', 'Contract Review Workflow')->first();
    $stages = $contractWorkflow->stages;
    $draftingStage = $stages->firstWhere('key', 'drafting');
    $internalReviewStage = $stages->firstWhere('key', 'internal_review');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $contractWorkflow->id,
        'current_stage_id' => $draftingStage->id,
    ]);

    $this->service->transition($task, $internalReviewStage, $this->user);

    // Task should still be at drafting stage
    expect($task->fresh()->current_stage_id)->toBe($draftingStage->id);
});

it('creates pending approval when transition has specific approver', function () {
    $todoStage = $this->stages->firstWhere('key', 'todo');
    $inProgressStage = $this->stages->firstWhere('key', 'in_progress');

    $approver = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $approver->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Admin,
    ]);

    // Set specific approver on transition
    $transition = TaskWorkflowTransition::where('task_workflow_id', $this->workflow->id)
        ->where('from_stage_id', $todoStage->id)
        ->where('to_stage_id', $inProgressStage->id)
        ->first();
    $transition->update(['requires_approval_by_user_id' => $approver->id]);

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $todoStage->id,
    ]);

    $result = $this->service->transition($task, $inProgressStage, $this->user);

    expect($result)->toBeInstanceOf(TaskWorkflowApproval::class)
        ->and($result->approver_user_id)->toBe($approver->id);
});

it('assigns workflow and sets initial stage', function () {
    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
    ]);

    expect($task->task_workflow_id)->toBeNull();

    $result = $this->service->assignWorkflow($task, $this->workflow);

    $initialStage = $this->workflow->initialStage();
    expect($result->task_workflow_id)->toBe($this->workflow->id)
        ->and($result->current_stage_id)->toBe($initialStage->id);
});

it('rejects transition on a task without a workflow', function () {
    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
    ]);

    $todoStage = $this->stages->firstWhere('key', 'todo');

    $this->service->transition($task, $todoStage, $this->user);
})->throws(ValidationException::class);

it('handles terminal stage correctly', function () {
    $inProgressStage = $this->stages->firstWhere('key', 'in_progress');
    $doneStage = $this->stages->firstWhere('key', 'done');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $inProgressStage->id,
    ]);

    $result = $this->service->transition($task, $doneStage, $this->user);

    expect($result->current_stage_id)->toBe($doneStage->id);
    expect($doneStage->is_terminal)->toBeTrue();
});

it('persists auto_transition_after_hours field', function () {
    $todoStage = $this->stages->firstWhere('key', 'todo');
    $inProgressStage = $this->stages->firstWhere('key', 'in_progress');

    $transition = TaskWorkflowTransition::where('task_workflow_id', $this->workflow->id)
        ->where('from_stage_id', $todoStage->id)
        ->where('to_stage_id', $inProgressStage->id)
        ->first();

    $transition->update(['auto_transition_after_hours' => 48]);

    expect($transition->fresh()->auto_transition_after_hours)->toBe(48);
});
