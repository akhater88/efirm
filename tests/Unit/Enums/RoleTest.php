<?php

use App\Enums\Role;

it('has three values: owner, admin, member', function () {
    expect(Role::cases())->toHaveCount(3);
    expect(Role::Owner->value)->toBe('owner');
    expect(Role::Admin->value)->toBe('admin');
    expect(Role::Member->value)->toBe('member');
});

it('returns localized label for each role', function () {
    app()->setLocale('en');

    expect(Role::Owner->label())->toBe('Owner');
    expect(Role::Admin->label())->toBe('Admin');
    expect(Role::Member->label())->toBe('Member');
});

it('owner and admin can access filament', function () {
    expect(Role::Owner->canAccessFilament())->toBeTrue();
    expect(Role::Admin->canAccessFilament())->toBeTrue();
});

it('member cannot access filament', function () {
    expect(Role::Member->canAccessFilament())->toBeFalse();
});

it('only owner is privileged', function () {
    expect(Role::Owner->isPrivileged())->toBeTrue();
    expect(Role::Admin->isPrivileged())->toBeFalse();
    expect(Role::Member->isPrivileged())->toBeFalse();
});
