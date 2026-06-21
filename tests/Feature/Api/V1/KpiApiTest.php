<?php

use App\Enums\Role;
use App\Models\Contact;
use App\Models\KpiTarget;
use App\Models\Matter;
use App\Models\Team;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    Sanctum::actingAs($this->user);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);
});

it('creates a KPI target for a user', function () {
    $response = $this->postJson('/api/v1/kpi/targets', [
        'targetable_type' => 'user',
        'targetable_id' => $this->user->id,
        'metric' => 'billable_hours_monthly',
        'target_value' => 120,
        'period' => 'monthly',
        'effective_from' => now()->startOfMonth()->toDateString(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.metric', 'billable_hours_monthly');
});

it('computes my-progress with correct billable hours', function () {
    // Set a target of 100 billable hours
    KpiTarget::create([
        'workspace_id' => $this->workspace->id,
        'targetable_type' => 'user',
        'targetable_id' => $this->user->id,
        'metric' => 'billable_hours_monthly',
        'target_value' => 100,
        'period' => 'monthly',
        'effective_from' => now()->startOfMonth(),
        'created_by_user_id' => $this->user->id,
    ]);

    // Log 90 minutes of billable time
    TimeEntry::factory()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
        'duration_minutes' => 90,
        'is_billable' => true,
        'started_at' => now(),
        'ended_at' => now()->addMinutes(90),
    ]);

    $response = $this->getJson('/api/v1/kpi/my-progress');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['metric'])->toBe('billable_hours_monthly')
        ->and($data[0]['actual_value'])->toBe('1.50') // 90 min = 1.5 hours
        ->and((float) $data[0]['percentage'])->toBeLessThan(5); // 1.5/100 = 1.5%
});

it('computes team progress', function () {
    $team = Team::create([
        'workspace_id' => $this->workspace->id,
        'name' => 'Test Team',
        'created_by_user_id' => $this->user->id,
    ]);
    $team->members()->attach($this->user->id);

    KpiTarget::create([
        'workspace_id' => $this->workspace->id,
        'targetable_type' => 'team',
        'targetable_id' => $team->id,
        'metric' => 'billable_hours_monthly',
        'target_value' => 200,
        'period' => 'monthly',
        'effective_from' => now()->startOfMonth(),
        'created_by_user_id' => $this->user->id,
    ]);

    TimeEntry::factory()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
        'duration_minutes' => 120,
        'is_billable' => true,
        'started_at' => now(),
        'ended_at' => now()->addMinutes(120),
    ]);

    $response = $this->getJson("/api/v1/kpi/team/{$team->id}/progress");

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['actual_value'])->toBe('2.00'); // 120 min = 2 hours
});
