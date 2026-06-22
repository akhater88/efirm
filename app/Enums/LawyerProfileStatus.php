<?php

namespace App\Enums;

enum LawyerProfileStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case OnLeave = 'on_leave';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('lawyers.status_active'),
            self::Inactive => __('lawyers.status_inactive'),
            self::OnLeave => __('lawyers.status_on_leave'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'gray',
            self::OnLeave => 'warning',
        };
    }
}
