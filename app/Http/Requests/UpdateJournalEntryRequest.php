<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('journal_entry'));
    }

    public function rules(): array
    {
        return [
            'entry_date' => 'sometimes|date',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'lines' => 'sometimes|array|min:2',
            'lines.*.account_id' => 'required_with:lines|exists:accounts,id',
            'lines.*.debit' => 'required_with:lines|numeric|min:0',
            'lines.*.credit' => 'required_with:lines|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ];
    }
}
