<?php

use App\Enums\Role;
use App\Livewire\Dashboard\DashboardHero;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Livewire\Livewire;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create(['name' => 'Ahmad']);
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);
});

test('hero renders with greeting and date', function () {
    Livewire::test(DashboardHero::class)
        ->assertSee('Ahmad')
        ->assertSee(__('brand.ai_twin_title'))
        ->assertStatus(200);
});

test('hero shows AI Twin card with CTA', function () {
    Livewire::test(DashboardHero::class)
        ->assertSee(__('brand.ai_twin_cta'))
        ->assertSee(__('brand.ai_twin_coming_soon'));
});

test('hero can open and close AI Twin modal', function () {
    Livewire::test(DashboardHero::class)
        ->assertSet('showAiTwinModal', false)
        ->set('showAiTwinModal', true)
        ->assertSee(__('dashboard.ai_twin_modal_description'))
        ->set('showAiTwinModal', false);
});

test('hero can submit waitlist email', function () {
    Livewire::test(DashboardHero::class)
        ->set('showAiTwinModal', true)
        ->set('waitlistEmail', 'test@example.com')
        ->call('submitWaitlist')
        ->assertDispatched('notify');

    $this->assertDatabaseHas('ai_twin_waitlist_entries', [
        'email' => 'test@example.com',
    ]);
});

test('hero validates empty email on submit', function () {
    Livewire::test(DashboardHero::class)
        ->set('showAiTwinModal', true)
        ->set('waitlistEmail', '')
        ->call('submitWaitlist')
        ->assertHasErrors(['waitlistEmail']);
});

test('hero validates invalid email on submit', function () {
    Livewire::test(DashboardHero::class)
        ->set('showAiTwinModal', true)
        ->set('waitlistEmail', 'not-an-email')
        ->call('submitWaitlist')
        ->assertHasErrors(['waitlistEmail']);
});

test('dashboard lang files have key parity', function () {
    $en = require resource_path('lang/en/dashboard.php');
    $ar = require resource_path('lang/ar/dashboard.php');

    expect(array_keys($en))->toBe(array_keys($ar));
});

test('dashboard renders with hero banner', function () {
    $response = $this->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee('Ahmad');
});
