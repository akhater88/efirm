<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED]
enum DocumentType: string
{
    case Contract = 'contract';
    case Memo = 'memo';
    case Letter = 'letter';
    case Amendment = 'amendment';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Contract => __('documents.type_contract'),
            self::Memo => __('documents.type_memo'),
            self::Letter => __('documents.type_letter'),
            self::Amendment => __('documents.type_amendment'),
            self::Other => __('documents.type_other'),
        };
    }
}
