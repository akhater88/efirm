<?php

use App\Enums\AdminRole;
use App\Enums\SubscriptionState;
use App\Jobs\PurgeExpiredWorkspacesJob;
use App\Models\AdminUser;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Workspace;
use App\Services\PdplRetentionService;

beforeEach(function () {
    $this->plan = Plan::factory()->create();
    $this->workspace = Workspace::factory()->create();
    $this->subscription = Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $this->plan->id,
    ]);
    $this->admin = AdminUser::create([
        'name' => 'Admin',
        'email' => 'admin@pdpl.test',
        'password' => bcrypt('password'),
        'role' => AdminRole::SuperAdmin,
    ]);
    $this->service = app(PdplRetentionService::class);
});

test('cancellation sets 90-day retention window', function () {
    $subscription = $this->service->initiateCancellation($this->subscription, $this->admin);

    expect($subscription->state)->toBe(SubscriptionState::Cancelled);
    expect($subscription->data_retention_expires_at)->not->toBeNull();
    expect((int) round(abs($subscription->data_retention_expires_at->diffInDays(now()))))->toBe(PdplRetentionService::RETENTION_DAYS);
});

test('cancellation creates audit log entry', function () {
    $this->service->initiateCancellation($this->subscription, $this->admin);

    $this->assertDatabaseHas('admin_activity_log', [
        'event_type' => 'admin.cancellation.initiated',
    ]);
});

test('cancellation creates subscription event with PDPL reference', function () {
    $this->service->initiateCancellation($this->subscription, $this->admin);

    $this->assertDatabaseHas('subscription_events', [
        'subscription_id' => $this->subscription->id,
        'to_state' => 'retention',
    ]);
});

test('purge fails if retention window has not expired', function () {
    $this->service->initiateCancellation($this->subscription, $this->admin);

    expect(fn () => $this->service->purgeExpiredWorkspace($this->subscription->fresh()))
        ->toThrow(RuntimeException::class, 'retention window has not expired');
});

test('purge soft-deletes workspace data after retention expires', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);

    $this->service->initiateCancellation($this->subscription, $this->admin);

    // Fast-forward past retention
    $this->subscription->fresh()->update([
        'data_retention_expires_at' => now()->subDay(),
    ]);

    $this->service->purgeExpiredWorkspace($this->subscription->fresh());

    expect($this->subscription->fresh()->data_purged)->toBeTrue();
    expect($this->subscription->fresh()->data_purged_at)->not->toBeNull();
    expect(Workspace::find($this->workspace->id))->toBeNull(); // Soft-deleted
    expect(Workspace::withTrashed()->find($this->workspace->id))->not->toBeNull(); // Still in DB
});

test('purge is idempotent', function () {
    $this->service->initiateCancellation($this->subscription, $this->admin);
    $this->subscription->fresh()->update([
        'data_retention_expires_at' => now()->subDay(),
    ]);

    $this->service->purgeExpiredWorkspace($this->subscription->fresh());
    $this->service->purgeExpiredWorkspace($this->subscription->fresh()); // Second call — no error

    expect($this->subscription->fresh()->data_purged)->toBeTrue();
});

test('purge fails on non-cancelled subscription', function () {
    expect(fn () => $this->service->purgeExpiredWorkspace($this->subscription))
        ->toThrow(RuntimeException::class, 'not cancelled');
});

test('getExpiredRetentions returns only expired cancelled subscriptions', function () {
    $this->service->initiateCancellation($this->subscription, $this->admin);
    $this->subscription->fresh()->update([
        'data_retention_expires_at' => now()->subDay(),
    ]);

    $expired = $this->service->getExpiredRetentions();
    expect($expired)->toHaveCount(1);
});

test('consent recording works', function () {
    $this->service->recordConsent($this->workspace);

    $this->workspace->refresh();
    expect($this->workspace->pdpl_consent_obtained)->toBeTrue();
    expect($this->workspace->pdpl_consent_date)->not->toBeNull();
    expect($this->workspace->pdpl_consent_text_version)->toBe(PdplRetentionService::CONSENT_TEXT_VERSION);
});

test('hasValidConsent checks version', function () {
    $this->service->recordConsent($this->workspace);

    expect($this->service->hasValidConsent($this->workspace))->toBeTrue();

    $this->workspace->update(['pdpl_consent_text_version' => 'v0.9-old']);

    expect($this->service->hasValidConsent($this->workspace->fresh()))->toBeFalse();
});

test('purge job processes expired workspaces', function () {
    $this->service->initiateCancellation($this->subscription, $this->admin);
    $this->subscription->fresh()->update([
        'data_retention_expires_at' => now()->subDay(),
    ]);

    app(PurgeExpiredWorkspacesJob::class)->handle($this->service);

    expect($this->subscription->fresh()->data_purged)->toBeTrue();
});

test('admin lang files have PDPL key parity', function () {
    $en = require resource_path('lang/en/admin.php');
    $ar = require resource_path('lang/ar/admin.php');

    expect(array_keys($en['pdpl']))->toBe(array_keys($ar['pdpl']));
    expect(array_keys($en['activity']))->toBe(array_keys($ar['activity']));
});
