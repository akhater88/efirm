<?php

namespace App\Enums;

enum OurPosition: string
{
    case WeRepresent = 'we_represent';
    case TheyRepresent = 'they_represent';
    case NoCounsel = 'no_counsel';
    case Mutual = 'mutual';

    public function label(): string
    {
        return match ($this) {
            self::WeRepresent => __('matters.position_we_represent'),
            self::TheyRepresent => __('matters.position_they_represent'),
            self::NoCounsel => __('matters.position_no_counsel'),
            self::Mutual => __('matters.position_mutual'),
        };
    }
}
