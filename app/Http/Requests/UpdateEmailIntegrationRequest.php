<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('emailIntegration'));
    }

    public function rules(): array
    {
        return [
            'oauth_access_token' => 'sometimes|string',
            'oauth_refresh_token' => 'sometimes|string',
            'oauth_expires_at' => 'nullable|date',
            'scopes_granted' => 'nullable|array',
            'scopes_granted.*' => 'string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
