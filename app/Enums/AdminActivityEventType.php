<?php

namespace App\Enums;

enum AdminActivityEventType: string
{
    case LoginSuccess = 'admin.login.success';
    case LoginFailed = 'admin.login.failed';
    case LoginBlockedDisabled = 'admin.login.blocked_disabled';
    case LoginRateLimited = 'admin.login.rate_limited';
    case Logout = 'admin.logout';
    case SessionExpired = 'admin.session.expired';
    case UserCreated = 'admin.user.created';
    case UserUpdated = 'admin.user.updated';
    case UserPasswordReset = 'admin.user.password_reset';
    case UserDisabled = 'admin.user.disabled';
    case UserReenabled = 'admin.user.reenabled';
    case LocaleChanged = 'admin.locale.changed';

    public function label(): string
    {
        return match ($this) {
            self::LoginSuccess => __('admin.activity.login_success'),
            self::LoginFailed => __('admin.activity.login_failed'),
            self::LoginBlockedDisabled => __('admin.activity.login_blocked_disabled'),
            self::LoginRateLimited => __('admin.activity.login_rate_limited'),
            self::Logout => __('admin.activity.logout'),
            self::SessionExpired => __('admin.activity.session_expired'),
            self::UserCreated => __('admin.activity.user_created'),
            self::UserUpdated => __('admin.activity.user_updated'),
            self::UserPasswordReset => __('admin.activity.user_password_reset'),
            self::UserDisabled => __('admin.activity.user_disabled'),
            self::UserReenabled => __('admin.activity.user_reenabled'),
            self::LocaleChanged => __('admin.activity.locale_changed'),
        };
    }
}
