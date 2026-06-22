<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePipelineRequest;
use App\Http\Requests\UpdatePipelineRequest;
use App\Http\Resources\PipelineResource;
use App\Models\Pipeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PipelineController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return PipelineResource::collection(
            Pipeline::query()->latest()->paginate(15)
        );
    }

    public function store(StorePipelineRequest $request): JsonResponse
    {
        $pipeline = Pipeline::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new PipelineResource($pipeline))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Pipeline $pipeline): PipelineResource
    {
        $this->authorize('view', $pipeline);

        return new PipelineResource($pipeline);
    }

    public function update(UpdatePipelineRequest $request, Pipeline $pipeline): PipelineResource
    {
        $pipeline->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new PipelineResource($pipeline->fresh());
    }

    public function destroy(Pipeline $pipeline): JsonResponse
    {
        $this->authorize('delete', $pipeline);

        $pipeline->delete();

        return response()->json(null, 204);
    }
}
