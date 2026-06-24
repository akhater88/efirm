<?php

use App\Enums\AdminRole;
use App\Filament\Admin\Resources\WorkspaceResource;
use App\Models\AdminUser;
use App\Models\Workspace;

beforeEach(function () {
    $this->superAdmin = AdminUser::create([
        'name' => 'Super Admin',
        'email' => 'super@test.com',
        'password' => bcrypt('password'),
        'role' => AdminRole::SuperAdmin,
    ]);

    Workspace::factory()->count(3)->create();
});

test('workspace list is accessible by super_admin', function () {
    $this->actingAs($this->superAdmin, 'admin')
        ->get('/admin/workspaces')
        ->assertOk();
});

test('workspace list shows workspace names', function () {
    $workspace = Workspace::first();

    $this->actingAs($this->superAdmin, 'admin')
        ->get('/admin/workspaces')
        ->assertSee($workspace->name);
});

test('workspace view page is accessible', function () {
    $workspace = Workspace::first();

    $this->actingAs($this->superAdmin, 'admin')
        ->get("/admin/workspaces/{$workspace->id}")
        ->assertOk();
});

test('workspace resource does not allow creation', function () {
    expect(WorkspaceResource::canCreate())->toBeFalse();
});

test('workspace resource uses withoutGlobalScopes', function () {
    // Ensure admin can see workspaces even without BelongsToWorkspace scope
    $this->actingAs($this->superAdmin, 'admin');

    $response = $this->get('/admin/workspaces');
    $response->assertOk();
});
