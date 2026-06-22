<?php

use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\AutomationActions\CreateTaskAction;
use App\Services\AutomationActions\NotifyUserAction;
use App\Services\AutomationActions\SendEmailAction;
use App\Services\AutomationRunnerService;
use App\Services\ConditionEvaluatorService;

function createAutomationTestUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    return [$user, $workspace];
}

// --- F-11.2: ConditionEvaluatorService — Exhaustive operator tests ---

it('evaluates eq operator correctly', function () {
    $evaluator = new ConditionEvaluatorService;

    expect($evaluator->evaluate(
        ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
        ['status' => 'active']
    ))->toBeTrue();

    expect($evaluator->evaluate(
        ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
        ['status' => 'closed']
    ))->toBeFalse();
});

it('evaluates neq operator correctly', function () {
    $evaluator = new ConditionEvaluatorService;

    expect($evaluator->evaluate(
        ['operator' => 'neq', 'field' => 'status', 'value' => 'closed'],
        ['status' => 'active']
    ))->toBeTrue();

    expect($evaluator->evaluate(
        ['operator' => 'neq', 'field' => 'status', 'value' => 'active'],
        ['status' => 'active']
    ))->toBeFalse();
});

it('evaluates gt operator correctly', function () {
    $evaluator = new ConditionEvaluatorService;

    expect($evaluator->evaluate(
        ['operator' => 'gt', 'field' => 'amount', 'value' => 100],
        ['amount' => 200]
    ))->toBeTrue();

    expect($evaluator->evaluate(
        ['operator' => 'gt', 'field' => 'amount', 'value' => 100],
        ['amount' => 50]
    ))->toBeFalse();
});

it('evaluates lt operator correctly', function () {
    $evaluator = new ConditionEvaluatorService;

    expect($evaluator->evaluate(
        ['operator' => 'lt', 'field' => 'amount', 'value' => 100],
        ['amount' => 50]
    ))->toBeTrue();

    expect($evaluator->evaluate(
        ['operator' => 'lt', 'field' => 'amount', 'value' => 100],
        ['amount' => 200]
    ))->toBeFalse();
});

it('evaluates in operator correctly', function () {
    $evaluator = new ConditionEvaluatorService;

    expect($evaluator->evaluate(
        ['operator' => 'in', 'field' => 'status', 'value' => ['active', 'pending']],
        ['status' => 'active']
    ))->toBeTrue();

    expect($evaluator->evaluate(
        ['operator' => 'in', 'field' => 'status', 'value' => ['active', 'pending']],
        ['status' => 'closed']
    ))->toBeFalse();
});

it('evaluates contains operator correctly', function () {
    $evaluator = new ConditionEvaluatorService;

    // String contains
    expect($evaluator->evaluate(
        ['operator' => 'contains', 'field' => 'title', 'value' => 'contract'],
        ['title' => 'Service contract agreement']
    ))->toBeTrue();

    // Array contains
    expect($evaluator->evaluate(
        ['operator' => 'contains', 'field' => 'tags', 'value' => 'urgent'],
        ['tags' => ['urgent', 'commercial']]
    ))->toBeTrue();

    expect($evaluator->evaluate(
        ['operator' => 'contains', 'field' => 'title', 'value' => 'xyz'],
        ['title' => 'Service contract']
    ))->toBeFalse();
});

it('evaluates is_null operator correctly', function () {
    $evaluator = new ConditionEvaluatorService;

    expect($evaluator->evaluate(
        ['operator' => 'is_null', 'field' => 'closed_at'],
        ['closed_at' => null]
    ))->toBeTrue();

    expect($evaluator->evaluate(
        ['operator' => 'is_null', 'field' => 'closed_at'],
        ['closed_at' => '2026-01-01']
    ))->toBeFalse();
});

it('evaluates and operator with nested conditions', function () {
    $evaluator = new ConditionEvaluatorService;

    expect($evaluator->evaluate(
        [
            'operator' => 'and',
            'conditions' => [
                ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
                ['operator' => 'gt', 'field' => 'amount', 'value' => 100],
            ],
        ],
        ['status' => 'active', 'amount' => 200]
    ))->toBeTrue();

    expect($evaluator->evaluate(
        [
            'operator' => 'and',
            'conditions' => [
                ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
                ['operator' => 'gt', 'field' => 'amount', 'value' => 100],
            ],
        ],
        ['status' => 'active', 'amount' => 50]
    ))->toBeFalse();
});

it('evaluates or operator with nested conditions', function () {
    $evaluator = new ConditionEvaluatorService;

    expect($evaluator->evaluate(
        [
            'operator' => 'or',
            'conditions' => [
                ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
                ['operator' => 'eq', 'field' => 'status', 'value' => 'pending'],
            ],
        ],
        ['status' => 'pending']
    ))->toBeTrue();

    expect($evaluator->evaluate(
        [
            'operator' => 'or',
            'conditions' => [
                ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
                ['operator' => 'eq', 'field' => 'status', 'value' => 'pending'],
            ],
        ],
        ['status' => 'closed']
    ))->toBeFalse();
});

it('evaluates not operator correctly', function () {
    $evaluator = new ConditionEvaluatorService;

    expect($evaluator->evaluate(
        [
            'operator' => 'not',
            'condition' => ['operator' => 'eq', 'field' => 'status', 'value' => 'closed'],
        ],
        ['status' => 'active']
    ))->toBeTrue();

    expect($evaluator->evaluate(
        [
            'operator' => 'not',
            'condition' => ['operator' => 'eq', 'field' => 'status', 'value' => 'closed'],
        ],
        ['status' => 'closed']
    ))->toBeFalse();
});

it('rejects unknown operators', function () {
    $evaluator = new ConditionEvaluatorService;

    $evaluator->evaluate(
        ['operator' => 'eval', 'field' => 'x', 'value' => 'malicious'],
        ['x' => 'test']
    );
})->throws(InvalidArgumentException::class, 'Unknown condition operator: eval');

// --- F-11.2: AutomationRunnerService ---

it('fires automation when trigger matches and conditions are met', function () {
    [$user, $workspace] = createAutomationTestUser();

    $automation = Automation::factory()->withActions(1)->create([
        'workspace_id' => $workspace->id,
        'trigger_event' => 'matter.created',
        'conditions' => ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
    ]);

    $runner = app(AutomationRunnerService::class);
    $run = $runner->run($automation, ['status' => 'active']);

    expect($run->status)->toBe('completed');
    expect($run->actions_executed)->toHaveCount(1);
});

it('skips automation when conditions are not met', function () {
    [$user, $workspace] = createAutomationTestUser();

    $automation = Automation::factory()->withActions(1)->create([
        'workspace_id' => $workspace->id,
        'conditions' => ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
    ]);

    $runner = app(AutomationRunnerService::class);
    $run = $runner->run($automation, ['status' => 'closed']);

    expect($run->status)->toBe('skipped');
});

it('executes actions in sequence and stops on error when stop_on_error is true', function () {
    [$user, $workspace] = createAutomationTestUser();

    $automation = Automation::factory()->create([
        'workspace_id' => $workspace->id,
        'conditions' => [],
    ]);

    // First action: unknown type (will fail)
    $automation->actions()->create([
        'sort_order' => 0,
        'action_type' => 'unknown_action',
        'action_payload' => [],
        'stop_on_error' => true,
    ]);

    // Second action: valid but should not run
    $automation->actions()->create([
        'sort_order' => 1,
        'action_type' => 'notify_user',
        'action_payload' => ['message' => 'test'],
        'stop_on_error' => true,
    ]);

    $runner = app(AutomationRunnerService::class);
    $run = $runner->run($automation, []);

    expect($run->status)->toBe('failed');
    expect($run->actions_executed)->toHaveCount(1);
});

it('prevents loops with depth limit', function () {
    [$user, $workspace] = createAutomationTestUser();

    $automation = Automation::factory()->withActions(1)->create([
        'workspace_id' => $workspace->id,
        'conditions' => [],
    ]);

    $runner = app(AutomationRunnerService::class);
    $run = $runner->run($automation, [], depth: 5);

    expect($run->status)->toBe('skipped');
    expect($run->conditions_evaluation['result'] ?? null)->toBe('loop_prevention');
});

it('finds matching automations for a trigger event', function () {
    [$user, $workspace] = createAutomationTestUser();

    Automation::factory()->create([
        'workspace_id' => $workspace->id,
        'trigger_event' => 'matter.created',
        'is_active' => true,
    ]);
    Automation::factory()->create([
        'workspace_id' => $workspace->id,
        'trigger_event' => 'matter.updated',
        'is_active' => true,
    ]);
    Automation::factory()->create([
        'workspace_id' => $workspace->id,
        'trigger_event' => 'matter.created',
        'is_active' => false, // inactive
    ]);

    $runner = app(AutomationRunnerService::class);
    $matches = $runner->findMatchingAutomations('matter.created', $workspace->id);

    expect($matches)->toHaveCount(1);
});

// --- F-11.2: AutomationRun append-only ---

it('blocks updates on AutomationRun', function () {
    [$user, $workspace] = createAutomationTestUser();

    $run = AutomationRun::factory()->create([
        'workspace_id' => $workspace->id,
    ]);

    $run->update(['status' => 'failed']);
})->throws(RuntimeException::class, 'AutomationRun is append-only. Updates are not allowed.');

it('blocks deletes on AutomationRun', function () {
    [$user, $workspace] = createAutomationTestUser();

    $run = AutomationRun::factory()->create([
        'workspace_id' => $workspace->id,
    ]);

    $run->delete();
})->throws(RuntimeException::class, 'AutomationRun is append-only. Deletes are not allowed.');

// --- F-11.2: Workspace isolation ---

it('scopes automations to current workspace', function () {
    [$user, $workspace] = createAutomationTestUser();
    $otherWorkspace = Workspace::factory()->create();

    Automation::factory()->create(['workspace_id' => $workspace->id]);
    Automation::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/automations');

    $response->assertOk();
    $response->assertJsonCount(1, 'data.data');
});

// --- F-11.2: API CRUD ---

it('creates an automation via API', function () {
    [$user, $workspace] = createAutomationTestUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/automations', [
        'name_ar' => 'أتمتة اختبار',
        'name_en' => 'Test Automation',
        'trigger_event' => 'matter.created',
        'conditions' => ['operator' => 'eq', 'field' => 'status', 'value' => 'active'],
        'actions' => [
            [
                'action_type' => 'create_task',
                'action_payload' => ['title' => 'Auto task'],
            ],
        ],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name_en', 'Test Automation');
    $response->assertJsonCount(1, 'data.actions');
});

it('tests an automation in test mode via API', function () {
    [$user, $workspace] = createAutomationTestUser();

    $automation = Automation::factory()->withActions(1)->create([
        'workspace_id' => $workspace->id,
        'conditions' => [],
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/automations/{$automation->id}/test", [
        'trigger_payload' => ['status' => 'active'],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.status', 'completed');
});

// --- F-11.2: Action handler isolation ---

it('isolates create_task action handler', function () {
    [$user, $workspace] = createAutomationTestUser();

    $matter = Matter::factory()->create(['workspace_id' => $workspace->id]);

    $handler = new CreateTaskAction;
    $result = $handler->execute(
        ['title' => 'Test task', 'due_in_days' => 7, 'taskable_type' => 'matter', 'taskable_id' => $matter->id],
        ['workspace_id' => $workspace->id]
    );

    expect($result)->toHaveKey('task_id');
    $this->assertDatabaseHas('tasks', ['id' => $result['task_id']]);
});

it('isolates send_email action handler as stub', function () {
    $handler = new SendEmailAction;
    $result = $handler->execute(
        ['to' => 'test@example.com', 'subject' => 'Test'],
        ['workspace_id' => 'test-workspace']
    );

    expect($result['stub'])->toBeTrue();
    expect($result['to'])->toBe('test@example.com');
});

it('isolates notify_user action handler as stub', function () {
    $handler = new NotifyUserAction;
    $result = $handler->execute(
        ['user_id' => 'user-123', 'message' => 'Hello'],
        ['workspace_id' => 'test-workspace']
    );

    expect($result['stub'])->toBeTrue();
    expect($result['user_id'])->toBe('user-123');
});
