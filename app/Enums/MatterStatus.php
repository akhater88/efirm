<?php

namespace App\Enums;

enum MatterStatus: string
{
    case Active = 'active';
    case OnHold = 'on_hold';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('matters.status_active'),
            self::OnHold => __('matters.status_on_hold'),
            self::Closed => __('matters.status_closed'),
            self::Archived => __('matters.status_archived'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::OnHold => 'warning',
            self::Closed => 'gray',
            self::Archived => 'danger',
        };
    }
}
