<?php

use App\Models\Account;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createFinancialUser(string $role = 'owner'): array
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

it('lists accounts in current workspace only', function () {
    [$user, $workspace] = createFinancialUser();
    $otherWorkspace = Workspace::factory()->create();

    Account::factory()->create(['workspace_id' => $workspace->id]);
    Account::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/accounts');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('creates an account with valid data', function () {
    [$user, $workspace] = createFinancialUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/accounts', [
        'code' => '1001',
        'name' => 'Cash',
        'account_type' => 'asset',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.code', '1001');
    $response->assertJsonPath('data.name', 'Cash');
    $response->assertJsonPath('data.account_type', 'asset');
});

it('rejects duplicate account code in same workspace', function () {
    [$user, $workspace] = createFinancialUser();

    Account::factory()->create(['workspace_id' => $workspace->id, 'code' => '1001']);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/accounts', [
        'code' => '1001',
        'name' => 'Duplicate',
        'account_type' => 'asset',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('code');
});

it('shows a single account', function () {
    [$user, $workspace] = createFinancialUser();
    $account = Account::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/accounts/{$account->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $account->id);
});

it('updates an account', function () {
    [$user, $workspace] = createFinancialUser();
    $account = Account::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/accounts/{$account->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Updated Name');
});

it('soft-deletes a non-system account', function () {
    [$user, $workspace] = createFinancialUser();
    $account = Account::factory()->create(['workspace_id' => $workspace->id, 'is_system' => false]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/accounts/{$account->id}");

    $response->assertNoContent();
    expect(Account::find($account->id))->toBeNull();
    expect(Account::withTrashed()->find($account->id))->not->toBeNull();
});

it('cannot delete a system account', function () {
    [$user, $workspace] = createFinancialUser();
    $account = Account::factory()->system()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/accounts/{$account->id}");

    $response->assertStatus(500);
    expect(Account::find($account->id))->not->toBeNull();
});

it('creates accounts with parent-child tree structure', function () {
    [$user, $workspace] = createFinancialUser();

    $parent = Account::factory()->create([
        'workspace_id' => $workspace->id,
        'code' => '1000',
        'name' => 'Assets',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/accounts', [
        'parent_id' => $parent->id,
        'code' => '1001',
        'name' => 'Cash',
        'account_type' => 'asset',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.parent_id', $parent->id);
});

it('denies access to other workspace data', function () {
    [$user, $workspace] = createFinancialUser();
    $otherWorkspace = Workspace::factory()->create();
    $account = Account::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/accounts/{$account->id}");

    $response->assertNotFound();
});

it('denies member role from creating accounts', function () {
    [$user, $workspace] = createFinancialUser('member');

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/accounts', [
        'code' => '1001',
        'name' => 'Cash',
        'account_type' => 'asset',
    ]);

    $response->assertForbidden();
});

it('filters accounts by account_type', function () {
    [$user, $workspace] = createFinancialUser();
    Account::factory()->asset()->create(['workspace_id' => $workspace->id]);
    Account::factory()->expense()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/accounts?account_type=asset');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});
