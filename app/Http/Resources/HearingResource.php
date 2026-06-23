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
            'judge_statement_ar' => $this->judge_statement_ar,
            'judge_statement_en' => $this->judge_statement_en,
            'outcome_summary_ar' => $this->outcome_summary_ar,
            'outcome_summary_en' => $this->outcome_summary_en,
            'our_submissions_made' => $this->our_submissions_made,
            'opposing_submissions_made' => $this->opposing_submissions_made,
            'next_session_required_actions_ar' => $this->next_session_required_actions_ar,
            'next_session_required_actions_en' => $this->next_session_required_actions_en,
            'session_attended_by' => $this->session_attended_by,
            'next_action_required' => $this->next_action_required,
            'action_items' => HearingActionItemResource::collection($this->whenLoaded('actionItems')),
            'postponed_to_hearing_id' => $this->postponed_to_hearing_id,
            'our_attendee_user_id' => $this->our_attendee_user_id,
            'assigned_lawyer_user_id' => $this->assigned_lawyer_user_id,
            'lawyer_assigned_at' => $this->lawyer_assigned_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
