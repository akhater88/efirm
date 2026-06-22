<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\TaskWorkflow;
use App\Models\TaskWorkflowApproval;
use App\Models\TaskWorkflowStage;
use App\Services\TaskTransitionService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class TaskBoard extends Component
{
    public ?string $workflowId = null;

    public ?string $assigneeFilter = null;

    public ?string $taskableType = null;

    public ?string $taskableId = null;

    public ?string $priorityFilter = null;

    public array $columns = [];

    public function mount(
        ?string $taskableType = null,
        ?string $taskableId = null,
    ): void {
        $this->taskableType = $taskableType;
        $this->taskableId = $taskableId;

        // Default to workspace's default workflow
        $defaultWorkflow = TaskWorkflow::where('is_default', true)->first();
        $this->workflowId = $defaultWorkflow?->id;

        $this->loadBoard();
    }

    public function updatedWorkflowId(): void
    {
        $this->loadBoard();
    }

    public function updatedAssigneeFilter(): void
    {
        $this->loadBoard();
    }

    public function updatedPriorityFilter(): void
    {
        $this->loadBoard();
    }

    public function loadBoard(): void
    {
        if (! $this->workflowId) {
            $this->columns = [];

            return;
        }

        $workflow = TaskWorkflow::with('stages')->find($this->workflowId);
        if (! $workflow) {
            $this->columns = [];

            return;
        }

        $stages = $workflow->stages->sortBy('sort_order');

        $this->columns = $stages->map(function (TaskWorkflowStage $stage) {
            $query = Task::where('task_workflow_id', $this->workflowId)
                ->where('current_stage_id', $stage->id)
                ->with(['assignedTo', 'taskable']);

            if ($this->taskableType && $this->taskableId) {
                $query->where('taskable_type', $this->taskableType)
                    ->where('taskable_id', $this->taskableId);
            }

            if ($this->assigneeFilter) {
                $query->where('assigned_to_user_id', $this->assigneeFilter);
            }

            if ($this->priorityFilter) {
                $query->where('priority', $this->priorityFilter);
            }

            $tasks = $query->orderBy('due_date')->limit(200)->get();

            return [
                'id' => $stage->id,
                'name' => app()->getLocale() === 'ar' ? $stage->name_ar : $stage->name_en,
                'key' => $stage->key,
                'color' => $stage->color,
                'is_terminal' => $stage->is_terminal,
                'requires_approval' => $stage->requires_approval,
                'tasks' => $tasks->map(fn (Task $task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'assignee_name' => $task->assignedTo?->name,
                    'due_date' => $task->due_date?->format('d/m'),
                    'priority' => $task->priority?->value,
                    'priority_color' => $task->priority?->color(),
                    'taskable_label' => $this->taskableLabel($task),
                    'has_pending_approval' => $task->approvals()
                        ->where('status', 'pending')
                        ->exists(),
                ])->toArray(),
                'task_count' => $tasks->count(),
            ];
        })->values()->toArray();
    }

    public function moveTask(string $taskId, string $toStageId): void
    {
        $task = Task::findOrFail($taskId);
        $toStage = TaskWorkflowStage::findOrFail($toStageId);

        $transitionService = app(TaskTransitionService::class);

        try {
            $result = $transitionService->transition($task, $toStage, auth()->user());

            if ($result instanceof TaskWorkflowApproval) {
                $this->dispatch('board-approval-created', [
                    'message' => __('task_workflows.approval_requested'),
                ]);
            }
        } catch (ValidationException $e) {
            $this->dispatch('board-transition-rejected', [
                'message' => $e->getMessage(),
            ]);
        }

        $this->loadBoard();
    }

    private function taskableLabel(Task $task): ?string
    {
        $taskable = $task->taskable;
        if (! $taskable) {
            return null;
        }

        return match ($task->taskable_type) {
            'matter' => $taskable->title ?? null,
            'contact' => $taskable->display_name ?? null,
            'document' => $taskable->title ?? null,
            'obligation' => $taskable->title ?? null,
            default => null,
        };
    }

    public function render()
    {
        $workflows = TaskWorkflow::orderBy('name')->get();

        return view('livewire.tasks.board', [
            'workflows' => $workflows,
        ]);
    }
}
