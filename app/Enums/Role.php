<?php

namespace App\Enums;

enum Role: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Owner => __('roles.owner'),
            self::Admin => __('roles.admin'),
            self::Member => __('roles.member'),
        };
    }

    public function canAccessFilament(): bool
    {
        return match ($this) {
            self::Owner, self::Admin => true,
            self::Member => false,
        };
    }

    public function isPrivileged(): bool
    {
        return $this === self::Owner;
    }
}
