<?php

use App\Models\User;

it('switches locale to en via POST /locale/switch', function () {
    $response = $this->post(route('locale.switch'), ['locale' => 'en']);

    $response->assertRedirect();
    expect(session('locale'))->toBe('en');
});

it('switches locale to ar via POST /locale/switch', function () {
    $response = $this->post(route('locale.switch'), ['locale' => 'ar']);

    $response->assertRedirect();
    expect(session('locale'))->toBe('ar');
});

it('rejects invalid locale value', function () {
    $response = $this->post(route('locale.switch'), ['locale' => 'fr']);

    $response->assertSessionHasErrors('locale');
});

it('persists locale to user preferred_locale when authenticated', function () {
    $user = User::factory()->create(['preferred_locale' => 'ar']);

    $this->actingAs($user)->post(route('locale.switch'), ['locale' => 'en']);

    $user->refresh();
    expect($user->preferred_locale)->toBe('en');
});

it('persists locale to session when guest', function () {
    $this->post(route('locale.switch'), ['locale' => 'en']);

    expect(session('locale'))->toBe('en');
});

it('redirects back after switching', function () {
    $response = $this->from(route('login'))->post(route('locale.switch'), ['locale' => 'en']);

    $response->assertRedirect(route('login'));
});

it('locale persists across subsequent requests', function () {
    $this->post(route('locale.switch'), ['locale' => 'en']);

    $this->get(route('login'));

    expect(app()->getLocale())->toBe('en');
});
