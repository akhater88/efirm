<?php

/**
 * F-FIX-01.5 — PDPL consent field tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decision #8 and Conversation 2, Decision #21.
 *
 * NOTE: No blocking middleware is enforced yet — fields are stored but not gated.
 */

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('workspace without consent can still function normally', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    // Workspace should have consent fields defaulting to false/null
    expect($workspace->pdpl_consent_obtained)->toBeFalse()
        ->and($workspace->pdpl_consent_date)->toBeNull()
        ->and($workspace->pdpl_consent_text_version)->toBeNull();

    // Workspace is still accessible — no blocking middleware
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/contacts');
    $response->assertOk();
});

it('persists PDPL consent fields on workspace', function () {
    $workspace = Workspace::factory()->create();

    $workspace->update([
        'pdpl_consent_obtained' => true,
        'pdpl_consent_date' => now(),
        'pdpl_consent_text_version' => '1.0.0',
    ]);

    $workspace->refresh();

    expect($workspace->pdpl_consent_obtained)->toBeTrue()
        ->and($workspace->pdpl_consent_text_version)->toBe('1.0.0');
});

it('records consent date when consent is obtained', function () {
    $workspace = Workspace::factory()->create();
    $consentDate = now();

    $workspace->update([
        'pdpl_consent_obtained' => true,
        'pdpl_consent_date' => $consentDate,
        'pdpl_consent_text_version' => '1.0.0',
    ]);

    $workspace->refresh();

    expect($workspace->pdpl_consent_date)->not->toBeNull()
        ->and($workspace->pdpl_consent_date->toDateString())->toBe($consentDate->toDateString());
});
