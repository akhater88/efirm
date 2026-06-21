<?php

namespace App\Enums;

enum DocumentLanguage: string
{
    case Arabic = 'ar';
    case English = 'en';
    case Bilingual = 'bilingual';

    public function label(): string
    {
        return match ($this) {
            self::Arabic => __('documents.language_ar'),
            self::English => __('documents.language_en'),
            self::Bilingual => __('documents.language_bilingual'),
        };
    }
}
