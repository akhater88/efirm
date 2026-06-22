<?php

namespace App\Enums;

enum AiDocGenerationStatus: string
{
    case Queued = 'queued';
    case Generating = 'generating';
    case Complete = 'complete';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Queued => __('ai.gen_status_queued'),
            self::Generating => __('ai.gen_status_generating'),
            self::Complete => __('ai.gen_status_complete'),
            self::Failed => __('ai.gen_status_failed'),
            self::Cancelled => __('ai.gen_status_cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Queued => 'gray',
            self::Generating => 'info',
            self::Complete => 'success',
            self::Failed => 'danger',
            self::Cancelled => 'warning',
        };
    }
}
