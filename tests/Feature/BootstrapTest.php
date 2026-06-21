<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('returns 200 on the welcome page', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

it('can connect to the database', function () {
    expect(DB::connection()->getPdo())->not->toBeNull();
});

it('can run and rollback migrations', function () {
    Artisan::call('migrate:status');
    expect(Artisan::output())->toContain('users');
});
