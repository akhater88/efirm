<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('contact'));
    }

    public function rules(): array
    {
        return [
            'type' => 'sometimes|in:person,organization',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'organization_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|size:2',
            'tax_registration_number' => 'nullable|string|max:100',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|size:2',
            'is_client' => 'boolean',
            'is_counterparty' => 'boolean',
            'is_opposing_counsel' => 'boolean',
            'notes' => 'nullable|string',
            'labels' => 'nullable|array',
            'labels.*' => 'string|max:50',
            'parent_organization_id' => 'nullable|exists:contacts,id',
        ];
    }
}
