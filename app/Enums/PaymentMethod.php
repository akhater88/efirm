<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Check = 'check';
    case CreditCard = 'credit_card';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('financial.payment_method_cash'),
            self::BankTransfer => __('financial.payment_method_bank_transfer'),
            self::Check => __('financial.payment_method_check'),
            self::CreditCard => __('financial.payment_method_credit_card'),
            self::Other => __('financial.payment_method_other'),
        };
    }
}
