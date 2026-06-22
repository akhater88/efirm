<?php

use App\Enums\ApprovalStatus;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Task;
use App\Models\TaskWorkflow;
use App\Models\TaskWorkflowApproval;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\TaskApprovalService;
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

    $this->approvalService = app(TaskApprovalService::class);
    $this->transitionService = app(TaskTransitionService::class);

    // Use Contract Review Workflow which has approval stages
    $this->workflow = TaskWorkflow::where('name', 'Contract Review Workflow')->first();
    $this->stages = $this->workflow->stages;
});

it('approving advances task to target stage', function () {
    $draftingStage = $this->stages->firstWhere('key', 'drafting');
    $internalReviewStage = $this->stages->firstWhere('key', 'internal_review');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $draftingStage->id,
    ]);

    $approval = $this->transitionService->transition($task, $internalReviewStage, $this->user);
    expect($approval)->toBeInstanceOf(TaskWorkflowApproval::class);

    // The approval's approver is the owner (this->user)
    $result = $this->approvalService->approve($approval, $this->user, 'Looks good');

    expect($result)->toBeInstanceOf(Task::class)
        ->and($result->current_stage_id)->toBe($internalReviewStage->id);
});

it('rejecting leaves task at current stage', function () {
    $draftingStage = $this->stages->firstWhere('key', 'drafting');
    $internalReviewStage = $this->stages->firstWhere('key', 'internal_review');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $draftingStage->id,
    ]);

    $approval = $this->transitionService->transition($task, $internalReviewStage, $this->user);

    $result = $this->approvalService->reject($approval, $this->user, 'Needs more work');

    expect($result)->toBeInstanceOf(TaskWorkflowApproval::class)
        ->and($result->status)->toBe(ApprovalStatus::Rejected);

    // Task stays at drafting
    expect($task->fresh()->current_stage_id)->toBe($draftingStage->id);
});

it('only designated approver can approve', function () {
    $draftingStage = $this->stages->firstWhere('key', 'drafting');
    $internalReviewStage = $this->stages->firstWhere('key', 'internal_review');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $draftingStage->id,
    ]);

    $approval = $this->transitionService->transition($task, $internalReviewStage, $this->user);

    $otherUser = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $otherUser->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Admin,
    ]);

    $this->approvalService->approve($approval, $otherUser);
})->throws(ValidationException::class);

it('sets responded_at timestamp', function () {
    $draftingStage = $this->stages->firstWhere('key', 'drafting');
    $internalReviewStage = $this->stages->firstWhere('key', 'internal_review');

    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'task_workflow_id' => $this->workflow->id,
        'current_stage_id' => $draftingStage->id,
    ]);

    $approval = $this->transitionService->transition($task, $internalReviewStage, $this->user);

    expect($approval->responded_at)->toBeNull();

    $this->approvalService->approve($approval, $this->user);

    expect($approval->fresh()->responded_at)->not->toBeNull();
});
