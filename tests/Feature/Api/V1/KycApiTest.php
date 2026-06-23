<?php

use App\Enums\KycChecklistStatus;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\KycChecklist;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    Sanctum::actingAs($this->user);
});

it('starts a KYC checklist for a person contact and seeds person items', function () {
    $person = Contact::factory()->create([
        'workspace_id' => $this->workspace->id,
        'type' => 'person',
    ]);

    $response = $this->postJson("/api/v1/contacts/{$person->id}/kyc/start");

    $response->assertCreated();

    $checklist = KycChecklist::where('contact_id', $person->id)->first();
    expect($checklist)->not->toBeNull()
        ->and($checklist->status)->toBe(KycChecklistStatus::InProgress)
        ->and($checklist->items)->toHaveCount(7); // 7 person items
});

it('starts a KYC checklist for an organization and seeds org items', function () {
    $org = Contact::factory()->create([
        'workspace_id' => $this->workspace->id,
        'type' => 'organization',
        'organization_name' => 'Test Org',
        'display_name' => 'Test Org',
    ]);

    $response = $this->postJson("/api/v1/contacts/{$org->id}/kyc/start");

    $response->assertCreated();

    $checklist = KycChecklist::where('contact_id', $org->id)->first();
    expect($checklist->items)->toHaveCount(7); // 7 org items (5 original + 2 from Decision #12)
});

it('prevents starting a second checklist while one is active', function () {
    $person = Contact::factory()->create([
        'workspace_id' => $this->workspace->id,
        'type' => 'person',
    ]);

    $this->postJson("/api/v1/contacts/{$person->id}/kyc/start");
    $response = $this->postJson("/api/v1/contacts/{$person->id}/kyc/start");

    $response->assertStatus(409)
        ->assertJsonPath('error', 'active_checklist_exists');
});

it('updates a KYC item status', function () {
    $person = Contact::factory()->create([
        'workspace_id' => $this->workspace->id,
        'type' => 'person',
    ]);

    $this->postJson("/api/v1/contacts/{$person->id}/kyc/start");

    $item = KycChecklist::where('contact_id', $person->id)->first()->items->first();

    $response = $this->patchJson("/api/v1/kyc-items/{$item->id}", [
        'status' => 'verified',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'verified');
});

it('recalculates checklist status when all items verified', function () {
    $person = Contact::factory()->create([
        'workspace_id' => $this->workspace->id,
        'type' => 'person',
    ]);

    $this->postJson("/api/v1/contacts/{$person->id}/kyc/start");

    $checklist = KycChecklist::where('contact_id', $person->id)->first();

    // Verify all items
    foreach ($checklist->items as $item) {
        $this->patchJson("/api/v1/kyc-items/{$item->id}", ['status' => 'verified']);
    }

    $checklist->refresh();
    expect($checklist->status)->toBe(KycChecklistStatus::Complete)
        ->and($checklist->completed_at)->not->toBeNull();
});

it('sets checklist to blocked when an item is rejected', function () {
    $person = Contact::factory()->create([
        'workspace_id' => $this->workspace->id,
        'type' => 'person',
    ]);

    $this->postJson("/api/v1/contacts/{$person->id}/kyc/start");

    $item = KycChecklist::where('contact_id', $person->id)->first()->items->first();

    $this->patchJson("/api/v1/kyc-items/{$item->id}", ['status' => 'rejected']);

    $checklist = KycChecklist::where('contact_id', $person->id)->first();
    expect($checklist->status)->toBe(KycChecklistStatus::Blocked);
});

it('reads KYC checklist for a contact', function () {
    $person = Contact::factory()->create([
        'workspace_id' => $this->workspace->id,
        'type' => 'person',
    ]);

    $this->postJson("/api/v1/contacts/{$person->id}/kyc/start");

    $response = $this->getJson("/api/v1/contacts/{$person->id}/kyc");

    $response->assertOk()
        ->assertJsonStructure(['data' => ['id', 'status', 'items']]);
});
