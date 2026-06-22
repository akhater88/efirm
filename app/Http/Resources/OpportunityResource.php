<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contact_id' => $this->contact_id,
            'pipeline_id' => $this->pipeline_id,
            'lead_id' => $this->lead_id,
            'title' => $this->title,
            'status' => $this->status?->value,
            'current_stage' => $this->current_stage,
            'estimated_value' => $this->estimated_value,
            'currency' => $this->currency,
            'expected_close_date' => $this->expected_close_date,
            'converted_to_matter_id' => $this->converted_to_matter_id,
            'notes' => $this->notes,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'pipeline' => new PipelineResource($this->whenLoaded('pipeline')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
