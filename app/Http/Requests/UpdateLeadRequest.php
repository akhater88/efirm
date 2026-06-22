<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('lead'));
    }

    public function rules(): array
    {
        return [
            'contact_id' => 'nullable|exists:contacts,id',
            'pipeline_id' => 'nullable|exists:pipelines,id',
            'title' => 'sometimes|string|max:255',
            'source' => 'nullable|string|in:referral,website,walk_in,social_media,conference,other',
            'status' => 'sometimes|string|in:new,contacted,qualified,converted,lost',
            'current_stage' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ];
    }
}
