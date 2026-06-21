<?php

namespace App\Enums;

enum KycChecklistStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Complete = 'complete';
    case Expired = 'expired';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => __('kyc.status_not_started'),
            self::InProgress => __('kyc.status_in_progress'),
            self::Complete => __('kyc.status_complete'),
            self::Expired => __('kyc.status_expired'),
            self::Blocked => __('kyc.status_blocked'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NotStarted => 'gray',
            self::InProgress => 'info',
            self::Complete => 'success',
            self::Expired => 'danger',
            self::Blocked => 'warning',
        };
    }
}
