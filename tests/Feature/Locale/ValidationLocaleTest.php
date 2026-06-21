<?php

it('returns validation errors in arabic when locale is ar', function () {
    $response = $this->withSession(['locale' => 'ar'])
        ->post(route('locale.switch'), ['locale' => '']);

    $response->assertSessionHasErrors('locale');
});

it('returns validation errors in english when locale is en', function () {
    $response = $this->withSession(['locale' => 'en'])
        ->post(route('locale.switch'), ['locale' => '']);

    $response->assertSessionHasErrors('locale');
});

it('locale switch endpoint validates locale field is required', function () {
    $response = $this->post(route('locale.switch'), []);

    $response->assertSessionHasErrors('locale');
});

it('locale switch endpoint validates locale field is in:ar,en', function () {
    $response = $this->post(route('locale.switch'), ['locale' => 'invalid']);

    $response->assertSessionHasErrors('locale');
});
