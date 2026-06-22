<?php

namespace App\Services\AutomationActions;

use Illuminate\Support\Facades\Log;

/**
 * Stub — logs instead of actually sending notifications.
 * Real implementation deferred to notification system integration.
 */
class NotifyUserAction implements ActionHandlerInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function execute(array $payload, array $context): array
    {
        $userId = $payload['user_id'] ?? 'unknown';
        $message = $payload['message'] ?? 'No message';

        Log::info('AutomationAction: NotifyUser (stub)', [
            'user_id' => $userId,
            'message' => $message,
            'workspace_id' => $context['workspace_id'],
        ]);

        return [
            'stub' => true,
            'user_id' => $userId,
            'message' => $message,
        ];
    }
}
