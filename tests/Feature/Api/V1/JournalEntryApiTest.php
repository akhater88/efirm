<?php

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createJournalUser(string $role = 'owner'): array
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

it('creates a journal entry with balanced lines', function () {
    [$user, $workspace] = createJournalUser();
    $debitAccount = Account::factory()->asset()->create(['workspace_id' => $workspace->id]);
    $creditAccount = Account::factory()->revenue()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/journal-entries', [
        'entry_date' => '2026-06-22',
        'description' => 'Legal fees received',
        'lines' => [
            [
                'account_id' => $debitAccount->id,
                'debit' => 5000.00,
                'credit' => 0,
            ],
            [
                'account_id' => $creditAccount->id,
                'debit' => 0,
                'credit' => 5000.00,
            ],
        ],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.description', 'Legal fees received');
    $response->assertJsonCount(2, 'data.lines');
});

it('auto-generates entry number', function () {
    [$user, $workspace] = createJournalUser();
    $account = Account::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/journal-entries', [
        'entry_date' => '2026-06-22',
        'lines' => [
            ['account_id' => $account->id, 'debit' => 100, 'credit' => 0],
            ['account_id' => $account->id, 'debit' => 0, 'credit' => 100],
        ],
    ]);

    $response->assertCreated();
    expect($response->json('data.entry_number'))->toStartWith('JE-');
});

it('posts a balanced journal entry', function () {
    [$user, $workspace] = createJournalUser();
    $debitAccount = Account::factory()->create(['workspace_id' => $workspace->id]);
    $creditAccount = Account::factory()->create(['workspace_id' => $workspace->id]);

    // Create entry
    $createResponse = $this->actingAs($user, 'sanctum')->postJson('/api/v1/journal-entries', [
        'entry_date' => '2026-06-22',
        'lines' => [
            ['account_id' => $debitAccount->id, 'debit' => 1000, 'credit' => 0],
            ['account_id' => $creditAccount->id, 'debit' => 0, 'credit' => 1000],
        ],
    ]);

    $entryId = $createResponse->json('data.id');

    // Post it
    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/journal-entries/{$entryId}/post");

    $response->assertOk();
    $response->assertJsonPath('data.is_posted', true);
    expect($response->json('data.posted_at'))->not->toBeNull();
});

it('rejects posting an unbalanced journal entry', function () {
    [$user, $workspace] = createJournalUser();
    $debitAccount = Account::factory()->create(['workspace_id' => $workspace->id]);
    $creditAccount = Account::factory()->create(['workspace_id' => $workspace->id]);

    $createResponse = $this->actingAs($user, 'sanctum')->postJson('/api/v1/journal-entries', [
        'entry_date' => '2026-06-22',
        'lines' => [
            ['account_id' => $debitAccount->id, 'debit' => 1000, 'credit' => 0],
            ['account_id' => $creditAccount->id, 'debit' => 0, 'credit' => 500],
        ],
    ]);

    $entryId = $createResponse->json('data.id');

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/journal-entries/{$entryId}/post");

    $response->assertUnprocessable();
});

it('rejects posting an already-posted entry', function () {
    [$user, $workspace] = createJournalUser();
    $account = Account::factory()->create(['workspace_id' => $workspace->id]);

    $createResponse = $this->actingAs($user, 'sanctum')->postJson('/api/v1/journal-entries', [
        'entry_date' => '2026-06-22',
        'lines' => [
            ['account_id' => $account->id, 'debit' => 100, 'credit' => 0],
            ['account_id' => $account->id, 'debit' => 0, 'credit' => 100],
        ],
    ]);

    $entryId = $createResponse->json('data.id');

    // Post once
    $this->actingAs($user, 'sanctum')->postJson("/api/v1/journal-entries/{$entryId}/post");

    // Post again
    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/journal-entries/{$entryId}/post");

    $response->assertUnprocessable();
});

it('cannot delete a posted journal entry', function () {
    [$user, $workspace] = createJournalUser();

    $entry = JournalEntry::factory()->posted()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/journal-entries/{$entry->id}");

    $response->assertUnprocessable();
});

it('lists journal entries in current workspace only', function () {
    [$user, $workspace] = createJournalUser();
    $otherWorkspace = Workspace::factory()->create();

    JournalEntry::factory()->create(['workspace_id' => $workspace->id]);
    JournalEntry::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/journal-entries');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});
