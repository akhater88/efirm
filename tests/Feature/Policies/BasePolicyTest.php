<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Policies\WorkspacePolicy;

it('owner short-circuits to true via before()', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $user->switchWorkspace($workspace);

    $policy = new WorkspacePolicy;
    $result = $policy->before($user, 'delete');

    expect($result)->toBeTrue();
});

it('admin falls through before() to specific method', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->admin()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $user->switchWorkspace($workspace);

    $policy = new WorkspacePolicy;
    $result = $policy->before($user, 'delete');

    expect($result)->toBeNull();
});

it('member falls through before() to specific method', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $user->switchWorkspace($workspace);

    $policy = new WorkspacePolicy;
    $result = $policy->before($user, 'view');

    expect($result)->toBeNull();
});

it('returns false when user has no current workspace', function () {
    $user = User::factory()->create();

    session()->forget('current_workspace_id');

    $policy = new WorkspacePolicy;
    $result = $policy->before($user, 'view');

    expect($result)->toBeFalse();
});
