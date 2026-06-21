<?php

namespace App\Enums;

enum ResponsibleParty: string
{
    case Us = 'us';
    case Counterparty = 'counterparty';
    case Mutual = 'mutual';
    case ThirdParty = 'third_party';

    public function label(): string
    {
        return match ($this) {
            self::Us => __('obligations.party_us'),
            self::Counterparty => __('obligations.party_counterparty'),
            self::Mutual => __('obligations.party_mutual'),
            self::ThirdParty => __('obligations.party_third_party'),
        };
    }
}
