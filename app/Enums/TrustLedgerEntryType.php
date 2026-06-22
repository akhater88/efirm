<?php

namespace App\Enums;

enum TrustLedgerEntryType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => __('financial.trust_entry_deposit'),
            self::Withdrawal => __('financial.trust_entry_withdrawal'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Deposit => 'success',
            self::Withdrawal => 'danger',
        };
    }
}
