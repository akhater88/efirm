<?php

namespace App\Http\Requests;

use App\Models\TrustAccount;
use Illuminate\Foundation\Http\FormRequest;

class StoreTrustAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', TrustAccount::class);
    }

    public function rules(): array
    {
        return [
            'contact_id' => 'required|exists:contacts,id',
            'matter_id' => 'nullable|exists:matters,id',
            'name' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'currency' => 'sometimes|string|size:3',
        ];
    }
}
