<?php

use App\Enums\MatterLawyerRole;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\LawyerProfile;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\LawyerPickerService;
use App\Services\MatterLawyerService;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->owner = User::factory()->create(['name' => 'Owner User']);
    WorkspaceMember::factory()->create([
        'user_id' => $this->owner->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->actingAs($this->owner);
    $this->owner->switchWorkspace($this->workspace);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    // Create lead lawyer
    $this->leadUser = User::factory()->create(['name' => 'Lead Lawyer']);
    WorkspaceMember::factory()->create(['user_id' => $this->leadUser->id, 'workspace_id' => $this->workspace->id]);
    LawyerProfile::factory()->create(['user_id' => $this->leadUser->id]);

    // Create supporting lawyer
    $this->supportingUser = User::factory()->create(['name' => 'Supporting Lawyer']);
    WorkspaceMember::factory()->create(['user_id' => $this->supportingUser->id, 'workspace_id' => $this->workspace->id]);
    LawyerProfile::factory()->create(['user_id' => $this->supportingUser->id]);

    // Create non-lawyer member
    $this->memberUser = User::factory()->create(['name' => 'Regular Member']);
    WorkspaceMember::factory()->create(['user_id' => $this->memberUser->id, 'workspace_id' => $this->workspace->id]);

    // Assign lawyers to matter
    $service = app(MatterLawyerService::class);
    $service->assignLawyer($this->matter, $this->leadUser, MatterLawyerRole::Lead, $this->owner);
    $service->assignLawyer($this->matter, $this->supportingUser, MatterLawyerRole::Supporting, $this->owner);

    $this->pickerService = new LawyerPickerService;
});

it('returns matter lawyers in first group with lead first', function () {
    $grouped = $this->pickerService->getGroupedOptionsForMatter($this->matter->id, $this->workspace->id);

    $matterLawyerIds = array_keys($grouped['matter_lawyers']);

    expect($grouped['matter_lawyers'])->toHaveCount(2)
        ->and($matterLawyerIds[0])->toBe($this->leadUser->id)
        ->and($matterLawyerIds[1])->toBe($this->supportingUser->id);
});

it('returns non-assigned members in second group', function () {
    $grouped = $this->pickerService->getGroupedOptionsForMatter($this->matter->id, $this->workspace->id);

    $otherIds = array_keys($grouped['other_members']);

    // Owner + regular member should be in other (not assigned to matter)
    expect($grouped['other_members'])->toHaveCount(2)
        ->and($otherIds)->toContain($this->owner->id)
        ->and($otherIds)->toContain($this->memberUser->id)
        ->and($otherIds)->not->toContain($this->leadUser->id);
});

it('returns flat list for non-matter context', function () {
    $flat = $this->pickerService->getFlatOptionsForWorkspace($this->workspace->id);

    // All 4 workspace members in flat list
    expect($flat)->toHaveCount(4);
});
