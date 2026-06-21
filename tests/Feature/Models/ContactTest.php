<?php

use App\Models\Contact;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('can create a contact with ULID primary key', function () {
    $contact = Contact::factory()->create();

    expect($contact->id)->toHaveLength(26);
});

it('uses BelongsToWorkspace trait', function () {
    $workspace = Workspace::factory()->create();
    $contact = Contact::factory()->create(['workspace_id' => $workspace->id]);

    expect($contact->workspace->id)->toBe($workspace->id);
});

it('computes display_name for person type', function () {
    $contact = Contact::factory()->create([
        'type' => 'person',
        'first_name' => 'Ahmad',
        'middle_name' => 'Hassan',
        'last_name' => 'Al-Masri',
    ]);

    expect($contact->display_name)->toBe('Ahmad Hassan Al-Masri');
});

it('computes display_name for organization type', function () {
    $contact = Contact::factory()->organization()->create([
        'organization_name' => 'مكتب المحاماة',
    ]);

    expect($contact->display_name)->toBe('مكتب المحاماة');
});

it('has parentOrganization relationship', function () {
    $org = Contact::factory()->organization()->create();
    $person = Contact::factory()->create(['parent_organization_id' => $org->id, 'workspace_id' => $org->workspace_id]);

    expect($person->parentOrganization->id)->toBe($org->id);
});

it('has peopleInOrganization relationship', function () {
    $org = Contact::factory()->organization()->create();
    Contact::factory()->count(3)->create([
        'parent_organization_id' => $org->id,
        'workspace_id' => $org->workspace_id,
    ]);

    expect($org->peopleInOrganization)->toHaveCount(3);
});

it('scope person returns only persons', function () {
    $workspace = Workspace::factory()->create();
    Contact::factory()->create(['workspace_id' => $workspace->id, 'type' => 'person']);
    Contact::factory()->organization()->create(['workspace_id' => $workspace->id]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $workspace->id]);
    $this->actingAs($user);
    $user->switchWorkspace($workspace);

    expect(Contact::person()->count())->toBe(1);
});

it('scope organization returns only organizations', function () {
    $workspace = Workspace::factory()->create();
    Contact::factory()->create(['workspace_id' => $workspace->id, 'type' => 'person']);
    Contact::factory()->organization()->create(['workspace_id' => $workspace->id]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $workspace->id]);
    $this->actingAs($user);
    $user->switchWorkspace($workspace);

    expect(Contact::organization()->count())->toBe(1);
});

it('scope client returns only clients', function () {
    $workspace = Workspace::factory()->create();
    Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    Contact::factory()->create(['workspace_id' => $workspace->id]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $workspace->id]);
    $this->actingAs($user);
    $user->switchWorkspace($workspace);

    expect(Contact::client()->count())->toBe(1);
});

it('scope counterparty returns only counterparties', function () {
    $workspace = Workspace::factory()->create();
    Contact::factory()->counterparty()->create(['workspace_id' => $workspace->id]);
    Contact::factory()->create(['workspace_id' => $workspace->id]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $workspace->id]);
    $this->actingAs($user);
    $user->switchWorkspace($workspace);

    expect(Contact::counterparty()->count())->toBe(1);
});

it('is soft-deletable', function () {
    $contact = Contact::factory()->create();
    $contact->delete();

    expect($contact->trashed())->toBeTrue();
    expect(Contact::withTrashed()->find($contact->id))->not->toBeNull();
});

it('casts labels to array', function () {
    $contact = Contact::factory()->create(['labels' => ['vip', 'corporate']]);

    expect($contact->labels)->toBe(['vip', 'corporate']);
});

it('casts is_client and is_counterparty to boolean', function () {
    $contact = Contact::factory()->client()->counterparty()->create();

    expect($contact->is_client)->toBeTrue();
    expect($contact->is_counterparty)->toBeTrue();
});

it('workspace isolation prevents cross-workspace access', function () {
    $ws1 = Workspace::factory()->create();
    $ws2 = Workspace::factory()->create();

    Contact::factory()->create(['workspace_id' => $ws1->id]);
    Contact::factory()->create(['workspace_id' => $ws2->id]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $ws1->id]);
    $this->actingAs($user);
    $user->switchWorkspace($ws1);

    expect(Contact::count())->toBe(1);
});
