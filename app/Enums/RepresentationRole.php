<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum RepresentationRole: string
{
    case Plaintiff = 'plaintiff';
    case Defendant = 'defendant';
    case Intervenor = 'intervenor';
    case ThirdParty = 'third_party';

    public function label(): string
    {
        return match ($this) {
            self::Plaintiff => __('litigation.role_plaintiff'),
            self::Defendant => __('litigation.role_defendant'),
            self::Intervenor => __('litigation.role_intervenor'),
            self::ThirdParty => __('litigation.role_third_party'),
        };
    }
}
