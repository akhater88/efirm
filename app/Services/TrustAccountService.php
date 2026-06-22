<?php

namespace App\Services;

use App\Enums\TrustLedgerEntryType;
use App\Models\TrustAccount;
use App\Models\TrustLedgerEntry;
use Illuminate\Support\Facades\DB;

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
}
