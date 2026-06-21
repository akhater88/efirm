<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

it('creates the users table with expected columns', function () {
    expect(Schema::hasTable('users'))->toBeTrue();
    expect(Schema::hasColumns('users', [
        'id', 'name', 'email', 'email_verified_at', 'password',
        'avatar_url', 'preferred_locale', 'google_id',
        'remember_token', 'created_at', 'updated_at',
    ]))->toBeTrue();
});

it('creates the workspaces table with expected columns', function () {
    expect(Schema::hasTable('workspaces'))->toBeTrue();
    expect(Schema::hasColumns('workspaces', [
        'id', 'name', 'slug', 'default_locale',
        'created_by_user_id', 'updated_by_user_id',
        'created_at', 'updated_at', 'deleted_at',
    ]))->toBeTrue();
});

it('creates the workspace_members table with expected columns', function () {
    expect(Schema::hasTable('workspace_members'))->toBeTrue();
    expect(Schema::hasColumns('workspace_members', [
        'id', 'workspace_id', 'user_id', 'role', 'joined_at',
        'created_by_user_id', 'updated_by_user_id',
        'created_at', 'updated_at',
    ]))->toBeTrue();
});

it('enforces unique constraint on workspace_members workspace_id + user_id', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();

    WorkspaceMember::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    expect(fn () => WorkspaceMember::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]))->toThrow(QueryException::class);
});

it('enforces unique constraint on users email', function () {
    User::factory()->create(['email' => 'test@example.com']);

    expect(fn () => User::factory()->create([
        'email' => 'test@example.com',
    ]))->toThrow(QueryException::class);
});

it('enforces unique constraint on workspaces slug', function () {
    Workspace::factory()->create(['slug' => 'test-slug']);

    expect(fn () => Workspace::factory()->create([
        'slug' => 'test-slug',
    ]))->toThrow(QueryException::class);
});

it('enforces foreign key from workspace_members to workspaces', function () {
    expect(fn () => WorkspaceMember::factory()->create([
        'workspace_id' => 'nonexistent-workspace-id-xx',
    ]))->toThrow(QueryException::class);
});

it('enforces foreign key from workspace_members to users', function () {
    $workspace = Workspace::factory()->create();

    expect(fn () => WorkspaceMember::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => 'nonexistent-user-id-xxxxxxx',
    ]))->toThrow(QueryException::class);
});
