<?php

/**
 * F-FIX-01.4 — Trust adjustment description requirement tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #6 (append-only confirmed) and #7 (mandatory description on adjustments).
 */

use App\Models\Contact;
use App\Models\TrustAccount;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\TrustAccountService;
use Illuminate\Validation\ValidationException;

function setupTrustAdjustUser(): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $trustAccount = TrustAccount::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
        'balance' => '10000.00',
    ]);

    return [$user, $workspace, $trustAccount];
}

it('rejects adjustment without description', function () {
    [, , $trustAccount] = setupTrustAdjustUser();
    $service = app(TrustAccountService::class);

    expect(fn () => $service->adjust($trustAccount, '500.00', null))
        ->toThrow(ValidationException::class);
});

it('rejects adjustment with description under 10 characters', function () {
    [, , $trustAccount] = setupTrustAdjustUser();
    $service = app(TrustAccountService::class);

    expect(fn () => $service->adjust($trustAccount, '500.00', 'too short'))
        ->toThrow(ValidationException::class);
});

it('accepts adjustment with description of 10 or more characters', function () {
    [$user, , $trustAccount] = setupTrustAdjustUser();
    $service = app(TrustAccountService::class);

    $entry = $service->adjust(
        $trustAccount,
        '500.00',
        'Correcting deposit amount recorded on wrong date — offsetting entry',
        'ADJ-001',
        $user->id,
    );

    expect($entry->type->value)->toBe('adjustment')
        ->and($entry->balance_after)->toBe('10500.00')
        ->and($trustAccount->fresh()->balance)->toBe('10500.00');
});

it('non-adjustment entries do not require description', function () {
    [$user, , $trustAccount] = setupTrustAdjustUser();
    $service = app(TrustAccountService::class);

    // Deposit without description should work fine
    $entry = $service->deposit($trustAccount, '1000.00');

    expect($entry->type->value)->toBe('deposit')
        ->and($entry->description)->toBeNull();
});
