<?php

namespace App\Http\Requests;

use App\Enums\DocumentType;
use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Document::class);
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:docx',
                'max:25600', // 25MB in KB
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', Rule::enum(DocumentType::class)],
            'language_primary' => ['nullable', 'string', 'in:ar,en,bilingual'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => __('documents.import_file_required'),
            'file.mimes' => __('documents.import_file_mimes'),
            'file.max' => __('documents.import_file_max'),
        ];
    }
}
