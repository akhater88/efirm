<?php

namespace App\Livewire\Pages;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskWorkflow;
use App\Models\TaskWorkflowStage;
use App\Models\User;
use App\Services\TaskTransitionService;
use Livewire\Component;

class TasksList extends Component
{
    public string $search = '';

    public string $priorityFilter = '';

    public string $statusFilter = '';

    public string $taskTypeFilter = '';

    public string $viewMode = 'list'; // 'list' or 'board'

    public ?string $boardWorkflowId = null;

    public array $boardColumns = [];

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $editingId = null;

    public string $formTitle = '';

    public string $formDescription = '';

    public ?string $formPriority = null;

    public ?string $formStatus = null;

    public ?string $formDueDate = null;

    public ?string $formAssignedToUserId = null;

    public ?string $formTaskTypeId = null;

    /** @var array<string, mixed> */
    public array $formCustomFieldValues = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTaskTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFormTaskTypeId(): void
    {
        $this->formCustomFieldValues = [];
    }

    public function resetPage(): void
    {
        // Reset cursor pagination by removing the cursor query param
    }

    public function openCreate(): void
    {
        $this->reset(['formTitle', 'formDescription', 'formPriority', 'formStatus', 'formDueDate', 'formAssignedToUserId', 'formTaskTypeId', 'formCustomFieldValues', 'editingId']);
        $this->formStatus = TaskStatus::Todo->value;
        $this->formPriority = TaskPriority::Normal->value;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $task = Task::findOrFail($id);
        $this->editingId = $task->id;
        $this->formTitle = $task->title ?? '';
        $this->formDescription = $task->description ?? '';
        $this->formPriority = $task->priority?->value;
        $this->formStatus = $task->status?->value;
        $this->formDueDate = $task->due_date?->format('Y-m-d');
        $this->formAssignedToUserId = $task->assigned_to_user_id;
        $this->formTaskTypeId = $task->task_type_id;
        $this->formCustomFieldValues = $task->custom_field_values ?? [];
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'formTitle' => 'required|string|max:255',
            'formDescription' => 'nullable|string|max:5000',
            'formPriority' => 'nullable|string',
            'formStatus' => 'nullable|string',
            'formDueDate' => 'nullable|date',
            'formAssignedToUserId' => 'nullable|string',
        ]);

        $data = [
            'title' => $this->formTitle,
            'description' => $this->formDescription ?: null,
            'priority' => $this->formPriority ?: null,
            'status' => $this->formStatus ?: null,
            'due_date' => $this->formDueDate ?: null,
            'assigned_to_user_id' => $this->formAssignedToUserId ?: null,
            'task_type_id' => $this->formTaskTypeId ?: null,
            'custom_field_values' => ! empty($this->formCustomFieldValues) ? $this->formCustomFieldValues : null,
        ];

        if ($this->isEditing && $this->editingId) {
            $task = Task::findOrFail($this->editingId);
            $data['updated_by_user_id'] = auth()->id();
            $task->update($data);
            session()->flash('message', __('common.saved'));
        } else {
            $workspace = auth()->user()->currentWorkspace();
            $data['workspace_id'] = $workspace->id;
            $data['created_by_user_id'] = auth()->id();
            $data['updated_by_user_id'] = auth()->id();
            Task::create($data);
            session()->flash('message', __('common.created'));
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if ($this->editingId) {
            $task = Task::findOrFail($this->editingId);
            $task->delete();
            session()->flash('message', __('common.deleted'));
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->editingId = null;
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;

        if ($mode === 'board') {
            $this->loadBoard();
        }
    }

    public function loadBoard(): void
    {
        if (! $this->boardWorkflowId) {
            $defaultWorkflow = TaskWorkflow::where('is_default', true)->first();
            $this->boardWorkflowId = $defaultWorkflow?->id;
        }

        if (! $this->boardWorkflowId) {
            $this->boardColumns = [];

            return;
        }

        $workflow = TaskWorkflow::with('stages')->find($this->boardWorkflowId);
        if (! $workflow) {
            $this->boardColumns = [];

            return;
        }

        $stages = $workflow->stages->sortBy('sort_order');
        $priorityColors = [
            'urgent' => '#DC2626',
            'high' => '#F59E0B',
            'medium' => '#2563EB',
            'normal' => '#78716C',
            'low' => '#A8A29E',
        ];

        $this->boardColumns = $stages->map(function (TaskWorkflowStage $stage) use ($priorityColors) {
            $query = Task::where(function ($q) use ($stage) {
                $q->where('task_workflow_id', $this->boardWorkflowId)
                    ->where('current_stage_id', $stage->id);

                $legacyMap = [
                    'todo' => ['todo'],
                    'in_progress' => ['in_progress', 'blocked'],
                    'done' => ['done', 'cancelled'],
                ];
                $matchingStatuses = $legacyMap[$stage->key] ?? [];
                if (! empty($matchingStatuses)) {
                    $q->orWhere(function ($q2) use ($matchingStatuses) {
                        $q2->whereNull('task_workflow_id')
                            ->whereIn('status', $matchingStatuses);
                    });
                }
            })->with(['assignedTo', 'taskType']);

            if ($this->search !== '') {
                $query->where('title', 'like', '%'.$this->search.'%');
            }
            if ($this->priorityFilter !== '') {
                $query->where('priority', $this->priorityFilter);
            }
            if ($this->taskTypeFilter !== '') {
                $query->where('task_type_id', $this->taskTypeFilter);
            }

            $tasks = $query->orderBy('due_date')->limit(50)->get();

            return [
                'id' => $stage->id,
                'name' => $stage->localizedName(),
                'key' => $stage->key,
                'color' => $stage->color,
                'tasks' => $tasks->map(fn ($t) => [
                    'id' => $t->id,
                    'title' => $t->title,
                    'assignee' => $t->assignedTo?->name,
                    'due_date' => $t->due_date?->format('d/m'),
                    'priority' => $t->priority?->value ?? 'normal',
                    'priority_color' => $priorityColors[$t->priority?->value ?? 'normal'] ?? '#78716C',
                    'type_name' => $t->taskType?->localizedName(),
                    'type_color' => $t->taskType?->color ?? '#78716C',
                ])->toArray(),
                'count' => $tasks->count(),
            ];
        })->values()->toArray();
    }

    public function moveTask(string $taskId, string $toStageId): void
    {
        $task = Task::findOrFail($taskId);
        $toStage = TaskWorkflowStage::findOrFail($toStageId);

        try {
            app(TaskTransitionService::class)->transition($task, $toStage, auth()->user());
        } catch (\Exception $e) {
            // Fallback: update status directly for legacy tasks
            $statusMap = ['todo' => 'todo', 'in_progress' => 'in_progress', 'done' => 'done'];
            if (isset($statusMap[$toStage->key])) {
                $task->update(['status' => $statusMap[$toStage->key]]);
            }
        }

        $this->loadBoard();
    }

    public function updatedBoardWorkflowId(): void
    {
        $this->loadBoard();
    }

    public function render()
    {
        $query = Task::with(['assignedTo:id,name', 'taskType'])
            ->orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        if ($this->priorityFilter !== '') {
            $query->where('priority', $this->priorityFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->taskTypeFilter !== '') {
            $query->where('task_type_id', $this->taskTypeFilter);
        }

        $tasks = $query->cursorPaginate(15);

        $workspace = auth()->user()->currentWorkspace();
        $workspaceMembers = $workspace
            ? User::whereHas('workspaces', fn ($q) => $q->where('workspaces.id', $workspace->id))->get(['id', 'name'])
            : collect();

        $taskTypes = TaskType::active()->orderBy('sort_order')->get();

        $selectedTaskType = $this->formTaskTypeId
            ? $taskTypes->firstWhere('id', $this->formTaskTypeId)
            : null;

        $workflows = TaskWorkflow::orderBy('name')->get(['id', 'name']);

        if ($this->viewMode === 'board' && empty($this->boardColumns)) {
            $this->loadBoard();
        }

        return view('livewire.pages.tasks-list', [
            'tasks' => $tasks,
            'priorities' => TaskPriority::cases(),
            'statuses' => TaskStatus::cases(),
            'workspaceMembers' => $workspaceMembers,
            'workflows' => $workflows,
            'taskTypes' => $taskTypes,
            'selectedTaskType' => $selectedTaskType,
        ])->layout('components.layouts.dashboard');
    }
}
