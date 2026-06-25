<?php

namespace App\Livewire\Pages;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Livewire\Component;

class TasksList extends Component
{
    public string $search = '';

    public string $priorityFilter = '';

    public string $statusFilter = '';

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

    public function resetPage(): void
    {
        // Reset cursor pagination by removing the cursor query param
    }

    public function render()
    {
        $query = Task::with('assignedTo:id,name')
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

        $tasks = $query->cursorPaginate(15);

        return view('livewire.pages.tasks-list', [
            'tasks' => $tasks,
            'priorities' => TaskPriority::cases(),
            'statuses' => TaskStatus::cases(),
        ])->layout('layouts.dashboard')
            ->section('content');
    }
}
