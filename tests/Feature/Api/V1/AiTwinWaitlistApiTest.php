<?php

use App\Models\AiTwinWaitlistEntry;

test('can submit email to AI Twin waitlist', function () {
    $response = $this->postJson('/api/v1/ai-twin/waitlist', [
        'email' => 'lawyer@example.com',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['message']);

    $this->assertDatabaseHas('ai_twin_waitlist_entries', [
        'email' => 'lawyer@example.com',
    ]);
});

test('duplicate email submission is idempotent', function () {
    $this->postJson('/api/v1/ai-twin/waitlist', [
        'email' => 'lawyer@example.com',
    ])->assertOk();

    $this->postJson('/api/v1/ai-twin/waitlist', [
        'email' => 'lawyer@example.com',
    ])->assertOk();

    expect(AiTwinWaitlistEntry::where('email', 'lawyer@example.com')->count())->toBe(1);
});

test('invalid email is rejected', function () {
    $this->postJson('/api/v1/ai-twin/waitlist', [
        'email' => 'not-an-email',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('empty email is rejected', function () {
    $this->postJson('/api/v1/ai-twin/waitlist', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('captures locale from request', function () {
    app()->setLocale('ar');

    $this->postJson('/api/v1/ai-twin/waitlist', [
        'email' => 'arabic-user@example.com',
    ])->assertOk();

    $entry = AiTwinWaitlistEntry::where('email', 'arabic-user@example.com')->first();
    expect($entry->locale)->toBe('ar');
});

test('does not require authentication', function () {
    // No actingAs — request is unauthenticated
    $this->postJson('/api/v1/ai-twin/waitlist', [
        'email' => 'guest@example.com',
    ])->assertOk();

    $this->assertDatabaseHas('ai_twin_waitlist_entries', [
        'email' => 'guest@example.com',
        'workspace_id' => null,
    ]);
});
