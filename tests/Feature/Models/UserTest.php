<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('can create a user with ULID primary key', function () {
    $user = User::factory()->create();

    expect($user->id)->toHaveLength(26);
});

it('has a workspaces relationship', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    expect($user->workspaces)->toHaveCount(1);
    expect($user->workspaces->first()->id)->toBe($workspace->id);
});

it('has a workspaceMembers relationship', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    expect($user->workspaceMembers)->toHaveCount(1);
    expect($user->workspaceMembers->first()->role)->toBe('owner');
});

it('returns current workspace from session', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    session(['current_workspace_id' => $workspace->id]);

    expect($user->currentWorkspace()->id)->toBe($workspace->id);
});

it('falls back to first workspace when no session', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    session()->forget('current_workspace_id');

    expect($user->currentWorkspace()->id)->toBe($workspace->id);
});

it('can switch workspace', function () {
    $user = User::factory()->create();
    $ws1 = Workspace::factory()->create();
    $ws2 = Workspace::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $ws1->id]);
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $ws2->id]);

    $user->switchWorkspace($ws2);

    expect(session('current_workspace_id'))->toBe($ws2->id);
    expect($user->currentWorkspace()->id)->toBe($ws2->id);
});

it('returns role in a specific workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    expect($user->roleInWorkspace($workspace))->toBe('owner');
});

it('returns null role for non-member workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();

    expect($user->roleInWorkspace($workspace))->toBeNull();
});

it('can check if user belongs to a workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $otherWorkspace = Workspace::factory()->create();

    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    expect($user->belongsToWorkspace($workspace))->toBeTrue();
    expect($user->belongsToWorkspace($otherWorkspace))->toBeFalse();
});
