<?php

use App\Enums\AdminRole;
use App\Models\AdminUser;
use App\Models\Plan;

beforeEach(function () {
    $this->superAdmin = AdminUser::create([
        'name' => 'Super Admin',
        'email' => 'super@test.com',
        'password' => bcrypt('password'),
        'role' => AdminRole::SuperAdmin,
    ]);
});

test('plan resource is accessible by super_admin', function () {
    $this->actingAs($this->superAdmin, 'admin')
        ->get('/admin/plans')
        ->assertOk();
});

test('plan resource is accessible by finance role', function () {
    $financeAdmin = AdminUser::create([
        'name' => 'Finance Admin',
        'email' => 'finance@test.com',
        'password' => bcrypt('password'),
        'role' => AdminRole::Finance,
    ]);

    $this->actingAs($financeAdmin, 'admin')
        ->get('/admin/plans')
        ->assertOk();
});

test('plan resource is not accessible by read_only role', function () {
    $readOnly = AdminUser::create([
        'name' => 'Read Only',
        'email' => 'readonly@test.com',
        'password' => bcrypt('password'),
        'role' => AdminRole::ReadOnly,
    ]);

    $this->actingAs($readOnly, 'admin')
        ->get('/admin/plans')
        ->assertForbidden();
});

test('plan seeder creates 3 default plans', function () {
    $this->artisan('db:seed', ['--class' => 'Database\Seeders\PlanSeeder']);

    expect(Plan::count())->toBe(3);
    expect(Plan::where('slug', 'starter')->exists())->toBeTrue();
    expect(Plan::where('slug', 'pro')->exists())->toBeTrue();
    expect(Plan::where('slug', 'enterprise')->exists())->toBeTrue();
});

test('plan seeder is idempotent', function () {
    $this->artisan('db:seed', ['--class' => 'Database\Seeders\PlanSeeder']);
    $this->artisan('db:seed', ['--class' => 'Database\Seeders\PlanSeeder']);

    expect(Plan::count())->toBe(3);
});

test('starter plan has correct pricing', function () {
    $this->artisan('db:seed', ['--class' => 'Database\Seeders\PlanSeeder']);

    $starter = Plan::where('slug', 'starter')->first();
    expect($starter->price_per_seat_usd)->toBe('20.00');
    expect($starter->max_seats)->toBe(3);
    expect($starter->max_matters)->toBe(50);
});

test('pro plan has correct pricing', function () {
    $this->artisan('db:seed', ['--class' => 'Database\Seeders\PlanSeeder']);

    $pro = Plan::where('slug', 'pro')->first();
    expect($pro->price_per_seat_usd)->toBe('25.00');
    expect($pro->max_seats)->toBe(10);
    expect($pro->features['ai_operations'])->toBeTrue();
});

test('enterprise plan has unlimited caps', function () {
    $this->artisan('db:seed', ['--class' => 'Database\Seeders\PlanSeeder']);

    $enterprise = Plan::where('slug', 'enterprise')->first();
    expect($enterprise->price_per_seat_usd)->toBe('30.00');
    expect($enterprise->max_seats)->toBeNull();
    expect($enterprise->max_matters)->toBeNull();
    expect($enterprise->max_contacts)->toBeNull();
    expect($enterprise->max_storage_mb)->toBeNull();
});

test('plan model has localized name', function () {
    $plan = Plan::create([
        'slug' => 'test',
        'name' => 'Test Plan',
        'name_ar' => 'خطة تجريبية',
        'price_per_seat_usd' => 10.00,
    ]);

    app()->setLocale('en');
    expect($plan->localizedName())->toBe('Test Plan');

    app()->setLocale('ar');
    expect($plan->localizedName())->toBe('خطة تجريبية');
});

test('plan model active scope filters correctly', function () {
    Plan::create(['slug' => 'active-plan', 'name' => 'Active', 'name_ar' => 'نشط', 'price_per_seat_usd' => 10, 'is_active' => true]);
    Plan::create(['slug' => 'inactive-plan', 'name' => 'Inactive', 'name_ar' => 'غير نشط', 'price_per_seat_usd' => 10, 'is_active' => false]);

    expect(Plan::active()->count())->toBe(1);
    expect(Plan::active()->first()->slug)->toBe('active-plan');
});

test('admin lang files have key parity for plans', function () {
    $en = require resource_path('lang/en/admin.php');
    $ar = require resource_path('lang/ar/admin.php');

    expect(array_keys($en))->toBe(array_keys($ar));
    expect(array_keys($en['plans']))->toBe(array_keys($ar['plans']));
    expect(array_keys($en['nav']))->toBe(array_keys($ar['nav']));
    expect(array_keys($en['activity']))->toBe(array_keys($ar['activity']));
});
