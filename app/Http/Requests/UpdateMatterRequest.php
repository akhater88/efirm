<?php

namespace App\Http\Requests;

use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMatterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('matter'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'client_id' => [
                'sometimes',
                'string',
                'exists:contacts,id',
                function ($attribute, $value, $fail) {
                    $contact = Contact::withoutGlobalScopes()->find($value);
                    if ($contact && ! $contact->is_client) {
                        $fail(__('matters.client_must_be_client'));
                    }
                },
            ],
            'practice_area' => ['sometimes', Rule::enum(PracticeArea::class)],
            'status' => ['sometimes', Rule::enum(MatterStatus::class)],
            'stage' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'internal_reference' => 'nullable|string|max:100',
            'lead_lawyer_id' => 'nullable|exists:users,id',
            'opened_at' => 'nullable|date',
            'closed_at' => 'nullable|date|after_or_equal:opened_at',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }
}
