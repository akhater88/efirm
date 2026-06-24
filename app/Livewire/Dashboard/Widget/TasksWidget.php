<?php

namespace App\Livewire\Dashboard\Widget;

use App\Models\Task;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class TasksWidget extends Component
{
    public function render()
    {
        $user = auth()->user();
        $workspace = $user->currentWorkspace();

        $cacheKey = "dashboard:tasks:{$workspace?->id}:{$user->id}";

        $tasks = Cache::remember($cacheKey, 300, function () use ($user) {
            return Task::query()
                ->where('assigned_to_user_id', $user->id)
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(['id', 'title', 'priority', 'status', 'updated_at']);
        });

        return view('livewire.dashboard.widget.tasks-widget', [
            'tasks' => $tasks,
        ]);
    }
}
