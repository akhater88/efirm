<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Models\Task;
use App\Models\TaskWorkflowApproval;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TaskApprovalService
{
    /**
     * Approve a pending workflow approval and advance the task.
     */
    public function approve(TaskWorkflowApproval $approval, User $approver, ?string $notes = null): Task
    {
        $this->verifyApprover($approval, $approver);

        $approval->update([
            'status' => ApprovalStatus::Approved,
            'responded_at' => now(),
            'notes' => $notes,
        ]);

        $task = $approval->task;
        $task->update(['current_stage_id' => $approval->to_stage_id]);

        return $task->fresh();
    }

    /**
     * Reject a pending workflow approval. Task stays at current stage.
     */
    public function reject(TaskWorkflowApproval $approval, User $approver, ?string $notes = null): TaskWorkflowApproval
    {
        $this->verifyApprover($approval, $approver);

        $approval->update([
            'status' => ApprovalStatus::Rejected,
            'responded_at' => now(),
            'notes' => $notes,
        ]);

        return $approval->fresh();
    }

    /**
     * Verify the user is the designated approver.
     */
    private function verifyApprover(TaskWorkflowApproval $approval, User $approver): void
    {
        if ($approval->approver_user_id !== $approver->id) {
            throw ValidationException::withMessages([
                'approver' => [__('task_workflows.error_not_designated_approver')],
            ]);
        }

        if ($approval->status !== ApprovalStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => [__('task_workflows.error_approval_not_pending')],
            ]);
        }
    }
}
