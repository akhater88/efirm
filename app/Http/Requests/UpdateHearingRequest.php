<?php

namespace App\Http\Requests;

use App\Enums\HearingStatus;
use App\Enums\HearingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHearingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('hearing'));
    }

    public function rules(): array
    {
        return [
            'hearing_date' => 'sometimes|date',
            'court_id' => 'sometimes|string|exists:courts,id',
            'judge_id' => 'nullable|string|exists:judges,id',
            'hearing_type' => ['sometimes', Rule::enum(HearingType::class)],
            'status' => ['sometimes', Rule::enum(HearingStatus::class)],
            'held_at' => 'nullable|date',
            'outcome' => 'nullable|string',
            'next_action_required' => 'nullable|string',
            'postponed_to_hearing_id' => 'nullable|string|exists:hearings,id',
            'postponement_reason_ar' => 'required_with:postponed_to_hearing_id|nullable|string|min:10',
            'postponement_reason_en' => 'nullable|string',
            'postponement_initiated_by' => 'required_with:postponed_to_hearing_id|nullable|string|in:our_side,opposing_side,court,unknown',
            'our_attendee_user_id' => 'nullable|string|exists:users,id',
        ];
    }
}
