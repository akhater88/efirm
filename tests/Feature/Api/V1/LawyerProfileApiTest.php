<?php

use App\Enums\LawyerProfileStatus;
use App\Models\LawyerProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createAuthenticatedUserForLawyerTests(string $role = 'owner'): array
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

it('creates a lawyer profile for a user', function () {
    [$owner, $workspace] = createAuthenticatedUserForLawyerTests('owner');
    $targetUser = User::factory()->create();
    WorkspaceMember::factory()->member()->create([
        'user_id' => $targetUser->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($owner, 'sanctum')->postJson('/api/v1/lawyer-profiles', [
        'user_id' => $targetUser->id,
        'bar_admission_number' => 'JO-12345',
        'bar_admission_country' => 'JO',
        'practice_areas' => ['commercial_contracts'],
        'languages_spoken' => ['ar', 'en'],
        'default_hourly_rate' => '200.00',
        'default_currency' => 'USD',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.user_id', $targetUser->id);
    $response->assertJsonPath('data.bar_admission_number', 'JO-12345');
    $response->assertJsonPath('data.practice_areas', ['commercial_contracts']);
});

it('returns true for User::isLawyer() with active profile', function () {
    $user = User::factory()->create();
    LawyerProfile::factory()->create([
        'user_id' => $user->id,
        'status' => LawyerProfileStatus::Active,
    ]);

    expect($user->isLawyer())->toBeTrue();
});

it('prevents duplicate profile for same user', function () {
    [$owner, $workspace] = createAuthenticatedUserForLawyerTests('owner');
    $targetUser = User::factory()->create();
    WorkspaceMember::factory()->member()->create([
        'user_id' => $targetUser->id,
        'workspace_id' => $workspace->id,
    ]);
    LawyerProfile::factory()->create(['user_id' => $targetUser->id]);

    $response = $this->actingAs($owner, 'sanctum')->postJson('/api/v1/lawyer-profiles', [
        'user_id' => $targetUser->id,
        'bar_admission_number' => 'JO-99999',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('user_id');
});

it('allows member to update own bio but strips restricted fields', function () {
    [$member, $workspace] = createAuthenticatedUserForLawyerTests('member');
    $profile = LawyerProfile::factory()->create([
        'user_id' => $member->id,
        'bar_admission_number' => 'JO-ORIGINAL',
        'bio_en' => 'Old bio',
    ]);

    $response = $this->actingAs($member, 'sanctum')->putJson("/api/v1/lawyer-profiles/{$profile->id}", [
        'bio_en' => 'Updated bio',
        'bar_admission_number' => 'JO-HACKED',
        'default_hourly_rate' => '999.00',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.bio_en', 'Updated bio');
    // Restricted fields should remain unchanged
    $response->assertJsonPath('data.bar_admission_number', 'JO-ORIGINAL');
    expect($profile->fresh()->default_hourly_rate)->toBe('150.00');
});

it('allows owner to update any profile including bar number', function () {
    [$owner, $workspace] = createAuthenticatedUserForLawyerTests('owner');
    $targetUser = User::factory()->create();
    WorkspaceMember::factory()->member()->create([
        'user_id' => $targetUser->id,
        'workspace_id' => $workspace->id,
    ]);
    $profile = LawyerProfile::factory()->create([
        'user_id' => $targetUser->id,
        'bar_admission_number' => 'JO-OLD',
    ]);

    $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/lawyer-profiles/{$profile->id}", [
        'bar_admission_number' => 'JO-NEW',
        'default_hourly_rate' => '300.00',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.bar_admission_number', 'JO-NEW');
    expect($profile->fresh()->default_hourly_rate)->toBe('300.00');
});

it('scopes profiles to current workspace members only', function () {
    [$owner, $workspace] = createAuthenticatedUserForLawyerTests('owner');

    // User in our workspace with a profile
    $memberUser = User::factory()->create();
    WorkspaceMember::factory()->member()->create([
        'user_id' => $memberUser->id,
        'workspace_id' => $workspace->id,
    ]);
    LawyerProfile::factory()->create(['user_id' => $memberUser->id]);

    // User in another workspace with a profile
    $otherWorkspace = Workspace::factory()->create();
    $otherUser = User::factory()->create();
    WorkspaceMember::factory()->member()->create([
        'user_id' => $otherUser->id,
        'workspace_id' => $otherWorkspace->id,
    ]);
    LawyerProfile::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/v1/lawyer-profiles');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.user_id', $memberUser->id);
});
