<?php

namespace App\Http\Requests;

use App\Models\Opportunity;
use Illuminate\Foundation\Http\FormRequest;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Opportunity::class);
    }

    public function rules(): array
    {
        return [
            'contact_id' => 'required|exists:contacts,id',
            'pipeline_id' => 'nullable|exists:pipelines,id',
            'lead_id' => 'nullable|exists:leads,id',
            'title' => 'required|string|max:255',
            'current_stage' => 'nullable|string|max:100',
            'estimated_value' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ];
    }
}
