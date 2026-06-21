<?php

namespace App\Enums;

enum PracticeArea: string
{
    case CommercialContracts = 'commercial_contracts';
    case MA = 'ma';
    case CorporateGovernance = 'corporate_governance';
    case Securities = 'securities';
    case GeneralCounsel = 'general_counsel';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CommercialContracts => __('matters.practice_area_commercial_contracts'),
            self::MA => __('matters.practice_area_ma'),
            self::CorporateGovernance => __('matters.practice_area_corporate_governance'),
            self::Securities => __('matters.practice_area_securities'),
            self::GeneralCounsel => __('matters.practice_area_general_counsel'),
            self::Other => __('matters.practice_area_other'),
        };
    }
}
