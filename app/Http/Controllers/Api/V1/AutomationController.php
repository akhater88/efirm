<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Services\AutomationRunnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Automation::class);

        $query = Automation::with('actions');

        if ($request->filled('trigger_event')) {
            $query->where('trigger_event', $request->input('trigger_event'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json([
            'data' => $query->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Automation::class);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_event' => 'required|string|max:100',
            'conditions' => 'required|array',
            'is_active' => 'nullable|boolean',
            'actions' => 'required|array|min:1',
            'actions.*.action_type' => 'required|string|max:100',
            'actions.*.action_payload' => 'required|array',
            'actions.*.sort_order' => 'nullable|integer',
            'actions.*.stop_on_error' => 'nullable|boolean',
        ]);

        $automation = Automation::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'description' => $validated['description'] ?? null,
            'trigger_event' => $validated['trigger_event'],
            'conditions' => $validated['conditions'],
            'is_active' => $validated['is_active'] ?? true,
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]);

        foreach ($validated['actions'] as $i => $actionData) {
            $automation->actions()->create([
                'sort_order' => $actionData['sort_order'] ?? $i,
                'action_type' => $actionData['action_type'],
                'action_payload' => $actionData['action_payload'],
                'stop_on_error' => $actionData['stop_on_error'] ?? true,
            ]);
        }

        return response()->json([
            'data' => $automation->load('actions'),
        ], 201);
    }

    public function show(Automation $automation): JsonResponse
    {
        $this->authorize('view', $automation);

        return response()->json([
            'data' => $automation->load(['actions', 'runs' => fn ($q) => $q->latest('created_at')->limit(10)]),
        ]);
    }

    public function update(Request $request, Automation $automation): JsonResponse
    {
        $this->authorize('update', $automation);

        $validated = $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'trigger_event' => 'sometimes|string|max:100',
            'conditions' => 'sometimes|array',
            'is_active' => 'nullable|boolean',
            'actions' => 'sometimes|array|min:1',
            'actions.*.action_type' => 'required_with:actions|string|max:100',
            'actions.*.action_payload' => 'required_with:actions|array',
            'actions.*.sort_order' => 'nullable|integer',
            'actions.*.stop_on_error' => 'nullable|boolean',
        ]);

        $automation->update(array_merge(
            collect($validated)->except('actions')->toArray(),
            ['updated_by_user_id' => $request->user()->id],
        ));

        if (isset($validated['actions'])) {
            $automation->actions()->delete();
            foreach ($validated['actions'] as $i => $actionData) {
                $automation->actions()->create([
                    'sort_order' => $actionData['sort_order'] ?? $i,
                    'action_type' => $actionData['action_type'],
                    'action_payload' => $actionData['action_payload'],
                    'stop_on_error' => $actionData['stop_on_error'] ?? true,
                ]);
            }
        }

        return response()->json([
            'data' => $automation->fresh()->load('actions'),
        ]);
    }

    public function destroy(Automation $automation): JsonResponse
    {
        $this->authorize('delete', $automation);

        $automation->delete();

        return response()->json(null, 204);
    }

    public function test(Request $request, Automation $automation, AutomationRunnerService $runner): JsonResponse
    {
        $this->authorize('update', $automation);

        $validated = $request->validate([
            'trigger_payload' => 'required|array',
        ]);

        $run = $runner->run($automation, $validated['trigger_payload'], testMode: true);

        return response()->json([
            'data' => $run,
        ]);
    }
}
