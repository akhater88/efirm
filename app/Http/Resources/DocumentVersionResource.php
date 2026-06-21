<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'version_number' => $this->version_number,
            'body' => $this->when($request->routeIs('api.v1.documents.versions.show'), $this->body),
            'body_hash' => $this->body_hash,
            'change_summary' => $this->change_summary,
            'created_by_user_id' => $this->created_by_user_id,
            'created_by' => $this->whenLoaded('createdBy', fn () => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
