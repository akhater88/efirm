<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Converted = 'converted';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => __('crm.lead_status_new'),
            self::Contacted => __('crm.lead_status_contacted'),
            self::Qualified => __('crm.lead_status_qualified'),
            self::Converted => __('crm.lead_status_converted'),
            self::Lost => __('crm.lead_status_lost'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Contacted => 'warning',
            self::Qualified => 'success',
            self::Converted => 'success',
            self::Lost => 'danger',
        };
    }
}
