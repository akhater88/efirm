<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matter_id' => $this->matter_id,
            'hearing_id' => $this->hearing_id,
            'decision_date' => $this->decision_date?->toDateString(),
            'decision_type' => $this->decision_type?->value,
            'outcome' => $this->outcome?->value,
            'summary_ar' => $this->summary_ar,
            'summary_en' => $this->summary_en,
            'decision_document_id' => $this->decision_document_id,
            'appealable' => $this->appealable,
            'appeal_deadline_date' => $this->appeal_deadline_date?->toDateString(),
            'appeal_filed' => $this->appeal_filed,
            'next_steps' => $this->next_steps,
            'hearing' => new HearingResource($this->whenLoaded('hearing')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
