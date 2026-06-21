<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Task::with(['assignedTo', 'taskable', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to_user_id', $request->input('assigned_to'));
        }

        if ($request->filled('taskable_type') && $request->filled('taskable_id')) {
            $query->where('taskable_type', $request->input('taskable_type'))
                ->where('taskable_id', $request->input('taskable_id'));
        }

        return response()->json([
            'data' => $query->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'taskable_type' => 'required|string|in:matter,contact,document,obligation',
            'taskable_id' => 'required|string|size:26',
            'assigned_to_user_id' => 'nullable|string|size:26',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
            'status' => 'nullable|string|in:todo,in_progress,blocked,done,cancelled',
            'tags' => 'nullable|array',
        ]);

        $task = Task::create(array_merge($validated, [
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $task->load(['assignedTo', 'taskable'])], 201);
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json([
            'data' => $task->load(['assignedTo', 'taskable', 'createdBy']),
        ]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'assigned_to_user_id' => 'nullable|string|size:26',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
            'status' => 'nullable|string|in:todo,in_progress,blocked,done,cancelled',
            'tags' => 'nullable|array',
        ]);

        $task->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $task->fresh()]);
    }

    public function complete(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->markComplete($request->user());

        return response()->json(['data' => $task->fresh()]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }
}
