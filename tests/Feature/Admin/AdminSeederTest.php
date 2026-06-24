<?php

use App\Enums\AdminRole;
use App\Models\AdminUser;

it('seeder creates admin when table is empty', function () {
    expect(AdminUser::count())->toBe(0);

    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\AdminUserSeeder']);

    expect(AdminUser::count())->toBe(1);
    expect(AdminUser::first()->role)->toBe(AdminRole::SuperAdmin);
});

it('seeder is idempotent', function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\AdminUserSeeder']);
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\AdminUserSeeder']);

    expect(AdminUser::count())->toBe(1);
});
