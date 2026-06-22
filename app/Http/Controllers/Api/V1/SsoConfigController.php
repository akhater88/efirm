<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkspaceSsoConfigRequest;
use App\Http\Requests\UpdateWorkspaceSsoConfigRequest;
use App\Http\Resources\WorkspaceSsoConfigResource;
use App\Models\WorkspaceSsoConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SsoConfigController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkspaceSsoConfig::class);

        $configs = WorkspaceSsoConfig::query()->latest()->paginate(15);

        return WorkspaceSsoConfigResource::collection($configs)->response();
    }

    public function store(StoreWorkspaceSsoConfigRequest $request): JsonResponse
    {
        $config = WorkspaceSsoConfig::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new WorkspaceSsoConfigResource($config))
            ->response()
            ->setStatusCode(201);
    }

    public function show(WorkspaceSsoConfig $ssoConfig): WorkspaceSsoConfigResource
    {
        $this->authorize('view', $ssoConfig);

        return new WorkspaceSsoConfigResource($ssoConfig);
    }

    public function update(UpdateWorkspaceSsoConfigRequest $request, WorkspaceSsoConfig $ssoConfig): WorkspaceSsoConfigResource
    {
        $ssoConfig->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new WorkspaceSsoConfigResource($ssoConfig->fresh());
    }

    public function destroy(WorkspaceSsoConfig $ssoConfig): JsonResponse
    {
        $this->authorize('delete', $ssoConfig);

        $ssoConfig->delete();

        return response()->json(null, 204);
    }
}
