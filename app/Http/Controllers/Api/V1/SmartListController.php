<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SmartList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SmartList::visibleTo($request->user());

        if ($request->filled('entity_type')) {
            $query->forEntity($request->input('entity_type'));
        }

        return response()->json(['data' => $query->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_type' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'filters' => 'required|array',
            'sort_order' => 'nullable|array',
            'is_shared_to_workspace' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
        ]);

        $smartList = SmartList::create(array_merge($validated, [
            'user_id' => $request->user()->id,
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $smartList], 201);
    }

    public function update(Request $request, SmartList $smartList): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'filters' => 'sometimes|array',
            'sort_order' => 'nullable|array',
            'is_shared_to_workspace' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
        ]);

        $smartList->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $smartList->fresh()]);
    }

    public function destroy(SmartList $smartList): JsonResponse
    {
        $smartList->delete();

        return response()->json(null, 204);
    }
}
