<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contact_id' => $this->contact_id,
            'pipeline_id' => $this->pipeline_id,
            'title' => $this->title,
            'source' => $this->source?->value,
            'status' => $this->status?->value,
            'current_stage' => $this->current_stage,
            'notes' => $this->notes,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'converted_to_opportunity_id' => $this->converted_to_opportunity_id,
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'pipeline' => new PipelineResource($this->whenLoaded('pipeline')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
