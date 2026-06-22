<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkspaceSsoConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'provider_type' => $this->provider_type,
            'provider_name' => $this->provider_name,
            'idp_metadata_url' => $this->idp_metadata_url,
            'idp_entity_id' => $this->idp_entity_id,
            'idp_sso_url' => $this->idp_sso_url,
            // NEVER expose idp_certificate in API responses
            'sp_entity_id' => $this->sp_entity_id,
            'attribute_mapping' => $this->attribute_mapping,
            'enforce_for_domain' => $this->enforce_for_domain,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
