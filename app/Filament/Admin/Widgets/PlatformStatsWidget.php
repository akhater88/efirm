<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.dashboard.total_workspaces'), 0)
                ->description(__('admin.dashboard.updated_when_available'))
                ->color('gray'),

            Stat::make(__('admin.dashboard.total_users'), 0)
                ->description(__('admin.dashboard.updated_when_available'))
                ->color('gray'),

            Stat::make(__('admin.dashboard.active_subscriptions'), 0)
                ->description(__('admin.dashboard.updated_when_available'))
                ->color('gray'),

            Stat::make(__('admin.dashboard.monthly_revenue'), '$0.00')
                ->description(__('admin.dashboard.updated_when_available'))
                ->color('gray'),
        ];
    }
}
