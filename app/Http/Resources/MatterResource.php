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
            'is_litigation' => $this->is_litigation,
            'court_id' => $this->court_id,
            'court' => new CourtResource($this->whenLoaded('court')),
            'judge_id' => $this->judge_id,
            'judge' => new JudgeResource($this->whenLoaded('judge')),
            'court_case_number' => $this->court_case_number,
            'case_number_internal' => $this->case_number_internal,
            'litigation_status' => $this->litigation_status?->value,
            'filed_date' => $this->filed_date?->toDateString(),
            'next_hearing_date' => $this->next_hearing_date?->toDateString(),
            'representation_role' => $this->representation_role?->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
