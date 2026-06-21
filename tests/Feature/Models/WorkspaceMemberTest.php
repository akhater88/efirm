<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\QueryException;

it('can create a workspace member with ULID primary key', function () {
    $member = WorkspaceMember::factory()->create();

    expect($member->id)->toHaveLength(26);
});

it('belongs to a workspace', function () {
    $member = WorkspaceMember::factory()->create();

    expect($member->workspace)->toBeInstanceOf(Workspace::class);
});

it('belongs to a user', function () {
    $member = WorkspaceMember::factory()->create();

    expect($member->user)->toBeInstanceOf(User::class);
});

it('enforces unique constraint on workspace_id + user_id', function () {
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

it('has role helper methods', function () {
    $owner = WorkspaceMember::factory()->owner()->create();
    $admin = WorkspaceMember::factory()->admin()->create();
    $member = WorkspaceMember::factory()->create();

    expect($owner->isOwner())->toBeTrue();
    expect($owner->isAdmin())->toBeFalse();
    expect($owner->isMember())->toBeFalse();

    expect($admin->isOwner())->toBeFalse();
    expect($admin->isAdmin())->toBeTrue();
    expect($admin->isMember())->toBeFalse();

    expect($member->isOwner())->toBeFalse();
    expect($member->isAdmin())->toBeFalse();
    expect($member->isMember())->toBeTrue();
});

it('defaults role to member', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();

    $member = WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'joined_at' => now(),
    ]);

    $member->refresh();

    expect($member->role)->toBe('member');
});
