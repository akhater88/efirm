<?php

namespace App\Services\AutomationActions;

use Illuminate\Support\Facades\Log;

/**
 * Stub — logs instead of actually sending email.
 * Real implementation deferred to SURGE with email integration.
 */
class SendEmailAction implements ActionHandlerInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function execute(array $payload, array $context): array
    {
        $to = $payload['to'] ?? 'unknown';
        $subject = $payload['subject'] ?? 'No subject';

        Log::info('AutomationAction: SendEmail (stub)', [
            'to' => $to,
            'subject' => $subject,
            'workspace_id' => $context['workspace_id'],
        ]);

        return [
            'stub' => true,
            'to' => $to,
            'subject' => $subject,
        ];
    }
}
