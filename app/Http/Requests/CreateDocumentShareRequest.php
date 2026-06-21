<?php

namespace App\Http\Requests;

use App\Models\DocumentShare;
use Illuminate\Foundation\Http\FormRequest;

class CreateDocumentShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', DocumentShare::class);
    }

    public function rules(): array
    {
        return [
            'version_id' => ['nullable', 'string', 'size:26'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'format' => ['nullable', 'string', 'in:docx,pdf'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
