<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Services\WorkflowBundleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowBundleController extends Controller
{
    public function index(WorkflowBundleService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->listAvailable(),
        ]);
    }

    public function activate(Request $request, string $key, WorkflowBundleService $service): JsonResponse
    {
        $this->authorize('create', Automation::class);

        $user = $request->user();
        $workspace = $user->currentWorkspace();

        if (! $workspace) {
            abort(403, 'No active workspace');
        }

        $counts = $service->activate($key, $workspace, $user);

        return response()->json([
            'data' => $counts,
            'message' => __('automations.bundle_activated'),
        ], 201);
    }
}
