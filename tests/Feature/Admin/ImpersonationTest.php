<?php

use App\Enums\AdminRole;
use App\Enums\Role;
use App\Models\AdminImpersonationSession;
use App\Models\AdminUser;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\AdminImpersonationService;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

beforeEach(function () {
    $this->superAdmin = AdminUser::create([
        'name' => 'Super Admin',
        'email' => 'super@impersonation.test',
        'password' => bcrypt('password'),
        'role' => AdminRole::SuperAdmin,
    ]);

    $this->workspace = Workspace::factory()->create();
    $this->targetUser = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->targetUser->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
});

test('super_admin can start impersonation session', function () {
    $this->actingAs($this->superAdmin, 'admin');

    $service = app(AdminImpersonationService::class);
    $session = $service->start($this->superAdmin, $this->targetUser, $this->workspace, 'Support ticket #123');

    expect($session)->toBeInstanceOf(AdminImpersonationSession::class);
    expect($session->admin_user_id)->toBe($this->superAdmin->id);
    expect($session->impersonated_user_id)->toBe($this->targetUser->id);
    expect($session->workspace_id)->toBe($this->workspace->id);
    expect($session->purpose)->toBe('Support ticket #123');
    expect($session->isActive())->toBeTrue();
});

test('impersonation enforces single-active-session invariant', function () {
    $this->actingAs($this->superAdmin, 'admin');

    $service = app(AdminImpersonationService::class);
    $service->start($this->superAdmin, $this->targetUser, $this->workspace, 'First session');

    expect(fn () => $service->start($this->superAdmin, $this->targetUser, $this->workspace, 'Second session'))
        ->toThrow(ConflictHttpException::class);
});

test('impersonation can be stopped', function () {
    $this->actingAs($this->superAdmin, 'admin');

    $service = app(AdminImpersonationService::class);
    $session = $service->start($this->superAdmin, $this->targetUser, $this->workspace, 'Quick check');

    $service->stop('explicit');

    $session->refresh();
    expect($session->isActive())->toBeFalse();
    expect($session->termination_reason)->toBe('explicit');
    expect($session->ended_at)->not->toBeNull();
});

test('impersonation session is append-only (cannot delete)', function () {
    $session = AdminImpersonationSession::create([
        'admin_user_id' => $this->superAdmin->id,
        'impersonated_user_id' => $this->targetUser->id,
        'workspace_id' => $this->workspace->id,
        'purpose' => 'Test',
        'ip_address' => '127.0.0.1',
        'started_at' => now(),
    ]);

    expect(fn () => $session->delete())
        ->toThrow(RuntimeException::class);
});

test('impersonation creates audit log entry', function () {
    $this->actingAs($this->superAdmin, 'admin');

    $service = app(AdminImpersonationService::class);
    $service->start($this->superAdmin, $this->targetUser, $this->workspace, 'Audit test');

    $this->assertDatabaseHas('admin_activity_log', [
        'event_type' => 'admin.impersonation.started',
    ]);
});

test('impersonation stop route works', function () {
    // Set up impersonation session data in session
    $session = AdminImpersonationSession::create([
        'admin_user_id' => $this->superAdmin->id,
        'impersonated_user_id' => $this->targetUser->id,
        'workspace_id' => $this->workspace->id,
        'purpose' => 'Route test',
        'ip_address' => '127.0.0.1',
        'started_at' => now(),
    ]);

    $this->actingAs($this->targetUser)
        ->withSession([
            'admin_impersonation_session_id' => $session->id,
            'admin_impersonation_admin_id' => $this->superAdmin->id,
            'admin_impersonation_started_at' => now()->toIso8601String(),
        ])
        ->post('/impersonation/stop')
        ->assertRedirect('/admin');
});

test('admin lang files have parity for impersonation and workspace keys', function () {
    $en = require resource_path('lang/en/admin.php');
    $ar = require resource_path('lang/ar/admin.php');

    expect(array_keys($en))->toBe(array_keys($ar));
    expect(array_keys($en['workspaces']))->toBe(array_keys($ar['workspaces']));
    expect(array_keys($en['impersonation']))->toBe(array_keys($ar['impersonation']));
});
