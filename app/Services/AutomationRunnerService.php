<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\AutomationRun;
use App\Services\AutomationActions\ActionHandlerInterface;
use App\Services\AutomationActions\CreateTaskAction;
use App\Services\AutomationActions\NotifyUserAction;
use App\Services\AutomationActions\SendEmailAction;
use Illuminate\Support\Collection;

class AutomationRunnerService
{
    private const MAX_DEPTH = 5;

    /**
     * Map of action_type => handler class.
     *
     * @var array<string, class-string<ActionHandlerInterface>>
     */
    private array $actionHandlers = [
        'create_task' => CreateTaskAction::class,
        'send_email' => SendEmailAction::class,
        'notify_user' => NotifyUserAction::class,
    ];

    public function __construct(
        private readonly ConditionEvaluatorService $conditionEvaluator,
    ) {}

    /**
     * Find all active automations matching a trigger event in a workspace.
     *
     * @return Collection<int, Automation>
     */
    public function findMatchingAutomations(string $triggerEvent, string $workspaceId): Collection
    {
        return Automation::withoutGlobalScope('workspace')
            ->where('workspace_id', $workspaceId)
            ->where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->with('actions')
            ->get();
    }

    /**
     * Run an automation with the given trigger payload.
     *
     * @param  array<string, mixed>  $triggerPayload
     */
    public function run(Automation $automation, array $triggerPayload, bool $testMode = false, int $depth = 0): AutomationRun
    {
        if ($depth >= self::MAX_DEPTH) {
            return $this->createRun($automation, $triggerPayload, 'skipped', [
                'result' => 'loop_prevention',
                'message' => 'Maximum automation depth of '.self::MAX_DEPTH.' reached',
            ]);
        }

        $startTime = hrtime(true);

        // Evaluate conditions
        $conditions = $automation->conditions ?? [];
        $conditionsResult = [];

        try {
            $conditionsMet = $this->conditionEvaluator->evaluate($conditions, $triggerPayload);
            $conditionsResult = ['met' => $conditionsMet];
        } catch (\Throwable $e) {
            return $this->createRun(
                $automation,
                $triggerPayload,
                'failed',
                null,
                null,
                $e->getMessage(),
                $this->elapsedMs($startTime),
            );
        }

        if (! $conditionsMet) {
            return $this->createRun(
                $automation,
                $triggerPayload,
                'skipped',
                $conditionsResult,
                null,
                null,
                $this->elapsedMs($startTime),
            );
        }

        // Execute actions in sequence
        $actionsExecuted = [];
        $status = 'completed';
        $errorMessage = null;

        $context = [
            'workspace_id' => $automation->workspace_id,
            'trigger_event' => $automation->trigger_event,
            'trigger_payload' => $triggerPayload,
            'test_mode' => $testMode,
            'depth' => $depth,
        ];

        foreach ($automation->actions()->orderBy('sort_order')->get() as $action) {
            try {
                $handler = $this->resolveHandler($action->action_type);
                $result = $handler->execute($action->action_payload ?? [], $context);

                $actionsExecuted[] = [
                    'action_id' => $action->id,
                    'action_type' => $action->action_type,
                    'status' => 'completed',
                    'result' => $result,
                ];
            } catch (\Throwable $e) {
                $actionsExecuted[] = [
                    'action_id' => $action->id,
                    'action_type' => $action->action_type,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];

                if ($action->stop_on_error) {
                    $status = 'failed';
                    $errorMessage = "Action {$action->action_type} failed: {$e->getMessage()}";

                    break;
                }
            }
        }

        $durationMs = $this->elapsedMs($startTime);

        // Update automation run counters (skip in test mode)
        if (! $testMode) {
            $automation->increment('run_count');
            $automation->update(['last_run_at' => now()]);
        }

        return $this->createRun(
            $automation,
            $triggerPayload,
            $status,
            $conditionsResult,
            $actionsExecuted,
            $errorMessage,
            $durationMs,
        );
    }

    private function resolveHandler(string $actionType): ActionHandlerInterface
    {
        $handlerClass = $this->actionHandlers[$actionType] ?? null;

        if ($handlerClass === null) {
            throw new \InvalidArgumentException("Unknown action type: {$actionType}");
        }

        return app($handlerClass);
    }

    /**
     * @param  array<string, mixed>  $triggerPayload
     * @param  array<string, mixed>|null  $conditionsEvaluation
     * @param  array<string, mixed>|null  $actionsExecuted
     */
    private function createRun(
        Automation $automation,
        array $triggerPayload,
        string $status,
        ?array $conditionsEvaluation = null,
        ?array $actionsExecuted = null,
        ?string $errorMessage = null,
        int $durationMs = 0,
    ): AutomationRun {
        /** @var AutomationRun */
        return AutomationRun::create([
            'workspace_id' => $automation->workspace_id,
            'automation_id' => $automation->id,
            'trigger_payload' => $triggerPayload,
            'conditions_evaluation' => $conditionsEvaluation,
            'actions_executed' => $actionsExecuted,
            'status' => $status,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
            'created_at' => now(),
        ]);
    }

    private function elapsedMs(int $startHrtime): int
    {
        return (int) ((hrtime(true) - $startHrtime) / 1_000_000);
    }
}
