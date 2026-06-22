<?php

namespace App\Enums;

enum LeadSource: string
{
    case Referral = 'referral';
    case Website = 'website';
    case WalkIn = 'walk_in';
    case SocialMedia = 'social_media';
    case Conference = 'conference';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Referral => __('crm.lead_source_referral'),
            self::Website => __('crm.lead_source_website'),
            self::WalkIn => __('crm.lead_source_walk_in'),
            self::SocialMedia => __('crm.lead_source_social_media'),
            self::Conference => __('crm.lead_source_conference'),
            self::Other => __('crm.lead_source_other'),
        };
    }
}
