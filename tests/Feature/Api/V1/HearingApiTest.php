<?php

use App\Models\Contact;
use App\Models\Court;
use App\Models\Hearing;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupHearingUser(string $role = 'owner'): array
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

it('creates a hearing and updates matter next_hearing_date', function () {
    [$user, $workspace, $matter, $court] = setupHearingUser();

    $hearingDate = now()->addDays(10)->startOfDay()->toIso8601String();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/hearings', [
        'matter_id' => $matter->id,
        'hearing_date' => $hearingDate,
        'court_id' => $court->id,
        'hearing_type' => 'first_session',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.hearing_type', 'first_session');
    $response->assertJsonPath('data.status', 'scheduled');

    // Matter's next_hearing_date should be updated
    $matter->refresh();
    expect($matter->next_hearing_date)->not->toBeNull();
});

it('lists hearings filtered by matter', function () {
    [$user, $workspace, $matter, $court] = setupHearingUser();
    $client2 = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $matter2 = Matter::factory()->litigation()->create(['workspace_id' => $workspace->id, 'client_id' => $client2->id]);

    Hearing::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter->id, 'court_id' => $court->id]);
    Hearing::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter2->id, 'court_id' => $court->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/hearings?matter_id={$matter->id}");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('updates hearing status to held', function () {
    [$user, $workspace, $matter, $court] = setupHearingUser();
    $hearing = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/hearings/{$hearing->id}", [
        'status' => 'held',
        'held_at' => now()->toIso8601String(),
        'outcome' => 'Case adjourned for evidence review',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.status', 'held');
    $response->assertJsonPath('data.outcome', 'Case adjourned for evidence review');
});

it('soft-deletes a hearing and updates matter next_hearing_date', function () {
    [$user, $workspace, $matter, $court] = setupHearingUser('owner');
    $hearing = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
        'hearing_date' => now()->addDays(5),
    ]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/hearings/{$hearing->id}");

    $response->assertNoContent();
    expect(Hearing::find($hearing->id))->toBeNull();

    // next_hearing_date should be cleared since no more scheduled hearings
    $matter->refresh();
    expect($matter->next_hearing_date)->toBeNull();
});

it('workspace isolation on hearings', function () {
    [$user, $workspace, $matter, $court] = setupHearingUser();
    $otherWorkspace = Workspace::factory()->create();
    $otherClient = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);
    $otherMatter = Matter::factory()->create(['workspace_id' => $otherWorkspace->id, 'client_id' => $otherClient->id]);
    $otherCourt = Court::factory()->create(['workspace_id' => $otherWorkspace->id]);

    Hearing::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter->id, 'court_id' => $court->id]);
    Hearing::factory()->create(['workspace_id' => $otherWorkspace->id, 'matter_id' => $otherMatter->id, 'court_id' => $otherCourt->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/hearings');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('denies hearing delete to member role', function () {
    [$user, $workspace, $matter, $court] = setupHearingUser('member');
    $hearing = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/hearings/{$hearing->id}");

    $response->assertForbidden();
});
