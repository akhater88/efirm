<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Route;

it('auto-selects first workspace if none in session', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    Route::middleware(['web', 'auth', 'workspace'])->get('/_test-workspace-middleware', function () {
        return response()->json([
            'workspace_id' => session('current_workspace_id'),
        ]);
    });

    $response = $this->actingAs($user)->get('/_test-workspace-middleware');
    $response->assertOk();

    $data = $response->json();
    expect($data['workspace_id'])->toBe($workspace->id);
});

it('aborts 403 if user has no workspaces', function () {
    $user = User::factory()->create();

    Route::middleware(['web', 'auth', 'workspace'])->get('/_test-workspace-middleware-403', fn () => 'ok');

    $response = $this->actingAs($user)->get('/_test-workspace-middleware-403');

    $response->assertStatus(403);
});

it('passes through if workspace already selected', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $user->switchWorkspace($workspace);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    expect(session('current_workspace_id'))->toBe($workspace->id);
});
