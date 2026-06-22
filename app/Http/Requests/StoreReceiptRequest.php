<?php

namespace App\Http\Requests;

use App\Models\Receipt;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Receipt::class);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card,other',
            'received_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
