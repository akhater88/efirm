<?php

namespace App\Enums;

enum KpiMetric: string
{
    case BillableHoursMonthly = 'billable_hours_monthly';
    case MattersOpenedMonthly = 'matters_opened_monthly';
    case MattersClosedMonthly = 'matters_closed_monthly';
    case RevenueMonthly = 'revenue_monthly';

    public function label(): string
    {
        return match ($this) {
            self::BillableHoursMonthly => __('kpi.metric_billable_hours'),
            self::MattersOpenedMonthly => __('kpi.metric_matters_opened'),
            self::MattersClosedMonthly => __('kpi.metric_matters_closed'),
            self::RevenueMonthly => __('kpi.metric_revenue'),
        };
    }
}
