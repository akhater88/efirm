<?php

use App\Enums\Role;
use App\Livewire\Dashboard\TopChrome;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\QuickTimerService;
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

test('top chrome renders with workspace name', function () {
    Livewire::test(TopChrome::class)
        ->assertSee($this->workspace->name)
        ->assertSee(__('shell.search_placeholder'))
        ->assertStatus(200);
});

test('top chrome shows start timer when no timer is active', function () {
    Livewire::test(TopChrome::class)
        ->assertSee(__('shell.start_timer'));
});

test('top chrome shows recent matters in timer dropdown', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'title' => 'Test Matter for Timer',
    ]);

    Livewire::test(TopChrome::class)
        ->assertSee('Test Matter for Timer');
});

test('top chrome can start and stop timer', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    $component = Livewire::test(TopChrome::class)
        ->call('startTimerForMatter', $matter->id);

    $timer = app(QuickTimerService::class)->getActiveTimerForUser($this->user);
    expect($timer)->not->toBeNull();

    $component->call('stopTimer')
        ->assertDispatched('notify');

    $timer = app(QuickTimerService::class)->getActiveTimerForUser($this->user);
    expect($timer)->toBeNull();
});

test('top chrome dispatches coming soon for chat', function () {
    Livewire::test(TopChrome::class)
        ->call('openChat')
        ->assertDispatched('notify');
});

test('top chrome shows notification count', function () {
    Livewire::test(TopChrome::class)
        ->assertSee(__('shell.notifications'));
});

test('shell lang files have key parity', function () {
    $en = require resource_path('lang/en/shell.php');
    $ar = require resource_path('lang/ar/shell.php');

    expect(array_keys($en))->toBe(array_keys($ar));
});

test('dashboard renders with top chrome layout', function () {
    $response = $this->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee($this->workspace->name);
});
