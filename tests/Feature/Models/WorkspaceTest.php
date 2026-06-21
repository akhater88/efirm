<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('can create a workspace with ULID primary key', function () {
    $workspace = Workspace::factory()->create();

    expect($workspace->id)->toHaveLength(26);
});

it('auto-generates slug from name on create', function () {
    $workspace = Workspace::factory()->create([
        'name' => 'Test Workspace',
        'slug' => null,
    ]);

    // Slug is set because creating hook fills it
    expect($workspace->slug)->not->toBeEmpty();
});

it('generates a slug from arabic name via transliteration', function () {
    $workspace = Workspace::factory()->create([
        'name' => 'مكتب المحاماة',
        'slug' => null,
    ]);

    // Str::slug() transliterates Arabic to Latin
    expect($workspace->slug)->not->toBeEmpty();
    expect($workspace->slug)->toBeString();
});

it('has a members relationship', function () {
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    expect($workspace->members)->toHaveCount(1);
});

it('has a users relationship', function () {
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    expect($workspace->users)->toHaveCount(1);
    expect($workspace->users->first()->id)->toBe($user->id);
});

it('has a createdBy relationship', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create([
        'created_by_user_id' => $user->id,
    ]);

    expect($workspace->createdBy->id)->toBe($user->id);
});

it('is soft-deletable', function () {
    $workspace = Workspace::factory()->create();
    $workspace->delete();

    expect($workspace->trashed())->toBeTrue();
    expect(Workspace::withTrashed()->find($workspace->id))->not->toBeNull();
    expect(Workspace::find($workspace->id))->toBeNull();
});

it('can scope to workspaces owned by a user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $ownedWorkspace = Workspace::factory()->create();
    $otherWorkspace = Workspace::factory()->create();

    WorkspaceMember::factory()->owner()->create([
        'workspace_id' => $ownedWorkspace->id,
        'user_id' => $owner->id,
    ]);
    WorkspaceMember::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'user_id' => $other->id,
    ]);

    $results = Workspace::ownedBy($owner)->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($ownedWorkspace->id);
});
