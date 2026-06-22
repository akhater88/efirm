<?php

use App\Enums\MatterLawyerRole;
use App\Models\Matter;
use App\Models\MatterLawyer;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createAuthenticatedUserForMatterLawyerTests(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    return [$user, $workspace];
}

function createMatterWithWorkspace(Workspace $workspace, ?User $leadLawyer = null): Matter
{
    return Matter::factory()->create(array_merge(
        ['workspace_id' => $workspace->id],
        $leadLawyer ? ['lead_lawyer_id' => $leadLawyer->id] : [],
    ));
}

function createWorkspaceMemberUser(Workspace $workspace): User
{
    $user = User::factory()->create();
    WorkspaceMember::factory()->member()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    return $user;
}

// 1. Assign lead lawyer; verify single active lead
it('assigns a lead lawyer and verifies single active lead', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer = createWorkspaceMemberUser($workspace);

    $response = $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer->id,
            'role' => 'lead',
        ]);

    $response->assertCreated();
    $response->assertJsonPath('data.role', 'lead');
    $response->assertJsonPath('data.user_id', $lawyer->id);

    $activeLeads = MatterLawyer::where('matter_id', $matter->id)->lead()->count();
    expect($activeLeads)->toBe(1);
});

// 2. Change lead lawyer; verify old lead unassigned_at set, new lead row created
it('changes lead lawyer and unassigns old lead', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer1 = createWorkspaceMemberUser($workspace);
    $lawyer2 = createWorkspaceMemberUser($workspace);

    // Assign first lead
    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer1->id,
            'role' => 'lead',
        ])->assertCreated();

    // Change to second lead
    $response = $this->actingAs($owner, 'sanctum')
        ->putJson("/api/v1/matters/{$matter->id}/lead-lawyer", [
            'user_id' => $lawyer2->id,
        ]);

    $response->assertOk();
    $response->assertJsonPath('data.user_id', $lawyer2->id);

    // Old lead should have unassigned_at set
    $oldAssignment = MatterLawyer::where('matter_id', $matter->id)
        ->where('user_id', $lawyer1->id)
        ->latest('id')
        ->first();
    expect($oldAssignment->unassigned_at)->not->toBeNull();

    // New lead is active
    $newLead = MatterLawyer::where('matter_id', $matter->id)->lead()->first();
    expect($newLead->user_id)->toBe($lawyer2->id);
});

// 3. Assign supporting lawyers; multiple allowed
it('assigns multiple supporting lawyers', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer1 = createWorkspaceMemberUser($workspace);
    $lawyer2 = createWorkspaceMemberUser($workspace);

    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer1->id,
            'role' => 'supporting',
        ])->assertCreated();

    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer2->id,
            'role' => 'supporting',
        ])->assertCreated();

    $supportingCount = MatterLawyer::where('matter_id', $matter->id)
        ->where('role', MatterLawyerRole::Supporting)
        ->active()
        ->count();
    expect($supportingCount)->toBe(2);
});

// 4. Unassign supporting lawyer; listing excludes them
it('unassigns a supporting lawyer and excludes from listing', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer = createWorkspaceMemberUser($workspace);

    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer->id,
            'role' => 'supporting',
        ])->assertCreated();

    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/v1/matters/{$matter->id}/lawyer-assignments/{$lawyer->id}")
        ->assertNoContent();

    $response = $this->actingAs($owner, 'sanctum')
        ->getJson("/api/v1/matters/{$matter->id}/lawyer-assignments");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(0);
});

// 5. Cannot assign duplicate (same user already active on matter)
it('prevents duplicate assignment of same user', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer = createWorkspaceMemberUser($workspace);

    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer->id,
            'role' => 'supporting',
        ])->assertCreated();

    $response = $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer->id,
            'role' => 'supporting',
        ]);

    $response->assertUnprocessable();
    $response->assertJsonFragment(['message' => __('lawyers.already_assigned')]);
});

// 6. Workspace isolation
it('enforces workspace isolation for matter lawyer assignments', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer = createWorkspaceMemberUser($workspace);

    // Assign a lawyer in workspace 1
    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer->id,
            'role' => 'lead',
        ])->assertCreated();

    // Create a different workspace and user
    $otherUser = User::factory()->create();
    $otherWorkspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $otherUser->id,
        'workspace_id' => $otherWorkspace->id,
    ]);
    $otherUser->switchWorkspace($otherWorkspace);

    // Other workspace user cannot access the matter (global scope hides it → 404)
    $response = $this->actingAs($otherUser, 'sanctum')
        ->getJson("/api/v1/matters/{$matter->id}/lawyer-assignments");

    $response->assertNotFound();
});

// 7. Matter.lead_lawyer_id stays in sync with active lead
it('keeps matter lead_lawyer_id in sync with active lead', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer = createWorkspaceMemberUser($workspace);

    // Assign lead
    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer->id,
            'role' => 'lead',
        ])->assertCreated();

    expect($matter->fresh()->lead_lawyer_id)->toBe($lawyer->id);

    // Unassign lead
    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/v1/matters/{$matter->id}/lawyer-assignments/{$lawyer->id}")
        ->assertNoContent();

    expect($matter->fresh()->lead_lawyer_id)->toBeNull();
});

// 8. Backward compat — Matter without any assignments works in API
it('handles matter without any lawyer assignments', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);

    $response = $this->actingAs($owner, 'sanctum')
        ->getJson("/api/v1/matters/{$matter->id}/lawyer-assignments");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(0);
});

// 9. List lawyers includes active only by default
it('lists only active lawyers by default', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer1 = createWorkspaceMemberUser($workspace);
    $lawyer2 = createWorkspaceMemberUser($workspace);

    // Assign and then unassign lawyer1
    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer1->id,
            'role' => 'supporting',
        ])->assertCreated();

    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/v1/matters/{$matter->id}/lawyer-assignments/{$lawyer1->id}")
        ->assertNoContent();

    // Assign lawyer2 (still active)
    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer2->id,
            'role' => 'supporting',
        ])->assertCreated();

    $response = $this->actingAs($owner, 'sanctum')
        ->getJson("/api/v1/matters/{$matter->id}/lawyer-assignments");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.user_id'))->toBe($lawyer2->id);
});

// 10. List lawyers with include_history=true shows unassigned
it('lists all lawyers including history when include_history is true', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer1 = createWorkspaceMemberUser($workspace);
    $lawyer2 = createWorkspaceMemberUser($workspace);

    // Assign and unassign lawyer1
    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer1->id,
            'role' => 'supporting',
        ])->assertCreated();

    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/v1/matters/{$matter->id}/lawyer-assignments/{$lawyer1->id}")
        ->assertNoContent();

    // Assign lawyer2
    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer2->id,
            'role' => 'supporting',
        ])->assertCreated();

    $response = $this->actingAs($owner, 'sanctum')
        ->getJson("/api/v1/matters/{$matter->id}/lawyer-assignments?include_history=true");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

// 11. Changing lead keeps old assignment as history
it('keeps old lead assignment as history when changing lead', function () {
    [$owner, $workspace] = createAuthenticatedUserForMatterLawyerTests('owner');
    $matter = createMatterWithWorkspace($workspace);
    $lawyer1 = createWorkspaceMemberUser($workspace);
    $lawyer2 = createWorkspaceMemberUser($workspace);

    // Assign first lead
    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/matters/{$matter->id}/lawyer-assignments", [
            'user_id' => $lawyer1->id,
            'role' => 'lead',
        ])->assertCreated();

    // Change lead
    $this->actingAs($owner, 'sanctum')
        ->putJson("/api/v1/matters/{$matter->id}/lead-lawyer", [
            'user_id' => $lawyer2->id,
        ])->assertOk();

    // Both assignments exist in history
    $allAssignments = MatterLawyer::where('matter_id', $matter->id)->count();
    expect($allAssignments)->toBe(2);

    // Only one active lead
    $activeLeads = MatterLawyer::where('matter_id', $matter->id)->lead()->count();
    expect($activeLeads)->toBe(1);

    // The old assignment has unassigned_at and the lead role preserved
    $oldAssignment = MatterLawyer::where('matter_id', $matter->id)
        ->where('user_id', $lawyer1->id)
        ->first();
    expect($oldAssignment->role)->toBe(MatterLawyerRole::Lead);
    expect($oldAssignment->unassigned_at)->not->toBeNull();
});
