<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'client_id' => $this->client_id,
            'client' => new ContactResource($this->whenLoaded('client')),
            'practice_area' => $this->practice_area?->value,
            'status' => $this->status?->value,
            'stage' => $this->stage,
            'description' => $this->description,
            'internal_reference' => $this->internal_reference,
            'lead_lawyer_id' => $this->lead_lawyer_id,
            'lead_lawyer' => new UserResource($this->whenLoaded('leadLawyer')),
            'counterparties' => ContactResource::collection($this->whenLoaded('counterparties')),
            'lawyers' => UserResource::collection($this->whenLoaded('lawyers')),
            'opened_at' => $this->opened_at?->toDateString(),
            'closed_at' => $this->closed_at?->toDateString(),
            'tags' => $this->tags,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
