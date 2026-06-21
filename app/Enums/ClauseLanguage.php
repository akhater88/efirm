<?php

namespace App\Enums;

enum ClauseLanguage: string
{
    case Arabic = 'ar';
    case English = 'en';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Arabic => __('documents.language_ar'),
            self::English => __('documents.language_en'),
            self::Mixed => __('documents.language_mixed'),
        };
    }
}
