<?php

/**
 * F-FIX-01.1 — Litigation enum extension tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #1 and #2.
 */

use App\Enums\HearingType;
use App\Enums\LitigationStatus;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupLitEnumUser(): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    return [$user, $workspace, $client];
}

it('creates a matter with fee_payment_and_registration status', function () {
    [$user, $workspace, $client] = setupLitEnumUser();

    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'litigation_status' => LitigationStatus::FeePaymentAndRegistration,
    ]);

    expect($matter->fresh()->litigation_status)->toBe(LitigationStatus::FeePaymentAndRegistration);
});

it('creates a matter with notification_pending status', function () {
    [$user, $workspace, $client] = setupLitEnumUser();

    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'litigation_status' => LitigationStatus::NotificationPending,
    ]);

    expect($matter->fresh()->litigation_status)->toBe(LitigationStatus::NotificationPending);
});

it('creates a matter with referred_to_expert status', function () {
    [$user, $workspace, $client] = setupLitEnumUser();

    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'litigation_status' => LitigationStatus::ReferredToExpert,
    ]);

    expect($matter->fresh()->litigation_status)->toBe(LitigationStatus::ReferredToExpert);
});

it('existing litigation status values still work after extension', function () {
    [$user, $workspace, $client] = setupLitEnumUser();

    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'litigation_status' => LitigationStatus::Filed,
    ]);

    expect($matter->fresh()->litigation_status)->toBe(LitigationStatus::Filed);
});

it('new litigation and hearing enum labels render in AR', function () {
    app()->setLocale('ar');

    expect(LitigationStatus::FeePaymentAndRegistration->label())->toBe('قيد الدعوى ودفع الرسوم')
        ->and(LitigationStatus::NotificationPending->label())->toBe('بانتظار التبليغ')
        ->and(LitigationStatus::ReferredToExpert->label())->toBe('الإحالة للخبرة')
        ->and(HearingType::PlaintiffEvidence->label())->toBe('بينات المدعي')
        ->and(HearingType::DefendantEvidence->label())->toBe('بينات المدعى عليه')
        ->and(HearingType::NotificationSession->label())->toBe('جلسة تبليغ');
});
