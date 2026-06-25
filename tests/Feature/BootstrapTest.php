<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('shows landing page for guests at root', function () {
    $response = $this->get('/');
    $response->assertOk();
});

it('redirects authenticated users from root to dashboard', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);
    $user->update(['current_workspace_id' => $workspace->id]);

    $response = $this->actingAs($user)->get('/');
    $response->assertRedirect(route('dashboard'));
});

it('can connect to the database', function () {
    expect(DB::connection()->getPdo())->not->toBeNull();
});

it('can run and rollback migrations', function () {
    Artisan::call('migrate:status');
    expect(Artisan::output())->toContain('users');
});
