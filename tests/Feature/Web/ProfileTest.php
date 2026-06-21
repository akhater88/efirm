<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('shows profile page for authenticated user', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertOk();
});

it('displays user name and email', function () {
    $user = User::factory()->create([
        'name' => 'Ahmad Test',
        'email' => 'ahmad@test.com',
    ]);
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertSee('Ahmad Test');
    $response->assertSee('ahmad@test.com');
});

it('updates user name via PUT /profile', function () {
    $user = User::factory()->create(['name' => 'Old Name']);
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'New Name',
        'preferred_locale' => 'ar',
    ]);

    $response->assertRedirect(route('profile'));
    $user->refresh();
    expect($user->name)->toBe('New Name');
});

it('updates user preferred_locale via PUT /profile', function () {
    $user = User::factory()->create(['preferred_locale' => 'ar']);
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'preferred_locale' => 'en',
    ]);

    $user->refresh();
    expect($user->preferred_locale)->toBe('en');
});

it('rejects name longer than 255 characters', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => str_repeat('a', 256),
        'preferred_locale' => 'ar',
    ]);

    $response->assertSessionHasErrors('name');
});

it('rejects invalid locale value', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Test',
        'preferred_locale' => 'fr',
    ]);

    $response->assertSessionHasErrors('preferred_locale');
});

it('shows success flash after update', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Updated Name',
        'preferred_locale' => 'ar',
    ]);

    $response->assertSessionHas('success');
});
