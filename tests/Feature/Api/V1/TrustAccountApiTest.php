<?php

use App\Models\Contact;
use App\Models\TrustAccount;
use App\Models\TrustLedgerEntry;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createTrustUser(string $role = 'owner'): array
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

it('creates a trust account', function () {
    [$user, $workspace] = createTrustUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/trust-accounts', [
        'contact_id' => $contact->id,
        'name' => 'Client Trust Fund',
        'bank_name' => 'Arab Bank',
        'currency' => 'JOD',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Client Trust Fund');
    $response->assertJsonPath('data.balance', '0.00');
});

it('deposits funds into trust account', function () {
    [$user, $workspace] = createTrustUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $trustAccount = TrustAccount::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
        'balance' => '0.00',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/trust-accounts/{$trustAccount->id}/deposit", [
        'amount' => 5000.00,
        'description' => 'Retainer deposit',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.type', 'deposit');
    $response->assertJsonPath('data.amount', '5000.00');
    $response->assertJsonPath('data.balance_after', '5000.00');

    expect($trustAccount->fresh()->balance)->toBe('5000.00');
});

it('withdraws funds from trust account', function () {
    [$user, $workspace] = createTrustUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $trustAccount = TrustAccount::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
        'balance' => '10000.00',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/trust-accounts/{$trustAccount->id}/withdraw", [
        'amount' => 3000.00,
        'description' => 'Legal fees disbursement',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.type', 'withdrawal');
    $response->assertJsonPath('data.amount', '3000.00');
    $response->assertJsonPath('data.balance_after', '7000.00');

    expect($trustAccount->fresh()->balance)->toBe('7000.00');
});

it('rejects withdrawal exceeding balance', function () {
    [$user, $workspace] = createTrustUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $trustAccount = TrustAccount::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
        'balance' => '1000.00',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/trust-accounts/{$trustAccount->id}/withdraw", [
        'amount' => 5000.00,
        'description' => 'Too much',
    ]);

    $response->assertUnprocessable();
});

it('blocks update on trust ledger entry (append-only)', function () {
    [$user, $workspace] = createTrustUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $trustAccount = TrustAccount::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
    ]);

    // Create entry via deposit
    $this->actingAs($user, 'sanctum')->postJson("/api/v1/trust-accounts/{$trustAccount->id}/deposit", [
        'amount' => 1000.00,
    ]);

    $entry = TrustLedgerEntry::first();

    expect(fn () => $entry->update(['amount' => '999.00']))
        ->toThrow(LogicException::class);
});

it('blocks delete on trust ledger entry (append-only)', function () {
    [$user, $workspace] = createTrustUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $trustAccount = TrustAccount::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
    ]);

    $this->actingAs($user, 'sanctum')->postJson("/api/v1/trust-accounts/{$trustAccount->id}/deposit", [
        'amount' => 1000.00,
    ]);

    $entry = TrustLedgerEntry::first();

    expect(fn () => $entry->delete())
        ->toThrow(LogicException::class);
});

it('lists trust accounts in current workspace only', function () {
    [$user, $workspace] = createTrustUser();
    $otherWorkspace = Workspace::factory()->create();

    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $otherContact = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);

    TrustAccount::factory()->create(['workspace_id' => $workspace->id, 'contact_id' => $contact->id]);
    TrustAccount::factory()->create(['workspace_id' => $otherWorkspace->id, 'contact_id' => $otherContact->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/trust-accounts');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});
