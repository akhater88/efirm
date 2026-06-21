<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('creates a new workspace via POST /api/v1/workspaces', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces', ['name' => 'New Workspace']);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'New Workspace');
});

it('auto-adds creator as owner of new workspace', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces', ['name' => 'New Workspace']);

    $workspace = Workspace::where('name', 'New Workspace')->first();
    $member = WorkspaceMember::where('workspace_id', $workspace->id)
        ->where('user_id', $user->id)
        ->first();

    expect($member->role->value)->toBe('owner');
});

it('returns 201 on workspace creation', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces', ['name' => 'Test']);

    $response->assertStatus(201);
});

it('validates workspace name is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('name');
});

it('validates workspace name max length', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces', ['name' => str_repeat('a', 256)]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('name');
});

it('defaults locale to ar when not provided', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces', ['name' => 'Test']);

    $response->assertJsonPath('data.default_locale', 'ar');
});

it('accepts custom default_locale for new workspace', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces', [
            'name' => 'English Workspace',
            'default_locale' => 'en',
        ]);

    $response->assertJsonPath('data.default_locale', 'en');
});

it('switches workspace via POST /api/v1/workspaces/switch', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces/switch', [
            'workspace_id' => $workspace->id,
        ]);

    $response->assertOk();
    $response->assertJsonPath('data.id', $workspace->id);
});

it('rejects switch to workspace user does not belong to', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces/switch', [
            'workspace_id' => $workspace->id,
        ]);

    $response->assertForbidden();
});

it('returns 422 for invalid workspace_id on switch', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/workspaces/switch', [
            'workspace_id' => 'nonexistent-id-xxxxxxxxxxx',
        ]);

    $response->assertUnprocessable();
});

it('returns 401 for unauthenticated workspace operations', function () {
    $response = $this->postJson('/api/v1/workspaces', ['name' => 'Test']);

    $response->assertUnauthorized();
});
