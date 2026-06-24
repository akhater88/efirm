<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('allows owner to access filament admin panel', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->get("/app/workspace/{$workspace->id}");

    $response->assertStatus(200);
});

it('allows admin to access filament admin panel', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->admin()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->get("/app/workspace/{$workspace->id}");

    $response->assertStatus(200);
});

it('allows member to access filament admin panel (Filament-everywhere pivot)', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->get("/app/workspace/{$workspace->id}");

    $response->assertStatus(200);
});

it('denies unauthenticated user access to filament admin panel', function () {
    $response = $this->get('/app');

    $response->assertRedirect();
});
