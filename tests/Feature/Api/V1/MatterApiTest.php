<?php

use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupMatterUser(string $role = 'owner'): array
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

it('lists matters in current workspace only', function () {
    [$user, $workspace, $client] = setupMatterUser();
    $otherWorkspace = Workspace::factory()->create();
    $otherClient = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);

    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    Matter::factory()->create(['workspace_id' => $otherWorkspace->id, 'client_id' => $otherClient->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/matters');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('creates a matter with valid data', function () {
    [$user, $workspace, $client] = setupMatterUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/matters', [
        'title' => 'Sale Agreement',
        'client_id' => $client->id,
        'practice_area' => 'commercial_contracts',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.title', 'Sale Agreement');
    $response->assertJsonPath('data.practice_area', 'commercial_contracts');
});

it('rejects non-client contact as client_id', function () {
    [$user, $workspace] = setupMatterUser();
    $nonClient = Contact::factory()->create(['workspace_id' => $workspace->id, 'is_client' => false]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/matters', [
        'title' => 'Test',
        'client_id' => $nonClient->id,
        'practice_area' => 'commercial_contracts',
    ]);

    $response->assertUnprocessable();
});

it('shows a single matter with relationships', function () {
    [$user, $workspace, $client] = setupMatterUser();
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/matters/{$matter->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $matter->id);
    $response->assertJsonStructure(['data' => ['client']]);
});

it('updates a matter', function () {
    [$user, $workspace, $client] = setupMatterUser();
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/matters/{$matter->id}", [
        'title' => 'Updated Title',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.title', 'Updated Title');
});

it('soft-deletes a matter as Owner', function () {
    [$user, $workspace, $client] = setupMatterUser('owner');
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/matters/{$matter->id}");

    $response->assertNoContent();
    expect(Matter::find($matter->id))->toBeNull();
    expect(Matter::withTrashed()->find($matter->id))->not->toBeNull();
});

it('denies delete to Member role', function () {
    [$user, $workspace, $client] = setupMatterUser('member');
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/matters/{$matter->id}");

    $response->assertForbidden();
});

it('attaches a counterparty to a matter', function () {
    [$user, $workspace, $client] = setupMatterUser();
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    $counterparty = Contact::factory()->counterparty()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/matters/{$matter->id}/counterparties", [
        'contact_id' => $counterparty->id,
        'representing' => 'they_represent',
    ]);

    $response->assertCreated();
    expect($matter->counterparties()->count())->toBe(1);
});

it('prevents attaching client as counterparty', function () {
    [$user, $workspace, $client] = setupMatterUser();
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/matters/{$matter->id}/counterparties", [
        'contact_id' => $client->id,
    ]);

    $response->assertUnprocessable();
});

it('detaches a counterparty from a matter', function () {
    [$user, $workspace, $client] = setupMatterUser();
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    $counterparty = Contact::factory()->counterparty()->create(['workspace_id' => $workspace->id]);
    $matter->counterparties()->attach($counterparty->id, ['representing' => 'no_counsel']);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/matters/{$matter->id}/counterparties/{$counterparty->id}");

    $response->assertNoContent();
    expect($matter->counterparties()->count())->toBe(0);
});

it('attaches a lawyer to a matter', function () {
    [$user, $workspace, $client] = setupMatterUser();
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    $lawyer = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/matters/{$matter->id}/lawyers", [
        'user_id' => $lawyer->id,
        'role' => 'associate',
    ]);

    $response->assertCreated();
    expect($matter->lawyers()->count())->toBe(1);
});

it('detaches a lawyer from a matter', function () {
    [$user, $workspace, $client] = setupMatterUser();
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    $lawyer = User::factory()->create();
    $matter->lawyers()->attach($lawyer->id, ['role' => 'reviewer']);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/matters/{$matter->id}/lawyers/{$lawyer->id}");

    $response->assertNoContent();
    expect($matter->lawyers()->count())->toBe(0);
});

it('filters matters by status', function () {
    [$user, $workspace, $client] = setupMatterUser();
    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'status' => 'active']);
    Matter::factory()->closed()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/matters?status=active');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('filters matters by practice_area', function () {
    [$user, $workspace, $client] = setupMatterUser();
    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'practice_area' => 'commercial_contracts']);
    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'practice_area' => 'ma']);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/matters?practice_area=ma');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('searches matters by title', function () {
    [$user, $workspace, $client] = setupMatterUser();
    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'title' => 'Sale Agreement']);
    Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'title' => 'Lease Contract']);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/matters?search=Sale');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('returns 401 for unauthenticated request', function () {
    $response = $this->getJson('/api/v1/matters');

    $response->assertUnauthorized();
});
