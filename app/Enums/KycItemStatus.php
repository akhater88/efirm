<?php

namespace App\Enums;

enum KycItemStatus: string
{
    case NotRequested = 'not_requested';
    case Requested = 'requested';
    case Received = 'received';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::NotRequested => __('kyc.item_not_requested'),
            self::Requested => __('kyc.item_requested'),
            self::Received => __('kyc.item_received'),
            self::Verified => __('kyc.item_verified'),
            self::Rejected => __('kyc.item_rejected'),
            self::Expired => __('kyc.item_expired'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NotRequested => 'gray',
            self::Requested => 'warning',
            self::Received => 'info',
            self::Verified => 'success',
            self::Rejected => 'danger',
            self::Expired => 'danger',
        };
    }
}
