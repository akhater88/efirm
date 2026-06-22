<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrustAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('trust_account'));
    }

    public function rules(): array
    {
        return [
            'contact_id' => 'sometimes|exists:contacts,id',
            'matter_id' => 'nullable|exists:matters,id',
            'name' => 'sometimes|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'currency' => 'sometimes|string|size:3',
        ];
    }
}
