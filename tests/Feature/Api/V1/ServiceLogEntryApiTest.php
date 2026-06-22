<?php

use App\Models\Contact;
use App\Models\Matter;
use App\Models\ServiceLogEntry;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupServiceLogUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $matter = Matter::factory()->litigation()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    $servedParty = Contact::factory()->counterparty()->create(['workspace_id' => $workspace->id]);

    return [$user, $workspace, $matter, $servedParty];
}

it('creates a service log entry', function () {
    [$user, $workspace, $matter, $servedParty] = setupServiceLogUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/service-log-entries', [
        'matter_id' => $matter->id,
        'served_party_contact_id' => $servedParty->id,
        'service_method' => 'personal_service',
        'service_date' => '2026-06-15',
        'served_by_name' => 'Process Server',
        'status' => 'successful',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.service_method', 'personal_service');
    $response->assertJsonPath('data.status', 'successful');
});

it('lists service log entries filtered by matter', function () {
    [$user, $workspace, $matter, $servedParty] = setupServiceLogUser();

    ServiceLogEntry::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'served_party_contact_id' => $servedParty->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/service-log-entries?matter_id={$matter->id}");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('updates a service log entry', function () {
    [$user, $workspace, $matter, $servedParty] = setupServiceLogUser();
    $entry = ServiceLogEntry::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'served_party_contact_id' => $servedParty->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/service-log-entries/{$entry->id}", [
        'status' => 'failed_refused',
        'notes' => 'Recipient refused to accept documents',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.status', 'failed_refused');
});

it('soft-deletes a service log entry', function () {
    [$user, $workspace, $matter, $servedParty] = setupServiceLogUser('owner');
    $entry = ServiceLogEntry::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'served_party_contact_id' => $servedParty->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/service-log-entries/{$entry->id}");

    $response->assertNoContent();
    expect(ServiceLogEntry::find($entry->id))->toBeNull();
    expect(ServiceLogEntry::withTrashed()->find($entry->id))->not->toBeNull();
});

it('workspace isolation on service log entries', function () {
    [$user, $workspace, $matter, $servedParty] = setupServiceLogUser();
    $otherWorkspace = Workspace::factory()->create();
    $otherClient = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);
    $otherMatter = Matter::factory()->create(['workspace_id' => $otherWorkspace->id, 'client_id' => $otherClient->id]);
    $otherContact = Contact::factory()->create(['workspace_id' => $otherWorkspace->id]);

    ServiceLogEntry::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'served_party_contact_id' => $servedParty->id,
    ]);
    ServiceLogEntry::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'matter_id' => $otherMatter->id,
        'served_party_contact_id' => $otherContact->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/service-log-entries');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});
