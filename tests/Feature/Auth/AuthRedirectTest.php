<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('redirects unauthenticated user from /dashboard to login', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

it('allows authenticated user to access /dashboard', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
});

it('guest can access login page', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
});

it('authenticated user accessing login is redirected to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('login'));

    // Guest middleware redirects authenticated users
    $response->assertRedirect(route('dashboard'));
});
