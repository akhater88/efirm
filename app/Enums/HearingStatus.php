<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum HearingStatus: string
{
    case Scheduled = 'scheduled';
    case Held = 'held';
    case Postponed = 'postponed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => __('litigation.hearing_status_scheduled'),
            self::Held => __('litigation.hearing_status_held'),
            self::Postponed => __('litigation.hearing_status_postponed'),
            self::Cancelled => __('litigation.hearing_status_cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'info',
            self::Held => 'success',
            self::Postponed => 'warning',
            self::Cancelled => 'gray',
        };
    }
}
