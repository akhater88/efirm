<?php

namespace App\Http\Requests;

use App\Models\JournalEntry;
use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', JournalEntry::class);
    }

    public function rules(): array
    {
        return [
            'entry_date' => 'required|date',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ];
    }
}
