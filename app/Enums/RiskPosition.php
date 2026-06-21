<?php

namespace App\Enums;

enum RiskPosition: string
{
    case Favourable = 'favourable';
    case Balanced = 'balanced';
    case Adverse = 'adverse';

    public function label(): string
    {
        return match ($this) {
            self::Favourable => __('library.risk_favourable'),
            self::Balanced => __('library.risk_balanced'),
            self::Adverse => __('library.risk_adverse'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Favourable => 'success',
            self::Balanced => 'gray',
            self::Adverse => 'danger',
        };
    }
}
