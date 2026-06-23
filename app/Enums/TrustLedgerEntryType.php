<?php

namespace App\Enums;

/**
 * Trust ledger entry types.
 *
 * Adjustment type added per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #6 (append-only confirmed) and #7 (offsetting entries with mandatory description).
 */
enum TrustLedgerEntryType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Adjustment = 'adjustment'; // Decision #7 — قيد تسوية / عكسي — requires description min 10 chars

    public function label(): string
    {
        return match ($this) {
            self::Deposit => __('financial.trust_entry_deposit'),
            self::Withdrawal => __('financial.trust_entry_withdrawal'),
            self::Adjustment => __('financial.trust_entry_adjustment'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Deposit => 'success',
            self::Withdrawal => 'danger',
            self::Adjustment => 'warning',
        };
    }
}
