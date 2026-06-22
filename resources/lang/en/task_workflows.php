<?php

return [
    // Entity names
    'workflow' => 'Workflow',
    'workflows' => 'Workflows',
    'stage' => 'Stage',
    'stages' => 'Stages',
    'transition' => 'Transition',
    'transitions' => 'Transitions',
    'approval' => 'Approval',
    'approvals' => 'Approvals',

    // Default workflow names
    'generic_workflow' => 'Generic Workflow',
    'contract_review_workflow' => 'Contract Review Workflow',
    'litigation_task_workflow' => 'Litigation Task Workflow',

    // Stage names
    'stage_todo' => 'To Do',
    'stage_in_progress' => 'In Progress',
    'stage_done' => 'Done',
    'stage_blocked' => 'Blocked',
    'stage_drafting' => 'Drafting',
    'stage_internal_review' => 'Internal Review',
    'stage_counterparty_review' => 'Counterparty Review',
    'stage_approval' => 'Approval',
    'stage_closed' => 'Closed',

    // Approval statuses
    'approval_pending' => 'Pending',
    'approval_approved' => 'Approved',
    'approval_rejected' => 'Rejected',
    'approval_cancelled' => 'Cancelled',

    // Success messages
    'created_success' => 'Workflow created',
    'updated_success' => 'Workflow updated',
    'deleted_success' => 'Workflow deleted',
    'transition_success' => 'Task transitioned successfully',
    'approval_requested' => 'Approval has been requested',

    // Error messages
    'error_no_workflow' => 'Task does not have a workflow assigned',
    'error_invalid_transition' => 'This transition is not allowed',
    'error_role_required' => 'You do not have the required role for this transition',
    'error_not_designated_approver' => 'You are not the designated approver for this request',
    'error_approval_not_pending' => 'This approval has already been responded to',
    'error_has_active_tasks' => 'Cannot delete workflow with active tasks',

    // Board UI (F-10.2)
    'task_board' => 'Task Board',
    'select_workflow' => 'Select Workflow',
    'all_priorities' => 'All Priorities',
    'no_tasks_in_stage' => 'No tasks in this stage',
    'select_workflow_to_view_board' => 'Select a workflow to view the board',
    'pending_approval' => 'Pending Approval',
    'transition_rejected' => 'Transition not allowed',
];
