<?php

namespace App\Http\Requests;

use App\Models\Pipeline;
use Illuminate\Foundation\Http\FormRequest;

class StorePipelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Pipeline::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stages' => 'required|array|min:2',
            'stages.*' => 'string|max:100',
            'is_default' => 'boolean',
        ];
    }
}
