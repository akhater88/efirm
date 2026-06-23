<?php

/**
 * F-FIX-02.2 — Court Review Trainee Dispatch tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #29.
 */

use App\Models\Contact;
use App\Models\CourtReview;
use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupDispatchUser(string $role = 'owner'): array
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

    return [$user, $workspace, $matter];
}

// 1. dispatch sets dispatched_to and dispatched_at
it('dispatch sets dispatched_to and dispatched_at', function () {
    [$user, $workspace, $matter] = setupDispatchUser();
    $trainee = User::factory()->create();
    WorkspaceMember::factory()->member()->create([
        'user_id' => $trainee->id,
        'workspace_id' => $workspace->id,
    ]);

    $review = CourtReview::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/court-reviews/{$review->id}/dispatch", [
        'dispatched_to_user_id' => $trainee->id,
        'location_in_courthouse_ar' => 'الطابق الثاني - ديوان المحكمة',
        'location_in_courthouse_en' => '2nd Floor - Court Registry',
        'expected_outcome_ar' => 'الحصول على نسخة من القرار',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.dispatched_to_user_id', $trainee->id);
    expect($response->json('data.dispatched_at'))->not->toBeNull();
    $response->assertJsonPath('data.location_in_courthouse_ar', 'الطابق الثاني - ديوان المحكمة');
});

// 2. dispatched-to-me endpoint returns only my dispatches
it('dispatched-to-me endpoint returns only my dispatches', function () {
    [$owner, $workspace, $matter] = setupDispatchUser();
    $trainee = User::factory()->create();
    WorkspaceMember::factory()->member()->create([
        'user_id' => $trainee->id,
        'workspace_id' => $workspace->id,
    ]);
    $trainee->switchWorkspace($workspace);

    $otherUser = User::factory()->create();
    WorkspaceMember::factory()->member()->create([
        'user_id' => $otherUser->id,
        'workspace_id' => $workspace->id,
    ]);

    CourtReview::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'dispatched_to_user_id' => $trainee->id,
        'dispatched_at' => now(),
    ]);
    CourtReview::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'dispatched_to_user_id' => $otherUser->id,
        'dispatched_at' => now(),
    ]);

    $response = $this->actingAs($trainee, 'sanctum')->getJson('/api/v1/court-reviews/dispatched-to-me');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.dispatched_to_user_id', $trainee->id);
});

// 3. complete sets completed_by and completion_notes
it('complete sets completed_by and completion_notes', function () {
    [$user, $workspace, $matter] = setupDispatchUser();

    $review = CourtReview::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'dispatched_to_user_id' => $user->id,
        'dispatched_at' => now(),
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/court-reviews/{$review->id}/complete", [
        'completion_notes' => 'Collected the decision document from registry',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.completed_by_user_id', $user->id);
    $response->assertJsonPath('data.completion_notes', 'Collected the decision document from registry');
});

// 4. complete with evidence_document_id links correctly
it('complete with evidence_document_id links correctly', function () {
    [$user, $workspace, $matter] = setupDispatchUser();
    $document = Document::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
    ]);

    $review = CourtReview::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'dispatched_to_user_id' => $user->id,
        'dispatched_at' => now(),
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/court-reviews/{$review->id}/complete", [
        'completion_notes' => 'Document collected',
        'evidence_document_id' => $document->id,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.evidence_document_id', $document->id);
});

// 5. overdue dispatch detected after 7 days
it('overdue dispatch detected after 7 days', function () {
    [$user, $workspace, $matter] = setupDispatchUser();

    $review = CourtReview::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'dispatched_to_user_id' => $user->id,
        'dispatched_at' => now()->subDays(8),
    ]);

    expect($review->isOverdueDispatch())->toBeTrue();

    // Completed dispatch should not be overdue
    $review->update(['completed_by_user_id' => $user->id]);
    $review->refresh();
    expect($review->isOverdueDispatch())->toBeFalse();
});

// 6. workspace isolation on dispatched-to-me
it('workspace isolation on dispatched-to-me', function () {
    [$user, $workspace, $matter] = setupDispatchUser();

    $otherWorkspace = Workspace::factory()->create();
    $otherClient = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);
    $otherMatter = Matter::factory()->litigation()->create(['workspace_id' => $otherWorkspace->id, 'client_id' => $otherClient->id]);

    CourtReview::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'dispatched_to_user_id' => $user->id,
        'dispatched_at' => now(),
    ]);
    CourtReview::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'matter_id' => $otherMatter->id,
        'dispatched_to_user_id' => $user->id,
        'dispatched_at' => now(),
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/court-reviews/dispatched-to-me');

    $response->assertOk();
    // Should only see dispatches in current workspace
    $response->assertJsonCount(1, 'data');
});

// 7. non-dispatched review returns empty for dispatched-to-me
it('non-dispatched review returns empty for dispatched-to-me', function () {
    [$user, $workspace, $matter] = setupDispatchUser();

    CourtReview::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/court-reviews/dispatched-to-me');

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

// 8. existing court review tests still pass (backward compat)
it('existing court review CRUD still works after dispatch migration', function () {
    [$user, $workspace, $matter] = setupDispatchUser();

    // Create
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/court-reviews', [
        'matter_id' => $matter->id,
        'decision_date' => '2026-06-20',
        'decision_type' => 'final_judgment',
        'outcome' => 'favourable',
        'summary_ar' => 'حكم لصالح الموكل',
        'appealable' => false,
    ]);

    $response->assertCreated();
    $reviewId = $response->json('data.id');

    // Update
    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/court-reviews/{$reviewId}", [
        'appeal_filed' => true,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.appeal_filed', true);
    // Dispatch fields should be null by default
    $response->assertJsonPath('data.dispatched_to_user_id', null);
});
