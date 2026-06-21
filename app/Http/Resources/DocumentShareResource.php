<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentShareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'version_id' => $this->version_id,
            'token' => $this->token,
            'url' => $this->getPublicUrl(),
            'recipient_email' => $this->recipient_email,
            'format' => $this->format,
            'expires_at' => $this->expires_at,
            'download_count' => $this->download_count,
            'last_accessed_at' => $this->last_accessed_at,
            'is_active' => $this->isActive(),
            'created_by' => $this->whenLoaded('createdBy', fn () => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
