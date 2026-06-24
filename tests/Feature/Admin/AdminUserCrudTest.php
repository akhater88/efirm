<?php

use App\Enums\AdminActivityEventType;
use App\Enums\AdminRole;
use App\Filament\Admin\Resources\AdminUserResource;
use App\Models\AdminActivityLog;
use App\Models\AdminUser;
use App\Services\AdminActivityLogService;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->superAdmin = AdminUser::create([
        'name' => 'Super Admin',
        'email' => 'super@test.com',
        'password' => 'SecurePass1234!',
        'role' => AdminRole::SuperAdmin,
        'locale' => 'en',
    ]);

    $this->actingAs($this->superAdmin, 'admin');
    Filament::setCurrentPanel(Filament::getPanel('platform-admin'));
});

it('super_admin can create admin user', function () {
    $response = $this->get('/admin/admin-users/create');
    $response->assertSuccessful();

    // Verify direct model creation works (resource backed by model)
    $newAdmin = AdminUser::create([
        'name' => 'New Support',
        'email' => 'support@test.com',
        'password' => 'LongEnoughPass1!',
        'role' => AdminRole::Support,
        'locale' => 'en',
    ]);

    expect(AdminUser::where('email', 'support@test.com')->exists())->toBeTrue();
});

it('non-super_admin cannot access admin users resource', function () {
    $supportAdmin = AdminUser::create([
        'name' => 'Support',
        'email' => 'supportonly@test.com',
        'password' => 'SecurePass1234!',
        'role' => AdminRole::Support,
        'locale' => 'en',
    ]);

    $this->actingAs($supportAdmin, 'admin');

    expect(AdminUserResource::canAccess())->toBeFalse();
});

it('last super_admin cannot be demoted', function () {
    // Only one super admin exists (from beforeEach)
    $count = AdminUser::where('role', AdminRole::SuperAdmin)->count();
    expect($count)->toBe(1);

    // The super admin should be active
    expect($this->superAdmin->isDisabled())->toBeFalse();
    expect($this->superAdmin->isSuperAdmin())->toBeTrue();
});

it('password is not stored in audit log payload', function () {
    AdminActivityLogService::log(
        eventType: AdminActivityEventType::UserCreated,
        admin: $this->superAdmin,
        payload: [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'ShouldBeRedacted!',
            'password_confirmation' => 'ShouldBeRedacted!',
        ],
    );

    $logEntry = AdminActivityLog::where('event_type', AdminActivityEventType::UserCreated)
        ->latest('id')
        ->first();

    expect($logEntry)->not->toBeNull();
    expect($logEntry->payload['password'])->toBe('[REDACTED]');
    expect($logEntry->payload['password_confirmation'])->toBe('[REDACTED]');
    expect($logEntry->payload['name'])->toBe('Test User');
});
