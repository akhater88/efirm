<?php

namespace App\Enums;

enum KpiMetric: string
{
    case BillableHoursMonthly = 'billable_hours_monthly';
    case MattersOpenedMonthly = 'matters_opened_monthly';
    case MattersClosedMonthly = 'matters_closed_monthly';
    case RevenueMonthly = 'revenue_monthly';
    case MattersAsLeadActive = 'matters_as_lead_active';
    case MattersAsSupportingActive = 'matters_as_supporting_active';
    case MattersClosedAsLeadPeriod = 'matters_closed_as_lead_period';

    public function label(): string
    {
        return match ($this) {
            self::BillableHoursMonthly => __('kpi.metric_billable_hours'),
            self::MattersOpenedMonthly => __('kpi.metric_matters_opened'),
            self::MattersClosedMonthly => __('kpi.metric_matters_closed'),
            self::RevenueMonthly => __('kpi.metric_revenue'),
            self::MattersAsLeadActive => __('kpi.metric_matters_as_lead'),
            self::MattersAsSupportingActive => __('kpi.metric_matters_as_supporting'),
            self::MattersClosedAsLeadPeriod => __('kpi.metric_matters_closed_as_lead'),
        };
    }
}
