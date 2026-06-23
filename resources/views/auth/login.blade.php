<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.login') }} — {{ __('common.app_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #F9FAFB; font-family: 'Inter', 'IBM Plex Sans Arabic', system-ui, sans-serif;">
    <div style="position: absolute; top: 16px; {{ app()->getLocale() === 'ar' ? 'left: 16px;' : 'right: 16px;' }}">
        <form method="POST" action="{{ route('locale.switch') }}">
            @csrf
            @if (app()->getLocale() === 'ar')
                <input type="hidden" name="locale" value="en">
                <button type="submit" style="font-size: 14px; color: #6B7280; background: none; border: none; cursor: pointer;">English</button>
            @else
                <input type="hidden" name="locale" value="ar">
                <button type="submit" style="font-size: 14px; color: #6B7280; background: none; border: none; cursor: pointer;">العربية</button>
            @endif
        </form>
    </div>

    <div style="width: 100%; max-width: 420px; padding: 24px;">
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 40px; text-align: center;">
            <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 8px; color: #111827;">{{ __('common.app_name') }}</h1>
            <p style="color: #6B7280; margin-bottom: 32px; font-size: 15px;">{{ __('auth.login') }}</p>

            @if ($errors->any())
                <div style="margin-bottom: 16px; padding: 12px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; color: #991B1B; font-size: 14px; text-align: start;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('error'))
                <div style="margin-bottom: 16px; padding: 12px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; color: #991B1B; font-size: 14px;">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Email/Password Form --}}
            <form method="POST" action="{{ route('login.submit') }}" style="text-align: start;">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label for="email" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px;">{{ __('auth.email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           style="width: 100%; padding: 10px 14px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;"
                           placeholder="name@example.com">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="password" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px;">{{ __('auth.password') }}</label>
                    <input type="password" id="password" name="password" required
                           style="width: 100%; padding: 10px 14px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="remember" name="remember" style="width: 16px; height: 16px;">
                    <label for="remember" style="font-size: 13px; color: #6B7280;">{{ __('auth.remember_me') }}</label>
                </div>

                <button type="submit"
                        style="width: 100%; padding: 12px; background: #4F46E5; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                    {{ __('auth.sign_in') }}
                </button>
            </form>

            {{-- Divider --}}
            <div style="display: flex; align-items: center; gap: 12px; margin: 24px 0;">
                <div style="flex: 1; height: 1px; background: #E5E7EB;"></div>
                <span style="font-size: 13px; color: #9CA3AF;">{{ __('auth.or') }}</span>
                <div style="flex: 1; height: 1px; background: #E5E7EB;"></div>
            </div>

            {{-- Google OAuth --}}
            <a href="{{ route('auth.google.redirect') }}"
               style="display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; padding: 12px; background: #fff; border: 1px solid #D1D5DB; border-radius: 8px; color: #374151; font-size: 14px; font-weight: 500; text-decoration: none; box-sizing: border-box;">
                <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                {{ __('auth.sign_in_with_google') }}
            </a>
        </div>
    </div>
</body>
</html>
