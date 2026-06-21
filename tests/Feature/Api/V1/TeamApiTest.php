<?php

use App\Enums\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    Sanctum::actingAs($this->user);
});

it('creates a team', function () {
    $response = $this->postJson('/api/v1/teams', [
        'name' => 'Corporate Practice',
        'description' => 'Commercial and corporate law team',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Corporate Practice');
});

it('creates nested teams', function () {
    $parent = Team::create([
        'workspace_id' => $this->workspace->id,
        'name' => 'Corporate',
        'created_by_user_id' => $this->user->id,
    ]);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'M&A Subteam',
        'parent_team_id' => $parent->id,
    ]);

    $response->assertCreated();

    $parent->refresh();
    expect($parent->subTeams)->toHaveCount(1)
        ->and($parent->subTeams->first()->name)->toBe('M&A Subteam');
});

it('attaches and detaches members', function () {
    $team = Team::create([
        'workspace_id' => $this->workspace->id,
        'name' => 'Test Team',
        'created_by_user_id' => $this->user->id,
    ]);

    $member = User::factory()->create();

    $this->postJson("/api/v1/teams/{$team->id}/members", [
        'user_id' => $member->id,
        'role_in_team' => 'associate',
    ])->assertOk();

    expect($team->members)->toHaveCount(1);

    $this->deleteJson("/api/v1/teams/{$team->id}/members/{$member->id}")
        ->assertNoContent();

    $team->refresh();
    expect($team->members)->toHaveCount(0);
});

it('lists teams with member count', function () {
    Team::create([
        'workspace_id' => $this->workspace->id,
        'name' => 'Team A',
        'created_by_user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/v1/teams');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});
