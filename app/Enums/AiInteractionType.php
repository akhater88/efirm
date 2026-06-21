<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED]
enum AiInteractionType: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Suggest = 'suggest';
    case Translate = 'translate';
    case Explain = 'explain';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('ai.type_draft'),
            self::Review => __('ai.type_review'),
            self::Suggest => __('ai.type_suggest'),
            self::Translate => __('ai.type_translate'),
            self::Explain => __('ai.type_explain'),
        };
    }
}
