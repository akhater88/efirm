<?php

use App\Enums\AdminRole;
use App\Models\AdminUser;
use App\Models\User;

it('admin credentials fail on workspace login', function () {
    AdminUser::create([
        'name' => 'Platform Admin',
        'email' => 'platform@test.com',
        'password' => 'SecurePass1234!',
        'role' => AdminRole::SuperAdmin,
        'locale' => 'en',
    ]);

    // Workspace guard should not authenticate admin users
    $result = auth()->guard('web')->attempt([
        'email' => 'platform@test.com',
        'password' => 'SecurePass1234!',
    ]);

    expect($result)->toBeFalse();
    $this->assertGuest('web');
});

it('workspace credentials fail on admin login', function () {
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
