<?php

use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Schema;

it('can create a matter with ULID primary key', function () {
    $matter = Matter::factory()->create();

    expect($matter->id)->toHaveLength(26);
});

it('casts practice_area to PracticeArea enum', function () {
    $matter = Matter::factory()->create(['practice_area' => 'commercial_contracts']);

    expect($matter->practice_area)->toBe(PracticeArea::CommercialContracts);
});

it('casts status to MatterStatus enum', function () {
    $matter = Matter::factory()->create(['status' => 'active']);

    expect($matter->status)->toBe(MatterStatus::Active);
});

it('has client relationship', function () {
    $client = Contact::factory()->client()->create();
    $matter = Matter::factory()->create(['client_id' => $client->id, 'workspace_id' => $client->workspace_id]);

    expect($matter->client->id)->toBe($client->id);
});

it('has counterparties relationship', function () {
    $workspace = Workspace::factory()->create();
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id]);
    $counterparty = Contact::factory()->counterparty()->create(['workspace_id' => $workspace->id]);

    $matter->counterparties()->attach($counterparty->id, ['representing' => 'they_represent']);

    expect($matter->counterparties)->toHaveCount(1);
    expect($matter->counterparties->first()->pivot->representing)->toBe('they_represent');
});

it('has leadLawyer relationship', function () {
    $user = User::factory()->create();
    $matter = Matter::factory()->create(['lead_lawyer_id' => $user->id]);

    expect($matter->leadLawyer->id)->toBe($user->id);
});

it('has lawyers relationship', function () {
    $matter = Matter::factory()->create();
    $lawyer = User::factory()->create();

    $matter->lawyers()->attach($lawyer->id, ['role' => 'associate']);

    expect($matter->lawyers)->toHaveCount(1);
    expect($matter->lawyers->first()->pivot->role)->toBe('associate');
});

it('scope active returns only active matters', function () {
    $workspace = Workspace::factory()->create();
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'status' => MatterStatus::Active]);
    Matter::factory()->closed()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $workspace->id]);
    $this->actingAs($user);
    $user->switchWorkspace($workspace);

    expect(Matter::active()->count())->toBe(1);
});

it('scope byPracticeArea filters correctly', function () {
    $workspace = Workspace::factory()->create();
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'practice_area' => PracticeArea::CommercialContracts]);
    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'practice_area' => PracticeArea::MA]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $workspace->id]);
    $this->actingAs($user);
    $user->switchWorkspace($workspace);

    expect(Matter::byPracticeArea(PracticeArea::CommercialContracts)->count())->toBe(1);
});

it('is soft-deletable', function () {
    $matter = Matter::factory()->create();
    $matter->delete();

    expect($matter->trashed())->toBeTrue();
    expect(Matter::withTrashed()->find($matter->id))->not->toBeNull();
});

it('workspace isolation prevents cross-workspace access', function () {
    $ws1 = Workspace::factory()->create();
    $ws2 = Workspace::factory()->create();
    $client1 = Contact::factory()->client()->create(['workspace_id' => $ws1->id]);
    $client2 = Contact::factory()->client()->create(['workspace_id' => $ws2->id]);

    Matter::factory()->create(['workspace_id' => $ws1->id, 'client_id' => $client1->id]);
    Matter::factory()->create(['workspace_id' => $ws2->id, 'client_id' => $client2->id]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $ws1->id]);
    $this->actingAs($user);
    $user->switchWorkspace($ws1);

    expect(Matter::count())->toBe(1);
});

it('does not contain any litigation fields', function () {
    $columns = Schema::getColumnListing('matters');

    $prohibitedFields = [
        'judge_name', 'judge_id', 'court_id', 'court_type', 'court',
        'court_case_number', 'opponent_name', 'opponent_contact_id',
        'opponents_lawyer', 'representation_type', 'jurisdiction_id', 'region',
    ];

    foreach ($prohibitedFields as $field) {
        expect($columns)->not->toContain($field, "Prohibited litigation field '{$field}' found in matters table");
    }
});
