<?php

namespace App\Http\Requests;

use App\Models\EmailIntegration;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmailIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EmailIntegration::class);
    }

    public function rules(): array
    {
        return [
            'provider' => 'required|string|in:outlook,gmail',
            'email_address' => 'required|email|max:255',
            'oauth_access_token' => 'required|string',
            'oauth_refresh_token' => 'required|string',
            'oauth_expires_at' => 'nullable|date',
            'scopes_granted' => 'nullable|array',
            'scopes_granted.*' => 'string|max:255',
        ];
    }
}
