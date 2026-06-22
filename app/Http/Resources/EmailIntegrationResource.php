<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailIntegrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'user_id' => $this->user_id,
            'provider' => $this->provider,
            'email_address' => $this->email_address,
            // NEVER expose OAuth tokens in API responses
            'oauth_expires_at' => $this->oauth_expires_at,
            'scopes_granted' => $this->scopes_granted,
            'is_active' => $this->is_active,
            'last_synced_at' => $this->last_synced_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
