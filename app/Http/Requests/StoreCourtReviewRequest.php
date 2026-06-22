<?php

namespace App\Http\Requests;

use App\Enums\DecisionOutcome;
use App\Enums\DecisionType;
use App\Models\CourtReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourtReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CourtReview::class);
    }

    public function rules(): array
    {
        return [
            'matter_id' => 'required|string|exists:matters,id',
            'hearing_id' => 'nullable|string|exists:hearings,id',
            'decision_date' => 'required|date',
            'decision_type' => ['required', Rule::enum(DecisionType::class)],
            'outcome' => ['required', Rule::enum(DecisionOutcome::class)],
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
