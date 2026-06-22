<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskWorkflow;
use App\Models\TaskWorkflowApproval;
use App\Models\TaskWorkflowStage;
use App\Services\TaskApprovalService;
use App\Services\TaskTransitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskWorkflowController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TaskWorkflow::class);

        $query = TaskWorkflow::with('stages');

        if ($request->filled('applies_to_task_type')) {
            $query->where('applies_to_task_type', $request->input('applies_to_task_type'));
        }

        return response()->json([
            'data' => $query->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', TaskWorkflow::class);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
            'applies_to_task_type' => 'nullable|string|max:100',
            'stages' => 'required|array|min:1',
            'stages.*.name_ar' => 'required|string|max:100',
            'stages.*.name_en' => 'required|string|max:100',
            'stages.*.key' => 'required|string|max:50',
            'stages.*.sort_order' => 'required|integer',
            'stages.*.is_initial' => 'nullable|boolean',
            'stages.*.is_terminal' => 'nullable|boolean',
            'stages.*.color' => 'nullable|string|max:20',
            'stages.*.requires_approval' => 'nullable|boolean',
            'transitions' => 'nullable|array',
            'transitions.*.from_stage_key' => 'required_with:transitions|string',
            'transitions.*.to_stage_key' => 'required_with:transitions|string',
            'transitions.*.requires_role' => 'nullable|string',
            'transitions.*.auto_transition_after_hours' => 'nullable|integer',
        ]);

        // If setting as default, unset other defaults in this workspace
        if (! empty($validated['is_default'])) {
            TaskWorkflow::where('is_default', true)->update(['is_default' => false]);
        }

        $workflow = TaskWorkflow::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'applies_to_task_type' => $validated['applies_to_task_type'] ?? null,
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]);

        // Create stages
        $stageMap = [];
        foreach ($validated['stages'] as $stageData) {
            $stage = $workflow->stages()->create($stageData);
            $stageMap[$stageData['key']] = $stage;
        }

        // Create transitions
        if (! empty($validated['transitions'])) {
            foreach ($validated['transitions'] as $transitionData) {
                $fromStage = $stageMap[$transitionData['from_stage_key']] ?? null;
                $toStage = $stageMap[$transitionData['to_stage_key']] ?? null;

                if ($fromStage && $toStage) {
                    $workflow->transitions()->create([
                        'from_stage_id' => $fromStage->id,
                        'to_stage_id' => $toStage->id,
                        'requires_role' => $transitionData['requires_role'] ?? null,
                        'auto_transition_after_hours' => $transitionData['auto_transition_after_hours'] ?? null,
                    ]);
                }
            }
        }

        return response()->json([
            'data' => $workflow->load(['stages', 'transitions']),
        ], 201);
    }

    public function show(TaskWorkflow $taskWorkflow): JsonResponse
    {
        $this->authorize('view', $taskWorkflow);

        return response()->json([
            'data' => $taskWorkflow->load(['stages', 'transitions.fromStage', 'transitions.toStage']),
        ]);
    }

    public function update(Request $request, TaskWorkflow $taskWorkflow): JsonResponse
    {
        $this->authorize('update', $taskWorkflow);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
            'applies_to_task_type' => 'nullable|string|max:100',
        ]);

        // If setting as default, unset other defaults in this workspace
        if (! empty($validated['is_default'])) {
            TaskWorkflow::where('is_default', true)
                ->where('id', '!=', $taskWorkflow->id)
                ->update(['is_default' => false]);
        }

        $taskWorkflow->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json([
            'data' => $taskWorkflow->fresh()->load('stages'),
        ]);
    }

    public function destroy(TaskWorkflow $taskWorkflow): JsonResponse
    {
        $this->authorize('delete', $taskWorkflow);

        // Additional guard: cannot delete if workflow has active tasks
        // (Policy before() short-circuits for Owner, so check here too)
        $hasActiveTasks = $taskWorkflow->tasks()
            ->whereNotIn('status', ['done', 'cancelled'])
            ->exists();

        if ($hasActiveTasks) {
            abort(403, __('task_workflows.error_has_active_tasks'));
        }

        $taskWorkflow->delete();

        return response()->json(null, 204);
    }

    public function transition(Request $request, Task $task, TaskTransitionService $service): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'to_stage_id' => 'required|string|size:26',
        ]);

        $toStage = TaskWorkflowStage::findOrFail($validated['to_stage_id']);

        $result = $service->transition($task, $toStage, $request->user());

        if ($result instanceof TaskWorkflowApproval) {
            return response()->json([
                'data' => $result->load(['fromStage', 'toStage', 'approver']),
                'message' => __('task_workflows.approval_requested'),
            ], 202);
        }

        return response()->json([
            'data' => $result->load('currentStage'),
        ]);
    }

    public function respondToApproval(Request $request, TaskWorkflowApproval $taskWorkflowApproval, TaskApprovalService $service): JsonResponse
    {
        $validated = $request->validate([
            'decision' => 'required|string|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        if ($validated['decision'] === 'approve') {
            $task = $service->approve($taskWorkflowApproval, $request->user(), $validated['notes'] ?? null);

            return response()->json([
                'data' => $task->load('currentStage'),
                'message' => __('task_workflows.approval_approved'),
            ]);
        }

        $approval = $service->reject($taskWorkflowApproval, $request->user(), $validated['notes'] ?? null);

        return response()->json([
            'data' => $approval,
            'message' => __('task_workflows.approval_rejected'),
        ]);
    }
}
