<?php

namespace App\Http\Requests;

use App\Enums\ServiceMethod;
use App\Enums\ServiceStatus;
use App\Models\ServiceLogEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceLogEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceLogEntry::class);
    }

    public function rules(): array
    {
        return [
            'matter_id' => 'required|string|exists:matters,id',
            'served_party_contact_id' => 'required|string|exists:contacts,id',
            'service_method' => ['required', Rule::enum(ServiceMethod::class)],
            'service_date' => 'required|date',
            'service_address' => 'nullable|string',
            'served_by_name' => 'nullable|string|max:200',
            'served_to_recipient_name' => 'nullable|string|max:200',
            'proof_document_id' => 'nullable|string|exists:documents,id',
            'status' => ['required', Rule::enum(ServiceStatus::class)],
            'notes' => 'nullable|string',
        ];
    }
}
