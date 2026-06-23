<?php

use App\Enums\KpiMetric;
use App\Enums\MatterLawyerRole;
use App\Enums\MatterStatus;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\LawyerProfile;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\KpiService;
use App\Services\MatterLawyerService;
use Illuminate\Support\Carbon;

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

    $this->lawyer = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->lawyer->id,
        'workspace_id' => $this->workspace->id,
    ]);
    LawyerProfile::factory()->create(['user_id' => $this->lawyer->id]);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);

    // Create 2 active matters with lawyer as lead
    $this->matter1 = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'status' => MatterStatus::Active,
    ]);
    $this->matter2 = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'status' => MatterStatus::Active,
    ]);

    $matterLawyerService = app(MatterLawyerService::class);
    $matterLawyerService->assignLawyer($this->matter1, $this->lawyer, MatterLawyerRole::Lead, $this->user);
    $matterLawyerService->assignLawyer($this->matter2, $this->lawyer, MatterLawyerRole::Lead, $this->user);

    $this->kpiService = app(KpiService::class);
});

it('counts matters as lead active correctly', function () {
    $value = $this->kpiService->getActualValue(
        $this->lawyer,
        KpiMetric::MattersAsLeadActive,
        Carbon::now()->startOfMonth(),
        Carbon::now()->endOfMonth(),
    );

    expect($value)->toBe('2');
});

it('counts matters as supporting active correctly', function () {
    // Assign as supporting on a third matter
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter3 = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'status' => MatterStatus::Active,
    ]);
    app(MatterLawyerService::class)->assignLawyer($matter3, $this->lawyer, MatterLawyerRole::Supporting, $this->user);

    $value = $this->kpiService->getActualValue(
        $this->lawyer,
        KpiMetric::MattersAsSupportingActive,
        Carbon::now()->startOfMonth(),
        Carbon::now()->endOfMonth(),
    );

    expect($value)->toBe('1');
});

it('counts matters closed as lead in period', function () {
    $this->matter1->update([
        'status' => MatterStatus::Closed,
        'closed_at' => now(),
    ]);

    $value = $this->kpiService->getActualValue(
        $this->lawyer,
        KpiMetric::MattersClosedAsLeadPeriod,
        Carbon::now()->startOfMonth(),
        Carbon::now()->endOfMonth(),
    );

    expect($value)->toBe('1');
});
