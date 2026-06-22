<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Lead::class);
    }

    public function rules(): array
    {
        return [
            'contact_id' => 'nullable|exists:contacts,id',
            'pipeline_id' => 'nullable|exists:pipelines,id',
            'title' => 'required|string|max:255',
            'source' => 'nullable|string|in:referral,website,walk_in,social_media,conference,other',
            'current_stage' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ];
    }
}
