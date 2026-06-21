<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('shows dashboard for authenticated user', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

it('displays current workspace name on dashboard', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['name' => 'Test Law Office']);
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSee('Test Law Office');
});

it('redirects unauthenticated user to login', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

it('dashboard renders in ar locale with rtl direction', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['locale' => 'ar'])
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('dir="rtl"', false);
});

it('dashboard renders in en locale with ltr direction', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('dir="ltr"', false);
});
