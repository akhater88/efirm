<?php

namespace App\Http\Requests;

use App\Enums\ExpertReportPosition;
use App\Enums\ExpertReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for updating an expert report.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 and #19.
 */
class UpdateExpertReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('expert_report'));
    }

    public function rules(): array
    {
        return [
            'expert_name_ar' => 'sometimes|string|max:200',
            'expert_name_en' => 'nullable|string|max:200',
            'report_type' => ['sometimes', Rule::enum(ExpertReportType::class)],
            'received_date' => 'sometimes|date',
            'objection_deadline_date' => 'nullable|date',
            'objection_filed' => 'sometimes|boolean',
            'objection_filed_date' => 'nullable|date',
            'our_position' => ['sometimes', Rule::enum(ExpertReportPosition::class)],
            'summary_ar' => 'nullable|string',
            'summary_en' => 'nullable|string',
            'document_id' => 'nullable|string|exists:documents,id',
        ];
    }
}
