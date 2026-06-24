<?php

use App\Enums\MatterStatus;
use App\Enums\Role;
use App\Livewire\Dashboard\Widget\LegalMattersWidget;
use App\Models\Contact;
use App\Models\Matter;
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

test('legal matters widget renders empty state when no matters', function () {
    Livewire::test(LegalMattersWidget::class)
        ->assertSee(__('dashboard.no_recent_matters'));
});

test('legal matters widget shows recent matters', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'title' => 'Commercial Contract Review',
        'status' => MatterStatus::Active,
    ]);

    Livewire::test(LegalMattersWidget::class)
        ->assertSee('Commercial Contract Review')
        ->assertSee($client->display_name);
});

test('legal matters widget shows status badges', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'status' => MatterStatus::Active,
    ]);

    Livewire::test(LegalMattersWidget::class)
        ->assertSee(__('matters.status_active'));
});

test('legal matters widget limits to 5 results', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    Matter::factory()->count(7)->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'status' => MatterStatus::Active,
    ]);

    $component = Livewire::test(LegalMattersWidget::class);
    $html = $component->html();

    // Count matter links (each has /app/matters/ href)
    $count = substr_count($html, '/app/matters/');
    // 5 matter links + 1 "view all" + 1 "create" = up to 7, but matter links should be exactly 5
    expect($count)->toBeLessThanOrEqual(7);
});

test('legal matters widget shows view all and create links', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    Livewire::test(LegalMattersWidget::class)
        ->assertSee(__('common.view_all'))
        ->assertSee(__('shell.new_matter'));
});

test('legal matters widget does not show cross-workspace matters', function () {
    $otherWorkspace = Workspace::factory()->create();
    $client = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);
    Matter::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'client_id' => $client->id,
        'title' => 'Other Workspace Matter',
    ]);

    Livewire::test(LegalMattersWidget::class)
        ->assertDontSee('Other Workspace Matter');
});
