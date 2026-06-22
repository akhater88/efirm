<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum DecisionOutcome: string
{
    case Favourable = 'favourable';
    case Adverse = 'adverse';
    case Mixed = 'mixed';
    case ProceduralOnly = 'procedural_only';

    public function label(): string
    {
        return match ($this) {
            self::Favourable => __('litigation.outcome_favourable'),
            self::Adverse => __('litigation.outcome_adverse'),
            self::Mixed => __('litigation.outcome_mixed'),
            self::ProceduralOnly => __('litigation.outcome_procedural_only'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Favourable => 'success',
            self::Adverse => 'danger',
            self::Mixed => 'warning',
            self::ProceduralOnly => 'gray',
        };
    }
}
