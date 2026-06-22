<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePipelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('pipeline'));
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'stages' => 'sometimes|array|min:2',
            'stages.*' => 'string|max:100',
            'is_default' => 'boolean',
        ];
    }
}
