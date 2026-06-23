<?php

/**
 * F-FIX-02.5 — Hearing Postponement Chain UX tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #30.
 */

use App\Enums\HearingStatus;
use App\Models\Contact;
use App\Models\Court;
use App\Models\Hearing;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupPostponementUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $matter = Matter::factory()->litigation()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    $court = Court::factory()->create(['workspace_id' => $workspace->id]);

    return [$user, $workspace, $matter, $court];
}

// 1. creating postponement without reason returns 422
it('creating postponement without reason returns 422', function () {
    [$user, $workspace, $matter, $court] = setupPostponementUser();

    $originalHearing = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'status' => HearingStatus::Scheduled,
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/hearings', [
        'matter_id' => $matter->id,
        'hearing_date' => now()->addDays(14)->toIso8601String(),
        'court_id' => $court->id,
        'hearing_type' => 'first_session',
        'postponed_to_hearing_id' => $originalHearing->id,
        // Missing postponement_reason_ar and postponement_initiated_by
    ]);

    $response->assertUnprocessable();
});

// 2. creating with reason + initiated_by succeeds and links
it('creating with reason and initiated_by succeeds and links', function () {
    [$user, $workspace, $matter, $court] = setupPostponementUser();

    $targetHearing = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'status' => HearingStatus::Scheduled,
        'hearing_date' => now()->addDays(30),
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/hearings', [
        'matter_id' => $matter->id,
        'hearing_date' => now()->addDays(7)->toIso8601String(),
        'court_id' => $court->id,
        'hearing_type' => 'first_session',
        'postponed_to_hearing_id' => $targetHearing->id,
        'postponement_reason_ar' => 'تم التأجيل بسبب عدم حضور المدعى عليه',
        'postponement_initiated_by' => 'court',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.postponed_to_hearing_id', $targetHearing->id);
    $response->assertJsonPath('data.postponement_reason_ar', 'تم التأجيل بسبب عدم حضور المدعى عليه');
    $response->assertJsonPath('data.postponement_initiated_by', 'court');
    $response->assertJsonPath('data.is_postponement', true);
});

// 3. chain endpoint returns full chain chronologically
it('chain endpoint returns full chain chronologically', function () {
    [$user, $workspace, $matter, $court] = setupPostponementUser();

    $hearingA = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'hearing_date' => now()->subDays(20),
        'status' => HearingStatus::Postponed,
    ]);

    $hearingB = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'hearing_date' => now()->subDays(10),
        'status' => HearingStatus::Postponed,
    ]);

    $hearingC = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'hearing_date' => now()->addDays(5),
        'status' => HearingStatus::Scheduled,
    ]);

    // A -> B -> C
    $hearingA->update(['postponed_to_hearing_id' => $hearingB->id]);
    $hearingB->update(['postponed_to_hearing_id' => $hearingC->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/hearings/{$hearingB->id}/postponement-chain");

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
    $ids = collect($response->json('data'))->pluck('id')->all();
    expect($ids[0])->toBe($hearingA->id);
    expect($ids[1])->toBe($hearingB->id);
    expect($ids[2])->toBe($hearingC->id);
});

// 4. circular reference (A -> B -> A) is rejected with 422
it('circular reference is rejected with 422', function () {
    [$user, $workspace, $matter, $court] = setupPostponementUser();

    $hearingA = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'hearing_date' => now()->subDays(10),
        'status' => HearingStatus::Postponed,
    ]);

    $hearingB = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'hearing_date' => now()->addDays(10),
        'status' => HearingStatus::Scheduled,
    ]);

    // A -> B
    $hearingA->update(['postponed_to_hearing_id' => $hearingB->id]);

    // Try B -> A (circular)
    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/hearings/{$hearingB->id}", [
        'postponed_to_hearing_id' => $hearingA->id,
        'postponement_reason_ar' => 'محاولة إنشاء مرجع دائري لأغراض الاختبار',
        'postponement_initiated_by' => 'court',
    ]);

    $response->assertUnprocessable();
});

// 5. hearing without postponement has empty chain
it('hearing without postponement has single-item chain', function () {
    [$user, $workspace, $matter, $court] = setupPostponementUser();

    $hearing = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'status' => HearingStatus::Scheduled,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/hearings/{$hearing->id}/postponement-chain");

    $response->assertOk();
    // A hearing with no links is still in its own chain (chain of 1)
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $hearing->id);
});
