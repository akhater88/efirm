<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'preferred_locale' => $this->preferred_locale,
            'current_workspace' => $this->when(
                $this->currentWorkspace(),
                fn () => new WorkspaceResource($this->currentWorkspace()),
            ),
            'current_role' => $this->currentRole()?->value,
            'workspaces' => WorkspaceResource::collection(
                $this->whenLoaded('workspaceMembers', function () {
                    return $this->workspaceMembers->map(fn ($m) => $m->workspace);
                })
            ),
            'created_at' => $this->created_at,
        ];
    }
}
