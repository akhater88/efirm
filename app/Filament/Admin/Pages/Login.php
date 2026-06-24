<?php

namespace App\Filament\Admin\Pages;

use App\Enums\AdminActivityEventType;
use App\Models\AdminUser;
use App\Services\AdminActivityLogService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Auth\SessionGuard;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            AdminActivityLogService::log(
                eventType: AdminActivityEventType::LoginRateLimited,
                attemptedEmail: $this->data['email'] ?? null,
            );

            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $email = $data['email'] ?? null;

        // Check if admin is disabled before attempting auth
        $admin = AdminUser::where('email', $email)->first();

        if ($admin?->isDisabled()) {
            AdminActivityLogService::log(
                eventType: AdminActivityEventType::LoginBlockedDisabled,
                admin: $admin,
                attemptedEmail: $email,
            );

            $this->addError('data.email', __('admin.auth.account_disabled'));

            return null;
        }

        /** @var SessionGuard $guard */
        $guard = Filament::auth();

        if (! $guard->attempt(
            $this->getCredentialsFromFormData($data),
            $data['remember'] ?? false,
        )) {
            AdminActivityLogService::log(
                eventType: AdminActivityEventType::LoginFailed,
                attemptedEmail: $email,
            );

            $this->throwFailureValidationException();
        }

        /** @var AdminUser $authenticatedAdmin */
        $authenticatedAdmin = $guard->user();

        session()->regenerate();
        session(['admin.last_activity_at' => now()]);
        session(['admin.session_started_at' => now()]);

        $authenticatedAdmin->update(['last_login_at' => now()]);

        AdminActivityLogService::log(
            eventType: AdminActivityEventType::LoginSuccess,
            admin: $authenticatedAdmin,
        );

        return app(LoginResponse::class);
    }
}
