<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum CourtType: string
{
    case Magistrate = 'magistrate';
    case FirstInstance = 'first_instance';
    case Appeal = 'appeal';
    case Cassation = 'cassation';
    case SpecializedCommercial = 'specialized_commercial';
    case SpecializedLabor = 'specialized_labor';
    case SpecializedFamily = 'specialized_family';
    case Administrative = 'administrative';
    case Sharia = 'sharia';
    case Arbitration = 'arbitration';

    public function label(): string
    {
        return match ($this) {
            self::Magistrate => __('litigation.court_type_magistrate'),
            self::FirstInstance => __('litigation.court_type_first_instance'),
            self::Appeal => __('litigation.court_type_appeal'),
            self::Cassation => __('litigation.court_type_cassation'),
            self::SpecializedCommercial => __('litigation.court_type_specialized_commercial'),
            self::SpecializedLabor => __('litigation.court_type_specialized_labor'),
            self::SpecializedFamily => __('litigation.court_type_specialized_family'),
            self::Administrative => __('litigation.court_type_administrative'),
            self::Sharia => __('litigation.court_type_sharia'),
            self::Arbitration => __('litigation.court_type_arbitration'),
        };
    }
}
