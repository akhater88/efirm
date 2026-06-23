<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum CourtLevel: string
{
    case Magistrate = 'magistrate';
    case FirstInstance = 'first_instance';
    case Appeal = 'appeal';
    case Cassation = 'cassation';
    case SpecializedCommercial = 'specialized_commercial';
    case SpecializedLabor = 'specialized_labor';
    case Administrative = 'administrative';
    case Sharia = 'sharia';
    case Arbitration = 'arbitration';

    public function label(): string
    {
        return match ($this) {
            self::Magistrate => __('litigation.court_level_magistrate'),
            self::FirstInstance => __('litigation.court_level_first_instance'),
            self::Appeal => __('litigation.court_level_appeal'),
            self::Cassation => __('litigation.court_level_cassation'),
            self::SpecializedCommercial => __('litigation.court_level_specialized_commercial'),
            self::SpecializedLabor => __('litigation.court_level_specialized_labor'),
            self::Administrative => __('litigation.court_level_administrative'),
            self::Sharia => __('litigation.court_level_sharia'),
            self::Arbitration => __('litigation.court_level_arbitration'),
        };
    }

    /**
     * Appeal window in calendar days, per advisor input from Khaldoun Khater
     * (Al-Dujani Office, Amman) — see docs/02_advisor_meeting_log.md
     * Conversation 2, Decision #18.
     *
     * Returns null for court levels where the appeal window is not a simple
     * fixed number of days (requires manual input or is not applicable).
     */
    public function appealWindowDays(): ?int
    {
        return match ($this) {
            self::Magistrate => 10,
            self::FirstInstance => 30,
            default => null,
        };
    }
}
