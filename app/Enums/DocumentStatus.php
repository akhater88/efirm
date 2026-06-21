<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Draft = 'draft';
    case UnderReview = 'under_review';
    case WithCounterparty = 'with_counterparty';
    case Signed = 'signed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('documents.status_draft'),
            self::UnderReview => __('documents.status_under_review'),
            self::WithCounterparty => __('documents.status_with_counterparty'),
            self::Signed => __('documents.status_signed'),
            self::Archived => __('documents.status_archived'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::UnderReview => 'warning',
            self::WithCounterparty => 'info',
            self::Signed => 'success',
            self::Archived => 'danger',
        };
    }
}
