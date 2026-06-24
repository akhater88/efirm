<?php

use App\Enums\AdminActivityEventType;
use App\Enums\AdminRole;
use App\Models\AdminActivityLog;
use App\Models\AdminUser;
use App\Services\AdminActivityLogService;

it('activity log service creates log entry', function () {
    $admin = AdminUser::create([
        'name' => 'Log Test Admin',
        'email' => 'logtest@test.com',
        'password' => 'SecurePass1234!',
        'role' => AdminRole::SuperAdmin,
        'locale' => 'en',
    ]);

    AdminActivityLogService::log(
        eventType: AdminActivityEventType::LoginSuccess,
        admin: $admin,
    );

    $logExists = AdminActivityLog::where('event_type', AdminActivityEventType::LoginSuccess)
        ->where('admin_user_id', $admin->id)
        ->exists();

    expect($logExists)->toBeTrue();
});

it('activity log blocks update and delete (append-only)', function () {
    $admin = AdminUser::create([
        'name' => 'Append Test',
        'email' => 'append@test.com',
        'password' => 'SecurePass1234!',
        'role' => AdminRole::SuperAdmin,
        'locale' => 'en',
    ]);

    AdminActivityLogService::log(
        eventType: AdminActivityEventType::LoginSuccess,
        admin: $admin,
    );

    $log = AdminActivityLog::latest('id')->first();

    expect(fn () => $log->update(['ip_address' => '1.2.3.4']))
        ->toThrow(LogicException::class, 'cannot be updated');

    expect(fn () => $log->delete())
        ->toThrow(LogicException::class, 'cannot be deleted');
});
