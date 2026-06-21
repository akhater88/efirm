<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\SwitchWorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends Controller
{
    public function store(StoreWorkspaceRequest $request): JsonResponse
    {
        $workspace = DB::transaction(function () use ($request) {
            $workspace = Workspace::create([
                'name' => $request->validated('name'),
                'default_locale' => $request->validated('default_locale', 'ar'),
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]);

            WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id' => $request->user()->id,
                'role' => Role::Owner,
                'joined_at' => now(),
                'created_by_user_id' => $request->user()->id,
            ]);

            return $workspace;
        });

        return (new WorkspaceResource($workspace))
            ->response()
            ->setStatusCode(201);
    }

    public function switch(SwitchWorkspaceRequest $request): WorkspaceResource
    {
        $user = $request->user();
        $workspace = Workspace::withoutGlobalScopes()->findOrFail($request->validated('workspace_id'));

        if (! $user->belongsToWorkspace($workspace)) {
            abort(403, __('workspace.not_a_member'));
        }

        $user->switchWorkspace($workspace);

        return new WorkspaceResource($workspace);
    }
}
