<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('returns authenticated user data via GET /api/v1/me', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/me');

    $response->assertOk();
    $response->assertJsonPath('data.id', $user->id);
    $response->assertJsonPath('data.email', $user->email);
});

it('includes current workspace in response', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/me');

    $response->assertJsonPath('data.current_workspace.id', $workspace->id);
});

it('includes current role in response', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/me');

    $response->assertJsonPath('data.current_role', 'owner');
});

it('includes list of user workspaces', function () {
    $user = User::factory()->create();
    $ws1 = Workspace::factory()->create();
    $ws2 = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create(['user_id' => $user->id, 'workspace_id' => $ws1->id]);
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $ws2->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/me');

    $response->assertJsonCount(2, 'data.workspaces');
});

it('returns 401 for unauthenticated request', function () {
    $response = $this->getJson('/api/v1/me');

    $response->assertUnauthorized();
});
