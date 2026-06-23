<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource transformer for ExpertReport.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 and #19.
 */
class ExpertReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matter_id' => $this->matter_id,
            'expert_name_ar' => $this->expert_name_ar,
            'expert_name_en' => $this->expert_name_en,
            'report_type' => $this->report_type?->value,
            'received_date' => $this->received_date?->toDateString(),
            'objection_deadline_date' => $this->objection_deadline_date?->toDateString(),
            'objection_filed' => $this->objection_filed,
            'objection_filed_date' => $this->objection_filed_date?->toDateString(),
            'our_position' => $this->our_position?->value,
            'summary_ar' => $this->summary_ar,
            'summary_en' => $this->summary_en,
            'document_id' => $this->document_id,
            'created_by_user_id' => $this->created_by_user_id,
            'updated_by_user_id' => $this->updated_by_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
