<?php

namespace App\Http\Requests;

use App\Enums\CourtType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('court'));
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'court_type' => ['sometimes', Rule::enum(CourtType::class)],
            'jurisdiction_country' => 'sometimes|string|size:2',
            'jurisdiction_governorate' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ];
    }
}
