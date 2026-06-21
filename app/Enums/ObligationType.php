<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED]
enum ObligationType: string
{
    case Payment = 'payment';
    case Delivery = 'delivery';
    case Reporting = 'reporting';
    case Notification = 'notification';
    case Consent = 'consent';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Payment => __('obligations.type_payment'),
            self::Delivery => __('obligations.type_delivery'),
            self::Reporting => __('obligations.type_reporting'),
            self::Notification => __('obligations.type_notification'),
            self::Consent => __('obligations.type_consent'),
            self::Other => __('obligations.type_other'),
        };
    }
}
