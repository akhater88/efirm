<?php

namespace App\Enums;

enum ObligationStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Overdue = 'overdue';
    case Waived = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('obligations.status_pending'),
            self::InProgress => __('obligations.status_in_progress'),
            self::Completed => __('obligations.status_completed'),
            self::Overdue => __('obligations.status_overdue'),
            self::Waived => __('obligations.status_waived'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::InProgress => 'info',
            self::Completed => 'success',
            self::Overdue => 'danger',
            self::Waived => 'gray',
        };
    }
}
