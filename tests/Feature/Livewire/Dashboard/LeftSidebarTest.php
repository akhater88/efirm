<?php

use App\Enums\Role;
use App\Livewire\Dashboard\LeftSidebar;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Livewire\Livewire;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);
});

test('left sidebar renders with nav groups', function () {
    Livewire::test(LeftSidebar::class)
        ->assertSee(__('shell.nav_dashboard'))
        ->assertSee(__('shell.nav_matters'))
        ->assertSee(__('shell.nav_contacts'))
        ->assertSee(__('shell.nav_tasks'))
        ->assertSee(__('shell.nav_documents'))
        ->assertStatus(200);
});

test('left sidebar does not contain forbidden nav items', function () {
    $component = Livewire::test(LeftSidebar::class);

    // CLAUDE.md §10: no hearings, court reviews, service log
    $html = $component->html();
    expect($html)->not->toContain('hearings');
    expect($html)->not->toContain('court-reviews');
    expect($html)->not->toContain('service-log');
});

test('left sidebar can toggle collapse', function () {
    Livewire::test(LeftSidebar::class)
        ->assertSet('collapsed', false)
        ->call('toggleCollapse')
        ->assertSet('collapsed', true)
        ->call('toggleCollapse')
        ->assertSet('collapsed', false);
});

test('left sidebar shows logo variants based on collapse state', function () {
    Livewire::test(LeftSidebar::class)
        ->assertSeeHtml('efirm-horizontal-compact-reversed.svg')
        ->call('toggleCollapse')
        ->assertSeeHtml('efirm-mark-reversed.svg');
});

test('left sidebar uses brand tokens for styling', function () {
    $component = Livewire::test(LeftSidebar::class);
    $html = $component->html();

    // Sidebar uses brand-700 for background
    expect($html)->toContain('#072E17');
    // Sidebar uses brand-800 for hover state
    expect($html)->toContain('#052015');
});

test('shell sidebar lang keys have parity', function () {
    $en = require resource_path('lang/en/shell.php');
    $ar = require resource_path('lang/ar/shell.php');

    expect(array_keys($en))->toBe(array_keys($ar));
});

test('dashboard renders with sidebar', function () {
    $response = $this->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee(__('shell.nav_matters'));
});
