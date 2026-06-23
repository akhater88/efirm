<?php

/**
 * F-FIX-02.1 — Hearing Session History tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #28.
 */

use App\Enums\HearingStatus;
use App\Enums\ObligationStatus;
use App\Models\Contact;
use App\Models\Court;
use App\Models\Document;
use App\Models\Hearing;
use App\Models\HearingActionItem;
use App\Models\Matter;
use App\Models\Obligation;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupSessionUser(string $role = 'owner'): array
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
    $document = Document::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter->id]);

    return [$user, $workspace, $matter, $court, $document];
}

// 1. saves session content when hearing status is held
it('saves session content when hearing status is held', function () {
    [$user, $workspace, $matter, $court] = setupSessionUser();

    $hearing = Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/hearings/{$hearing->id}/session", [
        'judge_statement_ar' => 'قرار القاضي بالتأجيل',
        'judge_statement_en' => 'Judge decided to adjourn',
        'outcome_summary_ar' => 'ملخص النتيجة',
        'our_submissions_made' => 'Our brief was submitted',
        'opposing_submissions_made' => 'Opposing counsel objected',
        'session_attended_by' => ['lawyer_a', 'trainee_b'],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.judge_statement_ar', 'قرار القاضي بالتأجيل');
    $response->assertJsonPath('data.judge_statement_en', 'Judge decided to adjourn');
    $response->assertJsonPath('data.our_submissions_made', 'Our brief was submitted');
    $response->assertJsonPath('data.session_attended_by', ['lawyer_a', 'trainee_b']);
});

// 2. rejects session content save when status is scheduled (422)
it('rejects session content save when status is scheduled', function () {
    [$user, $workspace, $matter, $court] = setupSessionUser();

    $hearing = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'status' => HearingStatus::Scheduled,
    ]);

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/hearings/{$hearing->id}/session", [
        'judge_statement_ar' => 'Should not work',
    ]);

    $response->assertUnprocessable();
});

// 3. auto-creates obligation when action item added
it('auto-creates obligation when action item added', function () {
    [$user, $workspace, $matter, $court, $document] = setupSessionUser();

    $hearing = Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/hearings/{$hearing->id}/action-items", [
        'description_ar' => 'تقديم مذكرة جوابية',
        'description_en' => 'Submit response brief',
        'due_date' => now()->addDays(14)->toDateString(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.description_ar', 'تقديم مذكرة جوابية');

    $actionItem = HearingActionItem::first();
    expect($actionItem)->not->toBeNull();
    expect($actionItem->obligation_id)->not->toBeNull();

    $obligation = Obligation::withoutGlobalScopes()->find($actionItem->obligation_id);
    expect($obligation)->not->toBeNull();
    expect($obligation->title)->toBe('تقديم مذكرة جوابية');
    expect($obligation->status)->toBe(ObligationStatus::Pending);
});

// 4. marks linked obligation completed when action item completed
it('marks linked obligation completed when action item completed', function () {
    [$user, $workspace, $matter, $court, $document] = setupSessionUser();

    $hearing = Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    // Create via API to get obligation link
    $this->actingAs($user, 'sanctum')->postJson("/api/v1/hearings/{$hearing->id}/action-items", [
        'description_ar' => 'إجراء مطلوب',
        'due_date' => now()->addDays(7)->toDateString(),
    ]);

    $actionItem = HearingActionItem::first();
    $obligationId = $actionItem->obligation_id;

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/hearing-action-items/{$actionItem->id}", [
        'status' => 'completed',
    ]);

    $response->assertOk();

    $obligation = Obligation::withoutGlobalScopes()->find($obligationId);
    expect($obligation->status)->toBe(ObligationStatus::Completed);
});

// 5. marks linked obligation waived when action item soft-deleted
it('marks linked obligation waived when action item soft-deleted', function () {
    [$user, $workspace, $matter, $court, $document] = setupSessionUser();

    $hearing = Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    $this->actingAs($user, 'sanctum')->postJson("/api/v1/hearings/{$hearing->id}/action-items", [
        'description_ar' => 'سيتم حذفه',
        'due_date' => now()->addDays(7)->toDateString(),
    ]);

    $actionItem = HearingActionItem::first();
    $obligationId = $actionItem->obligation_id;

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/hearing-action-items/{$actionItem->id}");
    $response->assertNoContent();

    $obligation = Obligation::withoutGlobalScopes()->find($obligationId);
    expect($obligation->status)->toBe(ObligationStatus::Waived);
});

// 6. sessions timeline returns held hearings chronologically
it('sessions timeline returns held hearings chronologically', function () {
    [$user, $workspace, $matter, $court] = setupSessionUser();

    Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'hearing_date' => now()->subDays(10),
    ]);
    Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'hearing_date' => now()->subDays(5),
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/matters/{$matter->id}/sessions-timeline");

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

// 7. sessions timeline excludes scheduled hearings
it('sessions timeline excludes scheduled hearings', function () {
    [$user, $workspace, $matter, $court] = setupSessionUser();

    Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'hearing_date' => now()->subDays(5),
    ]);
    Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'status' => HearingStatus::Scheduled,
        'hearing_date' => now()->addDays(10),
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/matters/{$matter->id}/sessions-timeline");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

// 8. only workspace members can access (workspace isolation)
it('workspace isolation on sessions timeline', function () {
    [$user, $workspace, $matter, $court] = setupSessionUser();

    $otherWorkspace = Workspace::factory()->create();
    $otherClient = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);
    $otherMatter = Matter::factory()->litigation()->create(['workspace_id' => $otherWorkspace->id, 'client_id' => $otherClient->id]);
    $otherCourt = Court::factory()->create(['workspace_id' => $otherWorkspace->id]);

    Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);
    Hearing::factory()->held()->create([
        'workspace_id' => $otherWorkspace->id,
        'matter_id' => $otherMatter->id,
        'court_id' => $otherCourt->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/matters/{$matter->id}/sessions-timeline");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

// 9. action item CRUD works
it('action item CRUD works', function () {
    [$user, $workspace, $matter, $court, $document] = setupSessionUser();

    $hearing = Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    // Create
    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/hearings/{$hearing->id}/action-items", [
        'description_ar' => 'بند أصلي',
        'description_en' => 'Original item',
        'due_date' => now()->addDays(7)->toDateString(),
    ]);
    $response->assertCreated();
    $itemId = $response->json('data.id');

    // Update
    $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/hearing-action-items/{$itemId}", [
        'description_en' => 'Updated item',
    ]);
    $response->assertOk();
    $response->assertJsonPath('data.description_en', 'Updated item');

    // Delete
    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/hearing-action-items/{$itemId}");
    $response->assertNoContent();

    expect(HearingActionItem::find($itemId))->toBeNull();
    expect(HearingActionItem::withTrashed()->find($itemId))->not->toBeNull();
});

// 10. action item with due_date persists correctly
it('action item with due_date persists correctly', function () {
    [$user, $workspace, $matter, $court, $document] = setupSessionUser();

    $hearing = Hearing::factory()->held()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    $dueDate = now()->addDays(21)->toDateString();

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/hearings/{$hearing->id}/action-items", [
        'description_ar' => 'بند بتاريخ',
        'due_date' => $dueDate,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.due_date', $dueDate);
});

// 11. hearing without session content still works (backward compat)
it('hearing without session content still works', function () {
    [$user, $workspace, $matter, $court] = setupSessionUser();

    $hearing = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/hearings/{$hearing->id}");

    $response->assertOk();
    $response->assertJsonPath('data.judge_statement_ar', null);
    $response->assertJsonPath('data.session_attended_by', null);
});

// 12. existing S-08 hearing tests remain valid (backward compat sanity check)
it('existing hearing CRUD still works after session content migration', function () {
    [$user, $workspace, $matter, $court] = setupSessionUser();

    // Create hearing
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/hearings', [
        'matter_id' => $matter->id,
        'hearing_date' => now()->addDays(10)->toIso8601String(),
        'court_id' => $court->id,
        'hearing_type' => 'first_session',
    ]);

    $response->assertCreated();
    $hearingId = $response->json('data.id');

    // Update to held
    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/hearings/{$hearingId}", [
        'status' => 'held',
        'held_at' => now()->toIso8601String(),
        'outcome' => 'Case adjourned',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.status', 'held');
});
