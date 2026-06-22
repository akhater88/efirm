<?php

use App\Models\Contact;
use App\Models\CourtReview;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupCourtReviewUser(string $role = 'owner'): array
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

it('creates a court review', function () {
    [$user, $workspace, $matter] = setupCourtReviewUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/court-reviews', [
        'matter_id' => $matter->id,
        'decision_date' => '2026-06-15',
        'decision_type' => 'final_judgment',
        'outcome' => 'favourable',
        'summary_ar' => 'حكم لصالح الموكل',
        'summary_en' => 'Judgment in favour of client',
        'appealable' => true,
        'appeal_deadline_date' => '2026-07-15',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.decision_type', 'final_judgment');
    $response->assertJsonPath('data.outcome', 'favourable');
    $response->assertJsonPath('data.appealable', true);
});

it('lists court reviews filtered by matter', function () {
    [$user, $workspace, $matter] = setupCourtReviewUser();

    CourtReview::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter->id]);
    CourtReview::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/court-reviews?matter_id={$matter->id}");

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

it('updates a court review', function () {
    [$user, $workspace, $matter] = setupCourtReviewUser();
    $review = CourtReview::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter->id]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/court-reviews/{$review->id}", [
        'appeal_filed' => true,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.appeal_filed', true);
});

it('soft-deletes a court review', function () {
    [$user, $workspace, $matter] = setupCourtReviewUser('owner');
    $review = CourtReview::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/court-reviews/{$review->id}");

    $response->assertNoContent();
    expect(CourtReview::find($review->id))->toBeNull();
    expect(CourtReview::withTrashed()->find($review->id))->not->toBeNull();
});

it('workspace isolation on court reviews', function () {
    [$user, $workspace, $matter] = setupCourtReviewUser();
    $otherWorkspace = Workspace::factory()->create();
    $otherClient = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);
    $otherMatter = Matter::factory()->create(['workspace_id' => $otherWorkspace->id, 'client_id' => $otherClient->id]);

    CourtReview::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter->id]);
    CourtReview::factory()->create(['workspace_id' => $otherWorkspace->id, 'matter_id' => $otherMatter->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/court-reviews');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});
