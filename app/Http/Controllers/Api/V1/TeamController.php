<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Team::with(['lead', 'parentTeam'])->withCount('members')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'lead_user_id' => 'nullable|string|size:26',
            'parent_team_id' => 'nullable|string|size:26',
        ]);

        $team = Team::create(array_merge($validated, [
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $team], 201);
    }

    public function show(Team $team): JsonResponse
    {
        return response()->json([
            'data' => $team->load(['lead', 'members', 'parentTeam', 'subTeams']),
        ]);
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'lead_user_id' => 'nullable|string|size:26',
            'parent_team_id' => 'nullable|string|size:26',
        ]);

        $team->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $team->fresh()]);
    }

    public function destroy(Team $team): JsonResponse
    {
        $team->delete();

        return response()->json(null, 204);
    }

    public function attachMember(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|string|size:26',
            'role_in_team' => 'nullable|string|max:50',
        ]);

        $team->members()->syncWithoutDetaching([
            $validated['user_id'] => ['role_in_team' => $validated['role_in_team'] ?? null],
        ]);

        return response()->json(['data' => $team->load('members')]);
    }

    public function detachMember(Team $team, string $userId): JsonResponse
    {
        $team->members()->detach($userId);

        return response()->json(null, 204);
    }
}
