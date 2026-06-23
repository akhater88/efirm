<?php

namespace App\Services;

use App\Enums\TrustLedgerEntryType;
use App\Models\TrustAccount;
use App\Models\TrustLedgerEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Trust account financial operations service.
 *
 * Adjustment description requirement per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #6 (append-only confirmed) and #7 (mandatory description on adjustments).
 */
class TrustAccountService
{
    /**
     * Deposit funds into a trust account.
     *
     * @return TrustLedgerEntry The created ledger entry
     */
    public function deposit(
        TrustAccount $trustAccount,
        string $amount,
        ?string $description = null,
        ?string $reference = null,
        ?string $userId = null,
    ): TrustLedgerEntry {
        if (bccomp($amount, '0', 2) <= 0) {
            throw new \InvalidArgumentException(__('financial.trust_amount_must_be_positive'));
        }

        return DB::transaction(function () use ($trustAccount, $amount, $description, $reference, $userId) {
            // Lock the trust account row for update
            $trustAccount = TrustAccount::query()
                ->withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($trustAccount->id);

            $newBalance = bcadd($trustAccount->balance, $amount, 2);

            $trustAccount->update(['balance' => $newBalance]);

            return TrustLedgerEntry::create([
                'workspace_id' => $trustAccount->workspace_id,
                'trust_account_id' => $trustAccount->id,
                'type' => TrustLedgerEntryType::Deposit,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'reference' => $reference,
                'created_by_user_id' => $userId,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Withdraw funds from a trust account.
     *
     * @return TrustLedgerEntry The created ledger entry
     */
    public function withdraw(
        TrustAccount $trustAccount,
        string $amount,
        ?string $description = null,
        ?string $reference = null,
        ?string $userId = null,
    ): TrustLedgerEntry {
        if (bccomp($amount, '0', 2) <= 0) {
            throw new \InvalidArgumentException(__('financial.trust_amount_must_be_positive'));
        }

        return DB::transaction(function () use ($trustAccount, $amount, $description, $reference, $userId) {
            // Lock the trust account row for update
            $trustAccount = TrustAccount::query()
                ->withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($trustAccount->id);

            if (bccomp($trustAccount->balance, $amount, 2) < 0) {
                throw new \LogicException(__('financial.trust_insufficient_balance'));
            }

            $newBalance = bcsub($trustAccount->balance, $amount, 2);

            $trustAccount->update(['balance' => $newBalance]);

            return TrustLedgerEntry::create([
                'workspace_id' => $trustAccount->workspace_id,
                'trust_account_id' => $trustAccount->id,
                'type' => TrustLedgerEntryType::Withdrawal,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'reference' => $reference,
                'created_by_user_id' => $userId,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Create an adjustment (offsetting) entry on a trust account.
     *
     * Per Decision #7: adjustments require a mandatory description of at least 10 characters
     * explaining why the correction was made (قيد تسوية / عكسي).
     *
     * @param  string  $amount  Positive = credit adjustment, negative = debit adjustment
     * @return TrustLedgerEntry The created ledger entry
     *
     * @throws ValidationException If description is missing or too short
     */
    public function adjust(
        TrustAccount $trustAccount,
        string $amount,
        ?string $description = null,
        ?string $reference = null,
        ?string $userId = null,
    ): TrustLedgerEntry {
        // Decision #7: mandatory description with minimum 10 characters for adjustments
        if (empty($description) || mb_strlen(trim($description)) < 10) {
            throw ValidationException::withMessages([
                'description' => [__('financial.trust_adjustment_description_required')],
            ]);
        }

        if (bccomp($amount, '0', 2) === 0) {
            throw new \InvalidArgumentException(__('financial.trust_adjustment_amount_non_zero'));
        }

        return DB::transaction(function () use ($trustAccount, $amount, $description, $reference, $userId) {
            $trustAccount = TrustAccount::query()
                ->withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($trustAccount->id);

            $newBalance = bcadd($trustAccount->balance, $amount, 2);

            if (bccomp($newBalance, '0', 2) < 0) {
                throw new \LogicException(__('financial.trust_insufficient_balance'));
            }

            $trustAccount->update(['balance' => $newBalance]);

            return TrustLedgerEntry::create([
                'workspace_id' => $trustAccount->workspace_id,
                'trust_account_id' => $trustAccount->id,
                'type' => TrustLedgerEntryType::Adjustment,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'reference' => $reference,
                'created_by_user_id' => $userId,
                'created_at' => now(),
            ]);
        });
    }
}
