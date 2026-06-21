<?php

use App\Models\User;

it('defaults to ar locale when no override is present', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    expect(app()->getLocale())->toBe('ar');
});

it('sets locale from query parameter ?lang=en', function () {
    $response = $this->get(route('login').'?lang=en');

    $response->assertOk();
    expect(app()->getLocale())->toBe('en');
});

it('sets locale from query parameter ?lang=ar', function () {
    $response = $this->get(route('login').'?lang=ar');

    $response->assertOk();
    expect(app()->getLocale())->toBe('ar');
});

it('rejects invalid query parameter locale', function () {
    $response = $this->get(route('login').'?lang=fr');

    $response->assertOk();
    // Falls through to default
    expect(app()->getLocale())->toBe('ar');
});

it('persists query parameter locale to session', function () {
    $this->get(route('login').'?lang=en');

    expect(session('locale'))->toBe('en');
});

it('reads locale from session when no query parameter', function () {
    $this->withSession(['locale' => 'en'])->get(route('login'));

    expect(app()->getLocale())->toBe('en');
});

it('reads locale from authenticated user preferred_locale', function () {
    $user = User::factory()->create(['preferred_locale' => 'en']);

    $this->actingAs($user)->get(route('dashboard'));

    expect(app()->getLocale())->toBe('en');
});

it('follows resolution order: query > session > user > default', function () {
    $user = User::factory()->create(['preferred_locale' => 'en']);

    // Query param wins over user preference
    $this->actingAs($user)
        ->withSession(['locale' => 'ar'])
        ->get(route('dashboard').'?lang=en');

    expect(app()->getLocale())->toBe('en');
});

it('sets app locale so __() returns correct translations', function () {
    $this->get(route('login').'?lang=en');

    expect(__('common.welcome'))->toBe('Welcome');

    $this->get(route('login').'?lang=ar');

    expect(__('common.welcome'))->toBe('مرحباً');
});
