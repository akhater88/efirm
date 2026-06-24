<?php

use App\Enums\Role;
use App\Livewire\Dashboard\QuickLinksRail;
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

test('quick links rail renders with navigation links', function () {
    Livewire::test(QuickLinksRail::class)
        ->assertSee(__('shell.nav_matters'))
        ->assertSee(__('shell.nav_contacts'))
        ->assertSee(__('shell.nav_documents'))
        ->assertSee(__('shell.nav_tasks'))
        ->assertSee(__('shell.nav_obligations'))
        ->assertSee(__('shell.nav_clause_library'))
        ->assertStatus(200);
});

test('quick links rail does not contain forbidden items', function () {
    $html = Livewire::test(QuickLinksRail::class)->html();

    expect($html)->not->toContain('hearings');
    expect($html)->not->toContain('court-reviews');
    expect($html)->not->toContain('service-log');
    expect($html)->not->toContain('leads');
    expect($html)->not->toContain('invoices');
});

test('quick links rail has correct link URLs', function () {
    $html = Livewire::test(QuickLinksRail::class)->html();

    expect($html)->toContain('/app/matters');
    expect($html)->toContain('/app/contacts');
    expect($html)->toContain('/app/documents');
    expect($html)->toContain('/app/tasks');
    expect($html)->toContain('/app/obligations');
    expect($html)->toContain('/app/library-clauses');
    expect($html)->toContain('/app/time-entries');
});

test('quick links rail hides below 1280px via CSS', function () {
    $html = Livewire::test(QuickLinksRail::class)->html();

    expect($html)->toContain('max-width: 1279px');
    expect($html)->toContain('display: none');
});

test('quick links rail shows title', function () {
    Livewire::test(QuickLinksRail::class)
        ->assertSee(__('shell.quick_links'));
});
