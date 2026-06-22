<?php

use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupOpposingCounselUser(string $role = 'owner'): array
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

it('creates a contact flagged as opposing counsel', function () {
    [$user, $workspace] = setupOpposingCounselUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/contacts', [
        'type' => 'person',
        'first_name' => 'Ahmad',
        'last_name' => 'Opposing',
        'is_opposing_counsel' => true,
    ]);

    $response->assertCreated();
    expect($response->json('data.is_opposing_counsel'))->toBeTrue();
});

it('filters contacts by opposing counsel scope', function () {
    [$user, $workspace] = setupOpposingCounselUser();

    Contact::factory()->create(['workspace_id' => $workspace->id, 'is_opposing_counsel' => true]);
    Contact::factory()->create(['workspace_id' => $workspace->id, 'is_opposing_counsel' => false]);

    expect(Contact::opposingCounsel()->count())->toBe(1);
});

it('attaches opposing counsel to a matter counterparty', function () {
    [$user, $workspace] = setupOpposingCounselUser();
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $matter = Matter::factory()->litigation()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    $counterparty = Contact::factory()->counterparty()->create(['workspace_id' => $workspace->id]);
    $opposingCounsel = Contact::factory()->create(['workspace_id' => $workspace->id, 'is_opposing_counsel' => true]);

    $matter->counterparties()->attach($counterparty->id, [
        'representing' => 'they_represent',
        'opposing_counsel_contact_id' => $opposingCounsel->id,
    ]);

    $pivot = $matter->counterparties()->first()->pivot;
    expect($pivot->opposing_counsel_contact_id)->toBe($opposingCounsel->id);
});
