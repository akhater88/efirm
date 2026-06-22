<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJudgeRequest;
use App\Http\Requests\UpdateJudgeRequest;
use App\Http\Resources\JudgeResource;
use App\Models\Judge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JudgeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Judge::with('court');

        if ($request->filled('court_id')) {
            $query->where('court_id', $request->input('court_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('name_en', 'LIKE', "%{$search}%");
            });
        }

        return JudgeResource::collection($query->latest()->paginate(15));
    }

    public function store(StoreJudgeRequest $request): JsonResponse
    {
        $judge = Judge::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new JudgeResource($judge->load('court')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Judge $judge): JudgeResource
    {
        $this->authorize('view', $judge);

        return new JudgeResource($judge->load('court'));
    }

    public function update(UpdateJudgeRequest $request, Judge $judge): JudgeResource
    {
        $judge->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new JudgeResource($judge->fresh('court'));
    }

    public function destroy(Judge $judge): JsonResponse
    {
        $this->authorize('delete', $judge);

        $judge->delete();

        return response()->json(null, 204);
    }
}
