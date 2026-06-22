<?php

namespace App\Enums;

enum LegalReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('ai.review_pending'),
            self::Approved => __('ai.review_approved'),
            self::Revoked => __('ai.review_revoked'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Revoked => 'danger',
        };
    }
}
