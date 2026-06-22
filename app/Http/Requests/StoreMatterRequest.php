<?php

namespace App\Http\Requests;

use App\Enums\LitigationStatus;
use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use App\Enums\RepresentationRole;
use App\Models\Contact;
use App\Models\Matter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMatterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Matter::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'client_id' => [
                'required',
                'string',
                'exists:contacts,id',
                function ($attribute, $value, $fail) {
                    $contact = Contact::withoutGlobalScopes()->find($value);
                    if ($contact && ! $contact->is_client) {
                        $fail(__('matters.client_must_be_client'));
                    }
                },
            ],
            'practice_area' => ['required', Rule::enum(PracticeArea::class)],
            'status' => ['sometimes', Rule::enum(MatterStatus::class)],
            'stage' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'internal_reference' => 'nullable|string|max:100',
            'lead_lawyer_id' => 'nullable|exists:users,id',
            'opened_at' => 'nullable|date',
            'closed_at' => 'nullable|date|after_or_equal:opened_at',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_litigation' => 'sometimes|boolean',
            'court_id' => 'nullable|string|exists:courts,id',
            'judge_id' => 'nullable|string|exists:judges,id',
            'court_case_number' => 'nullable|string|max:100',
            'case_number_internal' => 'nullable|string|max:100',
            'litigation_status' => ['nullable', Rule::enum(LitigationStatus::class)],
            'filed_date' => 'nullable|date',
            'next_hearing_date' => 'nullable|date',
            'representation_role' => ['nullable', Rule::enum(RepresentationRole::class)],
        ];
    }
}
