<?php

use App\Enums\LitigationStatus;
use App\Enums\RepresentationRole;
use App\Models\Contact;
use App\Models\Court;
use App\Models\Judge;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupLitigationUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    return [$user, $workspace, $client];
}

it('creates a commercial matter without litigation fields', function () {
    [$user, $workspace, $client] = setupLitigationUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/matters', [
        'title' => 'Sale Agreement',
        'client_id' => $client->id,
        'practice_area' => 'commercial_contracts',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.is_litigation', false);
    $response->assertJsonPath('data.litigation_status', null);
    $response->assertJsonPath('data.court_id', null);
});

it('creates a litigation matter with all litigation fields', function () {
    [$user, $workspace, $client] = setupLitigationUser();
    $court = Court::factory()->create(['workspace_id' => $workspace->id]);
    $judge = Judge::factory()->create(['workspace_id' => $workspace->id, 'court_id' => $court->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/matters', [
        'title' => 'Labor Dispute',
        'client_id' => $client->id,
        'practice_area' => 'commercial_contracts',
        'is_litigation' => true,
        'court_id' => $court->id,
        'judge_id' => $judge->id,
        'court_case_number' => 'CC-2026-1234',
        'case_number_internal' => 'INT-001',
        'litigation_status' => 'filed',
        'filed_date' => '2026-06-01',
        'representation_role' => 'plaintiff',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.is_litigation', true);
    $response->assertJsonPath('data.litigation_status', 'filed');
    $response->assertJsonPath('data.court_case_number', 'CC-2026-1234');
    $response->assertJsonPath('data.representation_role', 'plaintiff');
});

it('returns litigation scoped matters', function () {
    [$user, $workspace, $client] = setupLitigationUser();

    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'is_litigation' => false]);
    Matter::factory()->litigation()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    // Verify scopes work at model level
    expect(Matter::litigation()->count())->toBe(1);
    expect(Matter::commercial()->count())->toBe(1);
});

it('existing commercial matters remain unaffected by litigation migration', function () {
    [$user, $workspace, $client] = setupLitigationUser();

    $matter = Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
    ]);

    $fresh = $matter->fresh();
    expect($fresh->is_litigation)->toBeFalse();
    expect($fresh->court_id)->toBeNull();
    expect($fresh->judge_id)->toBeNull();
    expect($fresh->litigation_status)->toBeNull();
    expect($fresh->representation_role)->toBeNull();
});

it('casts litigation enums correctly', function () {
    [$user, $workspace, $client] = setupLitigationUser();

    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
    ]);

    expect($matter->litigation_status)->toBeInstanceOf(LitigationStatus::class);
    expect($matter->representation_role)->toBeInstanceOf(RepresentationRole::class);
});
