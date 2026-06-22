<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Str;

function createAuditAuthUser(string $role = 'owner'): array
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

it('audit log blocks updates — append-only enforcement', function () {
    [$user, $workspace] = createAuditAuthUser();

    $log = AuditLog::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'action' => 'created',
    ]);

    expect(fn () => $log->update(['action' => 'deleted']))
        ->toThrow(RuntimeException::class, 'append-only');
});

it('audit log blocks deletes — append-only enforcement', function () {
    [$user, $workspace] = createAuditAuthUser();

    $log = AuditLog::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'action' => 'created',
    ]);

    expect(fn () => $log->delete())
        ->toThrow(RuntimeException::class, 'append-only');
});

it('creates an audit log record', function () {
    [$user, $workspace] = createAuditAuthUser();

    $log = AuditLog::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'action' => 'created',
        'auditable_type' => 'matter',
        'auditable_id' => (string) Str::ulid(),
        'changes' => ['status' => ['old' => 'draft', 'new' => 'active']],
        'ip_address' => '192.168.1.1',
    ]);

    expect($log->exists)->toBeTrue();
    expect($log->action)->toBe('created');
    expect($log->changes)->toBe(['status' => ['old' => 'draft', 'new' => 'active']]);
});

it('lists audit logs for owner', function () {
    [$user, $workspace] = createAuditAuthUser('owner');

    AuditLog::factory()->count(3)->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/audit-logs');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

it('lists audit logs for admin', function () {
    [$user, $workspace] = createAuditAuthUser('admin');

    AuditLog::factory()->count(2)->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/audit-logs');

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

it('denies audit log access to member role', function () {
    [$user, $workspace] = createAuditAuthUser('member');

    AuditLog::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/audit-logs');

    $response->assertForbidden();
});

it('enforces workspace isolation for audit logs', function () {
    [$user, $workspace] = createAuditAuthUser('owner');
    $otherWorkspace = Workspace::factory()->create();

    AuditLog::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);
    AuditLog::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/audit-logs');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('shows a single audit log entry', function () {
    [$user, $workspace] = createAuditAuthUser('owner');

    $log = AuditLog::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'action' => 'exported',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/audit-logs/{$log->id}");

    $response->assertOk();
    $response->assertJsonPath('data.action', 'exported');
});
