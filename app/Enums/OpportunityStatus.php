<?php

namespace App\Enums;

enum OpportunityStatus: string
{
    case Open = 'open';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('crm.opportunity_status_open'),
            self::Won => __('crm.opportunity_status_won'),
            self::Lost => __('crm.opportunity_status_lost'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }
}
