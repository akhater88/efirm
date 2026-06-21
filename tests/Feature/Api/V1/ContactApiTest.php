<?php

use App\Models\Contact;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createAuthenticatedUser(string $role = 'owner'): array
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

it('lists contacts in current workspace only', function () {
    [$user, $workspace] = createAuthenticatedUser();
    $otherWorkspace = Workspace::factory()->create();

    Contact::factory()->create(['workspace_id' => $workspace->id]);
    Contact::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/contacts');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('creates a person contact with valid data', function () {
    [$user, $workspace] = createAuthenticatedUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/contacts', [
        'type' => 'person',
        'first_name' => 'Ahmad',
        'last_name' => 'Al-Masri',
        'email' => 'ahmad@example.com',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.display_name', 'Ahmad Al-Masri');
    $response->assertJsonPath('data.type', 'person');
});

it('creates an organization contact with valid data', function () {
    [$user, $workspace] = createAuthenticatedUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/contacts', [
        'type' => 'organization',
        'organization_name' => 'مكتب المحاماة',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.display_name', 'مكتب المحاماة');
    $response->assertJsonPath('data.type', 'organization');
});

it('rejects create with missing required person fields', function () {
    [$user, $workspace] = createAuthenticatedUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/contacts', [
        'type' => 'person',
        // missing first_name and last_name
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['first_name', 'last_name']);
});

it('rejects create with missing required organization fields', function () {
    [$user, $workspace] = createAuthenticatedUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/contacts', [
        'type' => 'organization',
        // missing organization_name
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('organization_name');
});

it('shows a single contact', function () {
    [$user, $workspace] = createAuthenticatedUser();
    $contact = Contact::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/contacts/{$contact->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $contact->id);
});

it('updates a contact', function () {
    [$user, $workspace] = createAuthenticatedUser();
    $contact = Contact::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/contacts/{$contact->id}", [
        'first_name' => 'Updated',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.first_name', 'Updated');
});

it('soft-deletes a contact as Owner', function () {
    [$user, $workspace] = createAuthenticatedUser('owner');
    $contact = Contact::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/contacts/{$contact->id}");

    $response->assertNoContent();
    expect(Contact::find($contact->id))->toBeNull();
    expect(Contact::withTrashed()->find($contact->id))->not->toBeNull();
});

it('denies delete to Member role', function () {
    [$user, $workspace] = createAuthenticatedUser('member');
    $contact = Contact::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/contacts/{$contact->id}");

    $response->assertForbidden();
});

it('denies access to other workspace data', function () {
    [$user, $workspace] = createAuthenticatedUser();
    $otherWorkspace = Workspace::factory()->create();
    $contact = Contact::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/contacts/{$contact->id}");

    $response->assertNotFound();
});

it('returns 401 for unauthenticated request', function () {
    $response = $this->getJson('/api/v1/contacts');

    $response->assertUnauthorized();
});

it('filters contacts by type', function () {
    [$user, $workspace] = createAuthenticatedUser();
    Contact::factory()->create(['workspace_id' => $workspace->id, 'type' => 'person']);
    Contact::factory()->organization()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/contacts?type=person');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.type', 'person');
});

it('filters contacts by is_client flag', function () {
    [$user, $workspace] = createAuthenticatedUser();
    Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    Contact::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/contacts?is_client=true');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('searches contacts by display_name', function () {
    [$user, $workspace] = createAuthenticatedUser();
    Contact::factory()->create([
        'workspace_id' => $workspace->id,
        'first_name' => 'Ahmad',
        'last_name' => 'Al-Masri',
    ]);
    Contact::factory()->create([
        'workspace_id' => $workspace->id,
        'first_name' => 'Sara',
        'last_name' => 'Hassan',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/contacts?search=Ahmad');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});
