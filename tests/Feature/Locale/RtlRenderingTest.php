<?php

use App\Models\User;

it('renders html dir=rtl and lang=ar for arabic locale', function () {
    $response = $this->get(route('login').'?lang=ar');

    $response->assertOk();
    $response->assertSee('dir="rtl"', false);
    $response->assertSee('lang="ar"', false);
});

it('renders html dir=ltr and lang=en for english locale', function () {
    $response = $this->get(route('login').'?lang=en');

    $response->assertOk();
    $response->assertSee('dir="ltr"', false);
    $response->assertSee('lang="en"', false);
});

it('login page renders correctly in ar locale', function () {
    $response = $this->get(route('login').'?lang=ar');

    $response->assertOk();
    $response->assertSee('تسجيل الدخول باستخدام Google', false);
    $response->assertSee('كود جوب', false);
});

it('login page renders correctly in en locale', function () {
    $response = $this->get(route('login').'?lang=en');

    $response->assertOk();
    $response->assertSee('Sign in with Google', false);
    $response->assertSee('Code Job', false);
});

it('dashboard renders correctly in ar locale', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['locale' => 'ar'])
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('dir="rtl"', false);
});

it('dashboard renders correctly in en locale', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('dir="ltr"', false);
});
