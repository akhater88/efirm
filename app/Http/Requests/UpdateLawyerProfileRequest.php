<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLawyerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('lawyerProfile'));
    }

    public function rules(): array
    {
        return [
            'bar_admission_number' => 'nullable|string|max:100',
            'bar_admission_country' => 'nullable|string|size:2',
            'bar_admission_date' => 'nullable|date',
            'jurisdictions' => 'nullable|array',
            'jurisdictions.*' => 'string|max:100',
            'practice_areas' => 'nullable|array',
            'practice_areas.*' => 'string|max:100',
            'languages_spoken' => 'nullable|array',
            'languages_spoken.*' => 'string|max:10',
            'default_hourly_rate' => 'nullable|numeric|min:0|max:999999.99',
            'default_currency' => 'nullable|string|size:3',
            'position_title_ar' => 'nullable|string|max:150',
            'position_title_en' => 'nullable|string|max:150',
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive,on_leave',
            'joined_firm_date' => 'nullable|date',
        ];
    }
}
