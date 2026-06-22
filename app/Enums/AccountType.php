<?php

namespace App\Enums;

enum AccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Asset => __('financial.account_type_asset'),
            self::Liability => __('financial.account_type_liability'),
            self::Equity => __('financial.account_type_equity'),
            self::Revenue => __('financial.account_type_revenue'),
            self::Expense => __('financial.account_type_expense'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Asset => 'info',
            self::Liability => 'danger',
            self::Equity => 'success',
            self::Revenue => 'success',
            self::Expense => 'warning',
        };
    }
}
