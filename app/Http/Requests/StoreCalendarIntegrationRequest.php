<?php

namespace App\Http\Requests;

use App\Models\CalendarIntegration;
use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CalendarIntegration::class);
    }

    public function rules(): array
    {
        return [
            'provider' => 'required|string|in:google,outlook',
            'calendar_id' => 'nullable|string|max:255',
            'oauth_access_token' => 'required|string',
            'oauth_refresh_token' => 'required|string',
            'oauth_expires_at' => 'nullable|date',
        ];
    }
}
