<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtRequest;
use App\Http\Requests\UpdateCourtRequest;
use App\Http\Resources\CourtResource;
use App\Models\Court;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourtController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Court::query();

        if ($request->filled('court_type')) {
            $query->where('court_type', $request->input('court_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('name_en', 'LIKE', "%{$search}%");
            });
        }

        return CourtResource::collection($query->latest()->paginate(15));
    }

    public function store(StoreCourtRequest $request): JsonResponse
    {
        $court = Court::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new CourtResource($court))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Court $court): CourtResource
    {
        $this->authorize('view', $court);

        return new CourtResource($court->load('judges'));
    }

    public function update(UpdateCourtRequest $request, Court $court): CourtResource
    {
        $court->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new CourtResource($court->fresh());
    }

    public function destroy(Court $court): JsonResponse
    {
        $this->authorize('delete', $court);

        $court->delete();

        return response()->json(null, 204);
    }
}
