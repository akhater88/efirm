<?php

use App\Models\Court;
use App\Models\Judge;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupCourtUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    return [$user, $workspace];
}

// --- Court CRUD ---

it('lists courts in current workspace only', function () {
    [$user, $workspace] = setupCourtUser();
    $otherWorkspace = Workspace::factory()->create();

    Court::factory()->create(['workspace_id' => $workspace->id]);
    Court::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/courts');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('creates a court with valid data', function () {
    [$user, $workspace] = setupCourtUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/courts', [
        'name_ar' => 'محكمة بداية عمان',
        'name_en' => 'Amman First Instance Court',
        'court_type' => 'first_instance',
        'jurisdiction_country' => 'JO',
        'city' => 'Amman',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name_ar', 'محكمة بداية عمان');
    $response->assertJsonPath('data.court_type', 'first_instance');
});

it('shows a court with judges', function () {
    [$user, $workspace] = setupCourtUser();
    $court = Court::factory()->create(['workspace_id' => $workspace->id]);
    Judge::factory()->create(['workspace_id' => $workspace->id, 'court_id' => $court->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/courts/{$court->id}");

    $response->assertOk();
    $response->assertJsonCount(1, 'data.judges');
});

it('updates a court', function () {
    [$user, $workspace] = setupCourtUser();
    $court = Court::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/courts/{$court->id}", [
        'name_en' => 'Updated Court Name',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name_en', 'Updated Court Name');
});

it('soft-deletes a court as owner', function () {
    [$user, $workspace] = setupCourtUser('owner');
    $court = Court::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/courts/{$court->id}");

    $response->assertNoContent();
    expect(Court::find($court->id))->toBeNull();
    expect(Court::withTrashed()->find($court->id))->not->toBeNull();
});

it('denies court delete to member role', function () {
    [$user, $workspace] = setupCourtUser('member');
    $court = Court::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/courts/{$court->id}");

    $response->assertForbidden();
});

// --- Judge CRUD ---

it('lists judges in current workspace only', function () {
    [$user, $workspace] = setupCourtUser();
    $otherWorkspace = Workspace::factory()->create();

    Judge::factory()->create(['workspace_id' => $workspace->id]);
    Judge::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/judges');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('creates a judge with valid data', function () {
    [$user, $workspace] = setupCourtUser();
    $court = Court::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/judges', [
        'name_ar' => 'القاضي أحمد',
        'name_en' => 'Judge Ahmad',
        'court_id' => $court->id,
        'title' => 'رئيس محكمة',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name_ar', 'القاضي أحمد');
    $response->assertJsonPath('data.court_id', $court->id);
});

it('updates a judge', function () {
    [$user, $workspace] = setupCourtUser();
    $judge = Judge::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/judges/{$judge->id}", [
        'title' => 'Senior Judge',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.title', 'Senior Judge');
});

it('soft-deletes a judge as owner', function () {
    [$user, $workspace] = setupCourtUser('owner');
    $judge = Judge::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/judges/{$judge->id}");

    $response->assertNoContent();
    expect(Judge::find($judge->id))->toBeNull();
    expect(Judge::withTrashed()->find($judge->id))->not->toBeNull();
});
