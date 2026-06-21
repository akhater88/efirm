<?php

namespace App\Services;

use App\Enums\KpiMetric;
use App\Enums\MatterStatus;
use App\Models\KpiTarget;
use App\Models\Matter;
use App\Models\Team;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Carbon;

class KpiService
{
    /**
     * Get the actual value for a metric in a given date range.
     */
    public function getActualValue(User|Team $target, KpiMetric $metric, Carbon $start, Carbon $end): string
    {
        $userIds = $this->resolveUserIds($target);

        return match ($metric) {
            KpiMetric::BillableHoursMonthly => $this->billableHours($userIds, $start, $end),
            KpiMetric::MattersOpenedMonthly => $this->mattersOpened($userIds, $start, $end),
            KpiMetric::MattersClosedMonthly => $this->mattersClosed($userIds, $start, $end),
            KpiMetric::RevenueMonthly => $this->revenue($userIds, $start, $end),
        };
    }

    /**
     * Get progress as a ratio (0.0 to 1.0+) against a KPI target.
     */
    public function getProgress(KpiTarget $target, Carbon $start, Carbon $end): array
    {
        $actual = $this->getActualValue(
            $target->targetable,
            $target->metric,
            $start,
            $end,
        );

        $targetValue = $target->target_value;
        $ratio = $targetValue > 0 ? bcdiv($actual, (string) $targetValue, 4) : '0.0000';

        return [
            'metric' => $target->metric->value,
            'target_value' => $targetValue,
            'actual_value' => $actual,
            'ratio' => $ratio,
            'percentage' => bcmul($ratio, '100', 1),
        ];
    }

    /**
     * Resolve user IDs from a User or Team target.
     *
     * @return string[]
     */
    private function resolveUserIds(User|Team $target): array
    {
        if ($target instanceof User) {
            return [$target->id];
        }

        return $target->members()->pluck('users.id')->toArray();
    }

    private function billableHours(array $userIds, Carbon $start, Carbon $end): string
    {
        $totalMinutes = TimeEntry::whereIn('user_id', $userIds)
            ->where('is_billable', true)
            ->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end)
            ->sum('duration_minutes');

        return bcdiv((string) $totalMinutes, '60', 2);
    }

    private function mattersOpened(array $userIds, Carbon $start, Carbon $end): string
    {
        return (string) Matter::whereIn('created_by_user_id', $userIds)
            ->where('opened_at', '>=', $start)
            ->where('opened_at', '<=', $end)
            ->count();
    }

    private function mattersClosed(array $userIds, Carbon $start, Carbon $end): string
    {
        return (string) Matter::whereIn('lead_lawyer_id', $userIds)
            ->where('status', MatterStatus::Closed)
            ->where('closed_at', '>=', $start)
            ->where('closed_at', '<=', $end)
            ->count();
    }

    private function revenue(array $userIds, Carbon $start, Carbon $end): string
    {
        // Revenue = sum of billable amount from time entries
        $entries = TimeEntry::whereIn('user_id', $userIds)
            ->where('is_billable', true)
            ->whereNotNull('billing_rate_per_hour')
            ->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end)
            ->get();

        $total = '0.00';
        foreach ($entries as $entry) {
            $amount = $entry->billableAmount();
            if ($amount !== null) {
                $total = bcadd($total, $amount, 2);
            }
        }

        return $total;
    }
}
