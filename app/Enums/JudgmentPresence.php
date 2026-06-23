<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum JudgmentPresence: string
{
    case Wijahi = 'wijahi';
    case MithlaWijahi = 'mithla_wijahi';
    case Ghyabi = 'ghyabi';

    public function label(): string
    {
        return match ($this) {
            self::Wijahi => __('litigation.judgment_wijahi'),
            self::MithlaWijahi => __('litigation.judgment_mithla_wijahi'),
            self::Ghyabi => __('litigation.judgment_ghyabi'),
        };
    }
}
