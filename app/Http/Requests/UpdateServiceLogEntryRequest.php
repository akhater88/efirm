<?php

namespace App\Http\Requests;

use App\Enums\ServiceMethod;
use App\Enums\ServiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceLogEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('serviceLogEntry'));
    }

    public function rules(): array
    {
        return [
            'served_party_contact_id' => 'sometimes|string|exists:contacts,id',
            'service_method' => ['sometimes', Rule::enum(ServiceMethod::class)],
            'service_date' => 'sometimes|date',
            'service_address' => 'nullable|string',
            'served_by_name' => 'nullable|string|max:200',
            'served_to_recipient_name' => 'nullable|string|max:200',
            'proof_document_id' => 'nullable|string|exists:documents,id',
            'status' => ['sometimes', Rule::enum(ServiceStatus::class)],
            'notes' => 'nullable|string',
        ];
    }
}
