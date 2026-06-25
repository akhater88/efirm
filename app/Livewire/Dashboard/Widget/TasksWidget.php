<?php

namespace App\Livewire\Dashboard\Widget;

use App\Models\Task;
use Livewire\Component;

class TasksWidget extends Component
{
    public function render()
    {
        $tasks = Task::query()
            ->where('assigned_to_user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get(['id', 'title', 'priority', 'status', 'updated_at']);

        return view('livewire.dashboard.widget.tasks-widget', [
            'tasks' => $tasks,
        ]);
    }
}
