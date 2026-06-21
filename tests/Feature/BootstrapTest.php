<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('redirects root to dashboard', function () {
    $response = $this->get('/');
    $response->assertRedirect(route('dashboard'));
});

it('can connect to the database', function () {
    expect(DB::connection()->getPdo())->not->toBeNull();
});

it('can run and rollback migrations', function () {
    Artisan::call('migrate:status');
    expect(Artisan::output())->toContain('users');
});
