<?php

use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupSearchUser(): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    return [$user, $workspace];
}

it('returns grouped search results with localized labels', function () {
    [$user, $workspace] = setupSearchUser();
    Contact::factory()->create([
        'workspace_id' => $workspace->id,
        'first_name' => 'Ahmad',
        'last_name' => 'Test',
    ]);
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'title' => 'Ahmad Sale Agreement',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/search?q=Ahmad');

    $response->assertOk();
    $response->assertJsonStructure([
        'groups' => [
            '*' => ['type', 'label', 'results'],
        ],
    ]);
    expect($response->json('groups'))->toHaveCount(2);
});

it('searches contacts by display_name', function () {
    [$user, $workspace] = setupSearchUser();
    Contact::factory()->create([
        'workspace_id' => $workspace->id,
        'first_name' => 'UniqueSearchName',
        'last_name' => 'Person',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/search?q=UniqueSearchName');

    $response->assertOk();
    $groups = collect($response->json('groups'));
    $contactGroup = $groups->firstWhere('type', 'contacts');
    expect($contactGroup)->not->toBeNull();
    expect($contactGroup['results'])->toHaveCount(1);
});

it('searches matters by title', function () {
    [$user, $workspace] = setupSearchUser();
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'title' => 'UniqueSearchMatter',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/search?q=UniqueSearchMatter');

    $response->assertOk();
    $groups = collect($response->json('groups'));
    $matterGroup = $groups->firstWhere('type', 'matters');
    expect($matterGroup)->not->toBeNull();
    expect($matterGroup['results'])->toHaveCount(1);
});

it('does not return other workspace records', function () {
    [$user, $workspace] = setupSearchUser();
    $otherWorkspace = Workspace::factory()->create();
    Contact::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'first_name' => 'CrossWorkspaceContact',
        'last_name' => 'Hidden',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/search?q=CrossWorkspaceContact');

    $response->assertOk();
    expect($response->json('groups'))->toBeEmpty();
});

it('returns empty groups when no results found', function () {
    [$user, $workspace] = setupSearchUser();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/search?q=NonexistentQuery12345');

    $response->assertOk();
    expect($response->json('groups'))->toBeEmpty();
});

it('validates q parameter is required', function () {
    [$user, $workspace] = setupSearchUser();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/search');

    $response->assertUnprocessable();
});

it('respects limit parameter', function () {
    [$user, $workspace] = setupSearchUser();
    Contact::factory()->count(5)->create([
        'workspace_id' => $workspace->id,
        'first_name' => 'LimitTest',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/search?q=LimitTest&limit=2');

    $response->assertOk();
    $groups = collect($response->json('groups'));
    $contactGroup = $groups->firstWhere('type', 'contacts');
    expect($contactGroup['results'])->toHaveCount(2);
});

it('returns 401 for unauthenticated request', function () {
    $response = $this->getJson('/api/v1/search?q=test');

    $response->assertUnauthorized();
});
