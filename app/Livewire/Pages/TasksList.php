<?php

namespace App\Livewire\Pages;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Livewire\Component;

class TasksList extends Component
{
    public string $search = '';

    public string $priorityFilter = '';

    public string $statusFilter = '';

    public string $taskTypeFilter = '';

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

        return view('livewire.pages.tasks-list', [
            'tasks' => $tasks,
            'priorities' => TaskPriority::cases(),
            'statuses' => TaskStatus::cases(),
            'workspaceMembers' => $workspaceMembers,
            'taskTypes' => $taskTypes,
            'selectedTaskType' => $selectedTaskType,
        ])->layout('components.layouts.dashboard');
    }
}
