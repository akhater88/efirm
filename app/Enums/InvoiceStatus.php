<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('financial.invoice_status_draft'),
            self::Sent => __('financial.invoice_status_sent'),
            self::Paid => __('financial.invoice_status_paid'),
            self::PartiallyPaid => __('financial.invoice_status_partially_paid'),
            self::Overdue => __('financial.invoice_status_overdue'),
            self::Cancelled => __('financial.invoice_status_cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'info',
            self::Paid => 'success',
            self::PartiallyPaid => 'warning',
            self::Overdue => 'danger',
            self::Cancelled => 'gray',
        };
    }
}
