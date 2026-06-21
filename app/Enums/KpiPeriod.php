<?php

namespace App\Enums;

enum KpiPeriod: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => __('kpi.period_monthly'),
            self::Quarterly => __('kpi.period_quarterly'),
            self::Annual => __('kpi.period_annual'),
        };
    }
}
