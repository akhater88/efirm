<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MatterLawyerRole;
use App\Http\Controllers\Controller;
use App\Models\Matter;
use App\Models\MatterLawyer;
use App\Models\User;
use App\Services\MatterLawyerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatterLawyerController extends Controller
{
    public function __construct(
        private readonly MatterLawyerService $service,
    ) {}

    public function index(Request $request, Matter $matter): JsonResponse
    {
        $this->authorize('view', $matter);

        $query = MatterLawyer::where('matter_id', $matter->id)
            ->with(['user', 'assignedBy', 'unassignedBy']);

        if (! $request->boolean('include_history')) {
            $query->active();
        }

        $lawyers = $query->latest('assigned_at')->get();

        return response()->json(['data' => $lawyers]);
    }

    public function store(Request $request, Matter $matter): JsonResponse
    {
        $this->authorize('update', $matter);

        $validated = $request->validate([
            'user_id' => 'required|string|exists:users,id',
            'role' => 'required|string|in:lead,supporting',
            'notes' => 'nullable|string|max:1000',
        ]);

        $role = MatterLawyerRole::from($validated['role']);

        // Check if user is already actively assigned to this matter
        $existingActive = MatterLawyer::where('matter_id', $matter->id)
            ->where('user_id', $validated['user_id'])
            ->active()
            ->exists();

        if ($existingActive) {
            return response()->json([
                'message' => __('lawyers.already_assigned'),
            ], 422);
        }

        $user = User::findOrFail($validated['user_id']);

        $matterLawyer = $this->service->assignLawyer($matter, $user, $role, $request->user());

        if (isset($validated['notes'])) {
            $matterLawyer->update(['notes' => $validated['notes']]);
        }

        return response()->json([
            'data' => $matterLawyer->load(['user', 'assignedBy']),
        ], 201);
    }

    public function destroy(Request $request, Matter $matter, User $user): JsonResponse
    {
        $this->authorize('update', $matter);

        $this->service->unassignLawyer($matter, $user, $request->user());

        return response()->json(null, 204);
    }

    public function updateLead(Request $request, Matter $matter): JsonResponse
    {
        $this->authorize('update', $matter);

        $validated = $request->validate([
            'user_id' => 'required|string|exists:users,id',
        ]);

        $newLead = User::findOrFail($validated['user_id']);

        $matterLawyer = $this->service->changeLeadLawyer($matter, $newLead, $request->user());

        return response()->json([
            'data' => $matterLawyer->load(['user', 'assignedBy']),
        ]);
    }
}
