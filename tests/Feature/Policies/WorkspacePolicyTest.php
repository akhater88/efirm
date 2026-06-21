<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupUserWithRole(string $role): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    return [$user, $workspace];
}

it('allows any member to view their workspace', function () {
    [$user, $workspace] = setupUserWithRole('member');

    expect($user->can('view', $workspace))->toBeTrue();
});

it('denies view to non-member', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $ownWorkspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $ownWorkspace->id,
    ]);
    $user->switchWorkspace($ownWorkspace);

    expect($user->can('view', $workspace))->toBeFalse();
});

it('allows any authenticated user to create a workspace', function () {
    [$user, $workspace] = setupUserWithRole('member');

    expect($user->can('create', Workspace::class))->toBeTrue();
});

it('allows owner to update workspace', function () {
    [$user, $workspace] = setupUserWithRole('owner');

    expect($user->can('update', $workspace))->toBeTrue();
});

it('allows admin to update workspace', function () {
    [$user, $workspace] = setupUserWithRole('admin');

    expect($user->can('update', $workspace))->toBeTrue();
});

it('denies member from updating workspace', function () {
    [$user, $workspace] = setupUserWithRole('member');

    expect($user->can('update', $workspace))->toBeFalse();
});

it('allows only owner to delete workspace', function () {
    [$user, $workspace] = setupUserWithRole('owner');

    expect($user->can('delete', $workspace))->toBeTrue();
});

it('denies admin from deleting workspace', function () {
    [$user, $workspace] = setupUserWithRole('admin');

    expect($user->can('delete', $workspace))->toBeFalse();
});

it('allows owner and admin to invite members', function () {
    [$owner, $workspace] = setupUserWithRole('owner');
    expect($owner->can('inviteMember', $workspace))->toBeTrue();

    [$admin, $workspace2] = setupUserWithRole('admin');
    expect($admin->can('inviteMember', $workspace2))->toBeTrue();
});

it('denies member from inviting members', function () {
    [$user, $workspace] = setupUserWithRole('member');

    expect($user->can('inviteMember', $workspace))->toBeFalse();
});

it('allows owner and admin to remove members', function () {
    [$owner, $workspace] = setupUserWithRole('owner');
    expect($owner->can('removeMember', $workspace))->toBeTrue();

    [$admin, $workspace2] = setupUserWithRole('admin');
    expect($admin->can('removeMember', $workspace2))->toBeTrue();
});

it('denies member from removing members', function () {
    [$user, $workspace] = setupUserWithRole('member');

    expect($user->can('removeMember', $workspace))->toBeFalse();
});
