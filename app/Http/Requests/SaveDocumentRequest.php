<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('document'));
    }

    public function rules(): array
    {
        return [
            'current_version_id' => ['required', 'string', 'size:26'],
            'body' => ['required', 'array'],
            'body.type' => ['required', 'string', 'in:doc'],
            'body.content' => ['required', 'array'],
            'change_summary' => ['nullable', 'string', 'max:500'],
        ];
    }
}
