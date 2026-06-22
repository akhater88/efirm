<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum ServiceStatus: string
{
    case Successful = 'successful';
    case FailedNoResponse = 'failed_no_response';
    case FailedRefused = 'failed_refused';
    case FailedInvalidAddress = 'failed_invalid_address';
    case PendingProof = 'pending_proof';

    public function label(): string
    {
        return match ($this) {
            self::Successful => __('litigation.service_status_successful'),
            self::FailedNoResponse => __('litigation.service_status_failed_no_response'),
            self::FailedRefused => __('litigation.service_status_failed_refused'),
            self::FailedInvalidAddress => __('litigation.service_status_failed_invalid_address'),
            self::PendingProof => __('litigation.service_status_pending_proof'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Successful => 'success',
            self::FailedNoResponse => 'danger',
            self::FailedRefused => 'danger',
            self::FailedInvalidAddress => 'danger',
            self::PendingProof => 'warning',
        };
    }
}
