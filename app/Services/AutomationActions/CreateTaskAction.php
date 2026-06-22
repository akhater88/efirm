<?php

namespace App\Services\AutomationActions;

use App\Models\Task;
use Illuminate\Support\Facades\Log;

class CreateTaskAction implements ActionHandlerInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function execute(array $payload, array $context): array
    {
        $taskData = [
            'workspace_id' => $context['workspace_id'],
            'title' => $payload['title'] ?? 'Auto-created task',
            'description' => $payload['description'] ?? null,
            'assigned_to_user_id' => $payload['assigned_to_user_id'] ?? null,
            'due_date' => isset($payload['due_in_days']) ? now()->addDays((int) $payload['due_in_days']) : null,
            'priority' => $payload['priority'] ?? 'normal',
            'status' => 'todo',
        ];

        // Only set polymorphic fields if both are present
        $taskableType = $payload['taskable_type'] ?? $context['entity_type'] ?? null;
        $taskableId = $payload['taskable_id'] ?? $context['entity_id'] ?? null;

        if ($taskableType !== null && $taskableId !== null) {
            $taskData['taskable_type'] = $taskableType;
            $taskData['taskable_id'] = $taskableId;
        }

        $task = Task::create($taskData);

        Log::info('AutomationAction: CreateTask', ['task_id' => $task->id]);

        return ['task_id' => $task->id];
    }
}
