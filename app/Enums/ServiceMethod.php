<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum ServiceMethod: string
{
    case PersonalService = 'personal_service';
    case RegisteredMail = 'registered_mail';
    case CourtBailiff = 'court_bailiff';
    case SubstitutedService = 'substituted_service';
    case Publication = 'publication';
    case Electronic = 'electronic';
    case ForeignService = 'foreign_service';

    public function label(): string
    {
        return match ($this) {
            self::PersonalService => __('litigation.service_method_personal_service'),
            self::RegisteredMail => __('litigation.service_method_registered_mail'),
            self::CourtBailiff => __('litigation.service_method_court_bailiff'),
            self::SubstitutedService => __('litigation.service_method_substituted_service'),
            self::Publication => __('litigation.service_method_publication'),
            self::Electronic => __('litigation.service_method_electronic'),
            self::ForeignService => __('litigation.service_method_foreign_service'),
        };
    }
}
