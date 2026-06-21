<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'matter_id' => $this->matter_id,
            'title' => $this->title,
            'document_type' => $this->document_type,
            'language_primary' => $this->language_primary,
            'status' => $this->status,
            'current_version_id' => $this->current_version_id,
            'original_file_url' => $this->original_file_url,
            'metadata' => $this->metadata,
            'current_version' => $this->whenLoaded('currentVersion', fn () => [
                'id' => $this->currentVersion->id,
                'version_number' => $this->currentVersion->version_number,
                'body_hash' => $this->currentVersion->body_hash,
                'change_summary' => $this->currentVersion->change_summary,
                'created_by_user_id' => $this->currentVersion->created_by_user_id,
                'created_at' => $this->currentVersion->created_at,
            ]),
            'versions_count' => $this->whenCounted('versions'),
            'created_by_user_id' => $this->created_by_user_id,
            'updated_by_user_id' => $this->updated_by_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
