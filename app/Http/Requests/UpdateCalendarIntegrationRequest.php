<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('calendarIntegration'));
    }

    public function rules(): array
    {
        return [
            'calendar_id' => 'nullable|string|max:255',
            'oauth_access_token' => 'sometimes|string',
            'oauth_refresh_token' => 'sometimes|string',
            'oauth_expires_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
