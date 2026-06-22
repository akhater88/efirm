<?php

namespace App\Http\Requests;

use App\Enums\CourtType;
use App\Models\Court;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Court::class);
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'court_type' => ['required', Rule::enum(CourtType::class)],
            'jurisdiction_country' => 'required|string|size:2',
            'jurisdiction_governorate' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ];
    }
}
