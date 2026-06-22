<?php

namespace App\Services\AutomationActions;

interface ActionHandlerInterface
{
    /**
     * Execute the action.
     *
     * @param  array<string, mixed>  $payload  The action_payload from the AutomationAction.
     * @param  array<string, mixed>  $context  Runtime context (trigger_payload, workspace_id, etc.).
     * @return array<string, mixed> Result data to store in the run log.
     */
    public function execute(array $payload, array $context): array;
}
