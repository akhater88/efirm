<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('invoice'));
    }

    public function rules(): array
    {
        return [
            'contact_id' => 'sometimes|exists:contacts,id',
            'matter_id' => 'nullable|exists:matters,id',
            'status' => 'sometimes|string|in:draft,sent,paid,partially_paid,overdue,cancelled',
            'currency' => 'sometimes|string|size:3',
            'tax_rate' => 'sometimes|numeric|min:0|max:100',
            'issue_date' => 'sometimes|date',
            'due_date' => 'sometimes|date',
            'notes' => 'nullable|string',
            'lines' => 'sometimes|array|min:1',
            'lines.*.description' => 'required_with:lines|string|max:500',
            'lines.*.quantity' => 'required_with:lines|numeric|min:0.01',
            'lines.*.unit_price' => 'required_with:lines|numeric|min:0',
        ];
    }
}
