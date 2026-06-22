<?php

namespace App\Http\Requests;

use App\Enums\HearingStatus;
use App\Enums\HearingType;
use App\Models\Hearing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHearingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Hearing::class);
    }

    public function rules(): array
    {
        return [
            'matter_id' => 'required|string|exists:matters,id',
            'hearing_date' => 'required|date',
            'court_id' => 'required|string|exists:courts,id',
            'judge_id' => 'nullable|string|exists:judges,id',
            'hearing_type' => ['required', Rule::enum(HearingType::class)],
            'status' => ['sometimes', Rule::enum(HearingStatus::class)],
            'held_at' => 'nullable|date',
            'outcome' => 'nullable|string',
            'next_action_required' => 'nullable|string',
            'postponed_to_hearing_id' => 'nullable|string|exists:hearings,id',
            'our_attendee_user_id' => 'nullable|string|exists:users,id',
        ];
    }
}
