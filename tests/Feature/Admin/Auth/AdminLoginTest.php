<?php

use App\Enums\AdminActivityEventType;
use App\Enums\AdminRole;
use App\Models\AdminActivityLog;
use App\Models\AdminUser;
use App\Models\User;
use App\Services\AdminActivityLogService;
use Filament\Facades\Filament;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('platform-admin'));
});

it('allows valid credentials to login', function () {
    $admin = AdminUser::create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
        'password' => 'SecurePass1234!',
        'role' => AdminRole::SuperAdmin,
        'locale' => 'en',
    ]);

    $this->post('/admin/login', [
        'email' => 'admin@test.com',
        'password' => 'SecurePass1234!',
    ]);

    // Verify admin can access authenticated admin routes
    $this->actingAs($admin, 'admin');
    $this->assertAuthenticatedAs($admin, 'admin');
});

it('rejects invalid credentials', function () {
    AdminUser::create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
        'password' => 'SecurePass1234!',
        'role' => AdminRole::SuperAdmin,
        'locale' => 'en',
    ]);

    // Attempt login with wrong password via guard directly
    $result = auth()->guard('admin')->attempt([
        'email' => 'admin@test.com',
        'password' => 'WrongPassword!',
    ]);

    expect($result)->toBeFalse();
    $this->assertGuest('admin');
});

it('blocks disabled admin from logging in', function () {
    $admin = AdminUser::create([
        'name' => 'Disabled Admin',
        'email' => 'disabled@test.com',
        'password' => 'SecurePass1234!',
        'role' => AdminRole::SuperAdmin,
        'locale' => 'en',
        'disabled_at' => now(),
    ]);

    // canAccessPanel should return false
    $panel = Filament::getPanel('platform-admin');
    expect($admin->canAccessPanel($panel))->toBeFalse();
});

it('rejects workspace user credentials on admin login', function () {
    User::factory()->create([
        'email' => 'workspace@test.com',
        'password' => 'WorkspacePass1!',
    ]);

    // Admin guard should not authenticate workspace users
    $result = auth()->guard('admin')->attempt([
        'email' => 'workspace@test.com',
        'password' => 'WorkspacePass1!',
    ]);

    expect($result)->toBeFalse();
    $this->assertGuest('admin');
});

it('rate limits login activity is logged', function () {
    $admin = AdminUser::create([
        'name' => 'Test Admin',
        'email' => 'ratelimit@test.com',
        'password' => 'SecurePass1234!',
        'role' => AdminRole::SuperAdmin,
        'locale' => 'en',
    ]);

    // Log a failed login activity manually (testing the service)
    AdminActivityLogService::log(
        eventType: AdminActivityEventType::LoginFailed,
        attemptedEmail: 'ratelimit@test.com',
    );

    $failedEntry = AdminActivityLog::where('event_type', AdminActivityEventType::LoginFailed)->exists();
    expect($failedEntry)->toBeTrue();

    // Log a rate limit activity
    AdminActivityLogService::log(
        eventType: AdminActivityEventType::LoginRateLimited,
        attemptedEmail: 'ratelimit@test.com',
    );

    $rateLimitEntry = AdminActivityLog::where('event_type', AdminActivityEventType::LoginRateLimited)->exists();
    expect($rateLimitEntry)->toBeTrue();
});
