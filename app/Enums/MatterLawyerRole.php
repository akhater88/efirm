<?php

namespace App\Enums;

enum MatterLawyerRole: string
{
    case Lead = 'lead';
    case Supporting = 'supporting';

    public function label(): string
    {
        return match ($this) {
            self::Lead => __('lawyers.lead_lawyer'),
            self::Supporting => __('lawyers.supporting_lawyer'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Lead => 'primary',
            self::Supporting => 'info',
        };
    }
}
