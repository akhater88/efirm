<?php

use App\Enums\HearingType;
use App\Enums\MatterLawyerRole;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\Court;
use App\Models\Hearing;
use App\Models\LawyerProfile;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\MatterLawyerService;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->owner = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->owner->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->owner->switchWorkspace($this->workspace);
    Sanctum::actingAs($this->owner);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'is_litigation' => true,
    ]);

    $this->court = Court::factory()->create(['workspace_id' => $this->workspace->id]);

    // Create lead lawyer
    $this->leadUser = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->leadUser->id,
        'workspace_id' => $this->workspace->id,
    ]);
    LawyerProfile::factory()->create(['user_id' => $this->leadUser->id]);

    // Assign as lead
    app(MatterLawyerService::class)->assignLawyer(
        $this->matter,
        $this->leadUser,
        MatterLawyerRole::Lead,
        $this->owner,
    );

    // Create supporting lawyer
    $this->supportingUser = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->supportingUser->id,
        'workspace_id' => $this->workspace->id,
    ]);
    LawyerProfile::factory()->create(['user_id' => $this->supportingUser->id]);
});

it('auto-populates assigned_lawyer from Matter lead on Hearing creation', function () {
    $hearing = Hearing::create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $this->matter->id,
        'hearing_date' => now()->addDays(7),
        'court_id' => $this->court->id,
        'hearing_type' => HearingType::FirstSession,
        'status' => 'scheduled',
        'created_by_user_id' => $this->owner->id,
    ]);

    expect($hearing->assigned_lawyer_user_id)->toBe($this->leadUser->id)
        ->and($hearing->lawyer_assigned_at)->not->toBeNull();
});

it('allows override of assigned_lawyer on creation', function () {
    $hearing = Hearing::create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $this->matter->id,
        'hearing_date' => now()->addDays(7),
        'court_id' => $this->court->id,
        'hearing_type' => HearingType::FirstSession,
        'status' => 'scheduled',
        'assigned_lawyer_user_id' => $this->supportingUser->id,
        'lawyer_assigned_at' => now(),
        'created_by_user_id' => $this->owner->id,
    ]);

    expect($hearing->assigned_lawyer_user_id)->toBe($this->supportingUser->id);
});

it('returns assigned_lawyer in hearing API response', function () {
    $hearing = Hearing::factory()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $this->matter->id,
        'court_id' => $this->court->id,
        'assigned_lawyer_user_id' => $this->leadUser->id,
    ]);

    $response = $this->getJson("/api/v1/hearings/{$hearing->id}");

    $response->assertOk()
        ->assertJsonPath('data.assigned_lawyer_user_id', $this->leadUser->id);
});

it('existing S-08 hearing tests still work (backward compat)', function () {
    // A hearing without assigned_lawyer should still be valid
    $hearing = Hearing::factory()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $this->matter->id,
        'court_id' => $this->court->id,
    ]);

    expect($hearing->id)->not->toBeNull()
        ->and($hearing->matter_id)->toBe($this->matter->id);
});

it('assignedLawyer relationship resolves correctly', function () {
    $hearing = Hearing::create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $this->matter->id,
        'hearing_date' => now()->addDays(5),
        'court_id' => $this->court->id,
        'hearing_type' => HearingType::Evidence,
        'status' => 'scheduled',
        'assigned_lawyer_user_id' => $this->supportingUser->id,
        'created_by_user_id' => $this->owner->id,
    ]);

    expect($hearing->assignedLawyer->id)->toBe($this->supportingUser->id)
        ->and($hearing->assignedLawyer->id)->not->toBe($this->leadUser->id);
});
