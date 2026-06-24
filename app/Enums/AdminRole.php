<?php

namespace App\Enums;

enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case Support = 'support';
    case Finance = 'finance';
    case ReadOnly = 'read_only';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => __('admin.roles.super_admin'),
            self::Support => __('admin.roles.support'),
            self::Finance => __('admin.roles.finance'),
            self::ReadOnly => __('admin.roles.read_only'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Support => 'warning',
            self::Finance => 'info',
            self::ReadOnly => 'gray',
        };
    }
}
