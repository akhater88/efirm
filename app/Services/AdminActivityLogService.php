<?php

namespace App\Services;

use App\Enums\AdminActivityEventType;
use App\Models\AdminActivityLog;
use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AdminActivityLogService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function log(
        AdminActivityEventType $eventType,
        ?AdminUser $admin = null,
        array $payload = [],
        ?Model $target = null,
        ?string $attemptedEmail = null,
    ): void {
        // Sanitize sensitive fields from payload
        $sanitized = self::sanitizePayload($payload);

        AdminActivityLog::create([
            'admin_user_id' => $admin?->id,
            'attempted_email' => $attemptedEmail,
            'event_type' => $eventType,
            'target_type' => $target ? $target->getMorphClass() : null,
            'target_id' => $target?->getKey(),
            'payload' => $sanitized,
            'ip_address' => Request::ip() ?? '0.0.0.0',
            'user_agent' => Request::userAgent(),
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
    }

    /**
     * Remove sensitive fields from payload before persisting.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function sanitizePayload(array $payload): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'current_password', 'new_password'];

        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $payload)) {
                $payload[$key] = '[REDACTED]';
            }
        }

        return $payload;
    }
}
