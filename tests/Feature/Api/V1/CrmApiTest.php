<?php

use App\Enums\LeadStatus;
use App\Enums\OpportunityStatus;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Matter;
use App\Models\Opportunity;
use App\Models\Pipeline;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createCrmUser(string $role = 'owner'): array
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

// --- Pipeline tests ---

it('creates a pipeline', function () {
    [$user, $workspace] = createCrmUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/pipelines', [
        'name' => 'Sales Pipeline',
        'stages' => ['New', 'Qualified', 'Proposal', 'Won'],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Sales Pipeline');
    $response->assertJsonPath('data.stages', ['New', 'Qualified', 'Proposal', 'Won']);
});

it('lists pipelines in current workspace only', function () {
    [$user, $workspace] = createCrmUser();
    $otherWorkspace = Workspace::factory()->create();

    Pipeline::factory()->create(['workspace_id' => $workspace->id]);
    Pipeline::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/pipelines');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

// --- Lead tests ---

it('creates a lead', function () {
    [$user, $workspace] = createCrmUser();
    $contact = Contact::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/leads', [
        'title' => 'New commercial contract inquiry',
        'contact_id' => $contact->id,
        'source' => 'referral',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.title', 'New commercial contract inquiry');
    $response->assertJsonPath('data.status', 'new');
    $response->assertJsonPath('data.source', 'referral');
});

it('converts a lead to an opportunity', function () {
    [$user, $workspace] = createCrmUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    $lead = Lead::factory()->qualified()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
        'title' => 'Contract for ABC Corp',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/convert", [
        'estimated_value' => 50000.00,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.title', 'Contract for ABC Corp');
    $response->assertJsonPath('data.contact_id', $contact->id);
    $response->assertJsonPath('data.status', 'open');

    // Lead should be marked as converted
    $lead->refresh();
    expect($lead->status)->toBe(LeadStatus::Converted);
    expect($lead->converted_to_opportunity_id)->not->toBeNull();
});

it('rejects converting an already-converted lead', function () {
    [$user, $workspace] = createCrmUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    $lead = Lead::factory()->converted()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/convert");

    $response->assertUnprocessable();
});

it('lists leads in current workspace only', function () {
    [$user, $workspace] = createCrmUser();
    $otherWorkspace = Workspace::factory()->create();

    Lead::factory()->create(['workspace_id' => $workspace->id]);
    Lead::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/leads');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('filters leads by status', function () {
    [$user, $workspace] = createCrmUser();

    Lead::factory()->create(['workspace_id' => $workspace->id, 'status' => LeadStatus::New]);
    Lead::factory()->qualified()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/leads?status=qualified');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

// --- Opportunity tests ---

it('creates an opportunity', function () {
    [$user, $workspace] = createCrmUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/opportunities', [
        'title' => 'Big contract deal',
        'contact_id' => $contact->id,
        'estimated_value' => 100000.00,
        'expected_close_date' => '2026-09-01',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.title', 'Big contract deal');
    $response->assertJsonPath('data.status', 'open');
});

it('converts an opportunity to a matter', function () {
    [$user, $workspace] = createCrmUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    $opportunity = Opportunity::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
        'title' => 'Commercial deal',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/opportunities/{$opportunity->id}/convert", [
        'title' => 'Commercial Deal Matter',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.title', 'Commercial Deal Matter');
    $response->assertJsonPath('data.client_id', $contact->id);
    $response->assertJsonPath('data.status', 'active');

    // Opportunity should be marked as won
    $opportunity->refresh();
    expect($opportunity->status)->toBe(OpportunityStatus::Won);
    expect($opportunity->converted_to_matter_id)->not->toBeNull();

    // Matter should exist
    $matter = Matter::find($opportunity->converted_to_matter_id);
    expect($matter)->not->toBeNull();
    expect($matter->title)->toBe('Commercial Deal Matter');
});

it('rejects converting an already-converted opportunity', function () {
    [$user, $workspace] = createCrmUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id, 'client_id' => $contact->id]);

    $opportunity = Opportunity::factory()->won()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
        'converted_to_matter_id' => $matter->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/opportunities/{$opportunity->id}/convert");

    $response->assertUnprocessable();
});

it('lists opportunities in current workspace only', function () {
    [$user, $workspace] = createCrmUser();
    $otherWorkspace = Workspace::factory()->create();

    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $otherContact = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);

    Opportunity::factory()->create(['workspace_id' => $workspace->id, 'contact_id' => $contact->id]);
    Opportunity::factory()->create(['workspace_id' => $otherWorkspace->id, 'contact_id' => $otherContact->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/opportunities');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('denies member from deleting leads', function () {
    [$user, $workspace] = createCrmUser('member');
    $lead = Lead::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/leads/{$lead->id}");

    $response->assertForbidden();
});

it('allows member to create leads', function () {
    [$user, $workspace] = createCrmUser('member');

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/leads', [
        'title' => 'A lead from member',
    ]);

    $response->assertCreated();
});

// --- Full lead-to-matter conversion chain ---

it('converts lead to opportunity to matter (full pipeline)', function () {
    [$user, $workspace] = createCrmUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    // 1. Create lead
    $leadResponse = $this->actingAs($user, 'sanctum')->postJson('/api/v1/leads', [
        'title' => 'Full pipeline test',
        'contact_id' => $contact->id,
        'source' => 'website',
    ]);
    $leadId = $leadResponse->json('data.id');

    // 2. Convert lead to opportunity
    $oppResponse = $this->actingAs($user, 'sanctum')->postJson("/api/v1/leads/{$leadId}/convert", [
        'estimated_value' => 25000,
    ]);
    $opportunityId = $oppResponse->json('data.id');

    expect($oppResponse->json('data.status'))->toBe('open');
    expect($oppResponse->json('data.lead_id'))->toBe($leadId);

    // 3. Convert opportunity to matter
    $matterResponse = $this->actingAs($user, 'sanctum')->postJson("/api/v1/opportunities/{$opportunityId}/convert");

    $matterResponse->assertCreated();
    expect($matterResponse->json('data.client_id'))->toBe($contact->id);

    // Verify chain
    $lead = Lead::find($leadId);
    expect($lead->status)->toBe(LeadStatus::Converted);

    $opportunity = Opportunity::find($opportunityId);
    expect($opportunity->status)->toBe(OpportunityStatus::Won);
    expect($opportunity->converted_to_matter_id)->not->toBeNull();
});
