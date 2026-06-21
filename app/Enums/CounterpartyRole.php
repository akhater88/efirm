<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED]
enum CounterpartyRole: string
{
    case Buyer = 'buyer';
    case Seller = 'seller';
    case Licensor = 'licensor';
    case Licensee = 'licensee';
    case ServiceProvider = 'service_provider';
    case Client = 'client';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Buyer => __('matters.role_buyer'),
            self::Seller => __('matters.role_seller'),
            self::Licensor => __('matters.role_licensor'),
            self::Licensee => __('matters.role_licensee'),
            self::ServiceProvider => __('matters.role_service_provider'),
            self::Client => __('matters.role_client'),
            self::Other => __('matters.role_other'),
        };
    }
}
