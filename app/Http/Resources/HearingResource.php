<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HearingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matter_id' => $this->matter_id,
            'hearing_date' => $this->hearing_date?->toIso8601String(),
            'court_id' => $this->court_id,
            'court' => new CourtResource($this->whenLoaded('court')),
            'judge_id' => $this->judge_id,
            'judge' => new JudgeResource($this->whenLoaded('judge')),
            'hearing_type' => $this->hearing_type?->value,
            'status' => $this->status?->value,
            'held_at' => $this->held_at?->toIso8601String(),
            'outcome' => $this->outcome,
            'next_action_required' => $this->next_action_required,
            'postponed_to_hearing_id' => $this->postponed_to_hearing_id,
            'our_attendee_user_id' => $this->our_attendee_user_id,
            'assigned_lawyer_user_id' => $this->assigned_lawyer_user_id,
            'lawyer_assigned_at' => $this->lawyer_assigned_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
