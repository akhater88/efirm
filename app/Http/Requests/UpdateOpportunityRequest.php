<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('opportunity'));
    }

    public function rules(): array
    {
        return [
            'contact_id' => 'sometimes|exists:contacts,id',
            'pipeline_id' => 'nullable|exists:pipelines,id',
            'title' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:open,won,lost',
            'current_stage' => 'nullable|string|max:100',
            'estimated_value' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ];
    }
}
