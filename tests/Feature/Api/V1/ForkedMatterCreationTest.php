<?php

use App\Enums\MatterTypeEnum;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupForkedMatterUser(string $role = 'owner'): array
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

it('creates a matter with commercial_contracts type', function () {
    [$user, $workspace, $client] = setupForkedMatterUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/matters', [
        'title' => 'Sale Agreement',
        'client_id' => $client->id,
        'practice_area' => 'commercial_contracts',
        'matter_type' => 'commercial_contracts',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.matter_type', 'commercial_contracts');
});

it('creates a matter with commercial_litigation type', function () {
    [$user, $workspace, $client] = setupForkedMatterUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/matters', [
        'title' => 'Dispute Resolution',
        'client_id' => $client->id,
        'practice_area' => 'commercial_contracts',
        'matter_type' => 'commercial_litigation',
        'is_litigation' => true,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.matter_type', 'commercial_litigation');
});

it('returns correct values for isTransactional on enum', function () {
    expect(MatterTypeEnum::CommercialContracts->isTransactional())->toBeTrue();
    expect(MatterTypeEnum::MnA->isTransactional())->toBeTrue();
    expect(MatterTypeEnum::CorporateGovernance->isTransactional())->toBeTrue();
    expect(MatterTypeEnum::Securities->isTransactional())->toBeTrue();
    expect(MatterTypeEnum::GeneralCounsel->isTransactional())->toBeTrue();
    expect(MatterTypeEnum::Advisory->isTransactional())->toBeTrue();
    expect(MatterTypeEnum::RealEstateTransaction->isTransactional())->toBeTrue();
    expect(MatterTypeEnum::EmploymentDrafting->isTransactional())->toBeTrue();

    expect(MatterTypeEnum::CommercialLitigation->isTransactional())->toBeFalse();
    expect(MatterTypeEnum::Arbitration->isTransactional())->toBeFalse();
});

it('returns correct values for isLitigation on enum', function () {
    expect(MatterTypeEnum::CommercialLitigation->isLitigation())->toBeTrue();
    expect(MatterTypeEnum::CivilLitigation->isLitigation())->toBeTrue();
    expect(MatterTypeEnum::Enforcement->isLitigation())->toBeTrue();
    expect(MatterTypeEnum::Arbitration->isLitigation())->toBeTrue();
    expect(MatterTypeEnum::LaborDispute->isLitigation())->toBeTrue();
    expect(MatterTypeEnum::AdministrativeDispute->isLitigation())->toBeTrue();

    expect(MatterTypeEnum::CommercialContracts->isLitigation())->toBeFalse();
    expect(MatterTypeEnum::MnA->isLitigation())->toBeFalse();
});

it('allows existing matters with null matter_type for backward compat', function () {
    [$user, $workspace, $client] = setupForkedMatterUser();

    $matter = Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'matter_type' => null,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/matters/{$matter->id}");

    $response->assertOk();
    $response->assertJsonPath('data.matter_type', null);
});

it('backfills is_litigation=true matters to commercial_litigation', function () {
    [$user, $workspace, $client] = setupForkedMatterUser();

    $matter = Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'is_litigation' => true,
        'matter_type' => null,
    ]);

    // Simulate the backfill logic
    Matter::withoutGlobalScopes()
        ->whereNull('matter_type')
        ->each(function (Matter $m) {
            $type = $m->is_litigation
                ? 'commercial_litigation'
                : 'commercial_contracts';
            $m->updateQuietly(['matter_type' => $type]);
        });

    expect($matter->fresh()->matter_type)->toBe(MatterTypeEnum::CommercialLitigation);
});

it('backfills is_litigation=false matters to commercial_contracts', function () {
    [$user, $workspace, $client] = setupForkedMatterUser();

    $matter = Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'is_litigation' => false,
        'matter_type' => null,
    ]);

    // Simulate the backfill logic
    Matter::withoutGlobalScopes()
        ->whereNull('matter_type')
        ->each(function (Matter $m) {
            $type = $m->is_litigation
                ? 'commercial_litigation'
                : 'commercial_contracts';
            $m->updateQuietly(['matter_type' => $type]);
        });

    expect($matter->fresh()->matter_type)->toBe(MatterTypeEnum::CommercialContracts);
});

it('returns grouped enum from matter types endpoint', function () {
    [$user] = setupForkedMatterUser();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/matters/types');

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            'transactional' => [['value', 'label']],
            'litigation' => [['value', 'label']],
        ],
    ]);

    $data = $response->json('data');
    expect(count($data['transactional']))->toBe(8);
    expect(count($data['litigation']))->toBe(6);
});

it('filters matters using transactional scope', function () {
    [$user, $workspace, $client] = setupForkedMatterUser();

    Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'matter_type' => MatterTypeEnum::CommercialContracts,
    ]);

    Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'matter_type' => MatterTypeEnum::MnA,
    ]);

    Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'matter_type' => MatterTypeEnum::CommercialLitigation,
    ]);

    $user->switchWorkspace($workspace);
    $transactional = Matter::transactional()->get();
    $this->assertCount(2, $transactional);
});

it('filters matters using litigation scope', function () {
    [$user, $workspace, $client] = setupForkedMatterUser();

    Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'matter_type' => MatterTypeEnum::CommercialContracts,
    ]);

    Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'matter_type' => MatterTypeEnum::Arbitration,
    ]);

    Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'matter_type' => MatterTypeEnum::CommercialLitigation,
    ]);

    $user->switchWorkspace($workspace);
    $litigation = Matter::litigation()->get();
    $this->assertCount(2, $litigation);
});
