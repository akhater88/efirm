<?php

namespace App\Observers;

use App\Models\TaskWorkflow;
use App\Models\TaskWorkflowStage;
use App\Models\TaskWorkflowTransition;
use App\Models\Workspace;

class WorkspaceObserver
{
    public function created(Workspace $workspace): void
    {
        // Guard: skip if workspace already has workflows (idempotent)
        if (TaskWorkflow::withoutGlobalScopes()->where('workspace_id', $workspace->id)->exists()) {
            return;
        }

        $this->seedGenericWorkflow($workspace);
        $this->seedContractReviewWorkflow($workspace);
        $this->seedLitigationTaskWorkflow($workspace);
    }

    private function seedGenericWorkflow(Workspace $workspace): void
    {
        $workflow = TaskWorkflow::withoutGlobalScopes()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Generic Workflow',
            'description' => 'Default workflow for general tasks',
            'is_default' => true,
        ]);

        $todo = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'للتنفيذ',
            'name_en' => 'To Do',
            'key' => 'todo',
            'sort_order' => 1,
            'is_initial' => true,
            'color' => 'gray',
        ]);

        $inProgress = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'قيد التنفيذ',
            'name_en' => 'In Progress',
            'key' => 'in_progress',
            'sort_order' => 2,
            'color' => 'info',
        ]);

        $done = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'مكتملة',
            'name_en' => 'Done',
            'key' => 'done',
            'sort_order' => 3,
            'is_terminal' => true,
            'color' => 'success',
        ]);

        // Transitions: todo->in_progress, in_progress->done, in_progress->todo
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $todo->id,
            'to_stage_id' => $inProgress->id,
        ]);
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $inProgress->id,
            'to_stage_id' => $done->id,
        ]);
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $inProgress->id,
            'to_stage_id' => $todo->id,
        ]);
    }

    private function seedContractReviewWorkflow(Workspace $workspace): void
    {
        $workflow = TaskWorkflow::withoutGlobalScopes()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Contract Review Workflow',
            'description' => 'Workflow for contract review tasks',
            'is_default' => false,
        ]);

        $drafting = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'الصياغة',
            'name_en' => 'Drafting',
            'key' => 'drafting',
            'sort_order' => 1,
            'is_initial' => true,
            'color' => 'gray',
        ]);

        $internalReview = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'المراجعة الداخلية',
            'name_en' => 'Internal Review',
            'key' => 'internal_review',
            'sort_order' => 2,
            'requires_approval' => true,
            'color' => 'warning',
        ]);

        $counterpartyReview = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'مراجعة الطرف الآخر',
            'name_en' => 'Counterparty Review',
            'key' => 'counterparty_review',
            'sort_order' => 3,
            'color' => 'info',
        ]);

        $approval = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'الموافقة',
            'name_en' => 'Approval',
            'key' => 'approval',
            'sort_order' => 4,
            'requires_approval' => true,
            'color' => 'warning',
        ]);

        $closed = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'مغلق',
            'name_en' => 'Closed',
            'key' => 'closed',
            'sort_order' => 5,
            'is_terminal' => true,
            'color' => 'success',
        ]);

        // Transitions
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $drafting->id,
            'to_stage_id' => $internalReview->id,
        ]);
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $internalReview->id,
            'to_stage_id' => $counterpartyReview->id,
        ]);
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $counterpartyReview->id,
            'to_stage_id' => $approval->id,
        ]);
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $approval->id,
            'to_stage_id' => $closed->id,
        ]);
    }

    private function seedLitigationTaskWorkflow(Workspace $workspace): void
    {
        $workflow = TaskWorkflow::withoutGlobalScopes()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Litigation Task Workflow',
            'description' => 'Workflow for litigation-related tasks',
            'is_default' => false,
        ]);

        $todo = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'للتنفيذ',
            'name_en' => 'To Do',
            'key' => 'todo',
            'sort_order' => 1,
            'is_initial' => true,
            'color' => 'gray',
        ]);

        $inProgress = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'قيد التنفيذ',
            'name_en' => 'In Progress',
            'key' => 'in_progress',
            'sort_order' => 2,
            'color' => 'info',
        ]);

        $blocked = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'معلّقة',
            'name_en' => 'Blocked',
            'key' => 'blocked',
            'sort_order' => 3,
            'color' => 'danger',
        ]);

        $done = TaskWorkflowStage::create([
            'task_workflow_id' => $workflow->id,
            'name_ar' => 'مكتملة',
            'name_en' => 'Done',
            'key' => 'done',
            'sort_order' => 4,
            'is_terminal' => true,
            'color' => 'success',
        ]);

        // Transitions
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $todo->id,
            'to_stage_id' => $inProgress->id,
        ]);
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $inProgress->id,
            'to_stage_id' => $blocked->id,
        ]);
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $blocked->id,
            'to_stage_id' => $inProgress->id,
        ]);
        TaskWorkflowTransition::create([
            'task_workflow_id' => $workflow->id,
            'from_stage_id' => $inProgress->id,
            'to_stage_id' => $done->id,
        ]);
    }
}
