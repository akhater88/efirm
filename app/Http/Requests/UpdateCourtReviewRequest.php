<?php

namespace App\Http\Requests;

use App\Enums\DecisionOutcome;
use App\Enums\DecisionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourtReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('courtReview'));
    }

    public function rules(): array
    {
        return [
            'hearing_id' => 'nullable|string|exists:hearings,id',
            'decision_date' => 'sometimes|date',
            'decision_type' => ['sometimes', Rule::enum(DecisionType::class)],
            'outcome' => ['sometimes', Rule::enum(DecisionOutcome::class)],
            'summary_ar' => 'nullable|string',
            'summary_en' => 'nullable|string',
            'decision_document_id' => 'nullable|string|exists:documents,id',
            'appealable' => 'sometimes|boolean',
            'appeal_deadline_date' => 'nullable|date',
            'appeal_filed' => 'sometimes|boolean',
            'next_steps' => 'nullable|string',
        ];
    }
}
