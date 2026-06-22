<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('receipt'));
    }

    public function rules(): array
    {
        return [
            'amount' => 'sometimes|numeric|min:0.01',
            'payment_method' => 'sometimes|string|in:cash,bank_transfer,check,credit_card,other',
            'received_date' => 'sometimes|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
