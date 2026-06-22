<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Models\Task;
use App\Models\TaskWorkflow;
use App\Models\TaskWorkflowApproval;
use App\Models\TaskWorkflowStage;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TaskTransitionService
{
    /**
     * Transition a task to a new stage within its workflow.
     *
     * Returns the updated Task if the transition is immediate,
     * or a TaskWorkflowApproval if the transition requires approval.
     */
    public function transition(Task $task, TaskWorkflowStage $toStage, User $actor): Task|TaskWorkflowApproval
    {
        if (! $task->task_workflow_id) {
            throw ValidationException::withMessages([
                'workflow' => [__('task_workflows.error_no_workflow')],
            ]);
        }

        $transition = $task->workflow->transitions()
            ->where('from_stage_id', $task->current_stage_id)
            ->where('to_stage_id', $toStage->id)
            ->first();

        if (! $transition) {
            throw ValidationException::withMessages([
                'to_stage_id' => [__('task_workflows.error_invalid_transition')],
            ]);
        }

        // Check role requirement
        if ($transition->requires_role) {
            $workspace = $actor->currentWorkspace();
            $actorRole = $workspace ? $actor->roleInWorkspace($workspace) : null;

            if (! $actorRole || $actorRole->value !== $transition->requires_role) {
                throw ValidationException::withMessages([
                    'role' => [__('task_workflows.error_role_required')],
                ]);
            }
        }

        // Check if approval is needed
        $needsApproval = $toStage->requires_approval || $transition->requires_approval_by_user_id;

        if ($needsApproval) {
            $approverId = $transition->requires_approval_by_user_id;

            // If no specific approver, find an owner in the workspace
            if (! $approverId) {
                $ownerMember = $task->workspace->members()->where('role', 'owner')->first();
                $approverId = $ownerMember?->user_id ?? $actor->id;
            }

            return TaskWorkflowApproval::create([
                'workspace_id' => $task->workspace_id,
                'task_id' => $task->id,
                'from_stage_id' => $task->current_stage_id,
                'to_stage_id' => $toStage->id,
                'requested_by_user_id' => $actor->id,
                'approver_user_id' => $approverId,
                'status' => ApprovalStatus::Pending,
            ]);
        }

        // Direct transition
        $task->update(['current_stage_id' => $toStage->id]);

        return $task->fresh();
    }

    /**
     * Assign a workflow to a task and set the initial stage.
     */
    public function assignWorkflow(Task $task, TaskWorkflow $workflow): Task
    {
        $initialStage = $workflow->initialStage();

        $task->update([
            'task_workflow_id' => $workflow->id,
            'current_stage_id' => $initialStage?->id,
        ]);

        return $task->fresh();
    }
}
