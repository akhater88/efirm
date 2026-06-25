<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.login') }} — {{ __('brand.app_name') }}</title>
    <link rel="icon" href="{{ asset('img/brand/efirm-favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('fonts/source-sans-pro-v21-latin-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/ibm-plex-sans-arabic-v12-arabic-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <meta name="theme-color" content="#072E17">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--surface-page, #FAFAF9); margin: 0;">
    {{-- Locale switcher --}}
    <div style="position: absolute; top: 16px; {{ app()->getLocale() === 'ar' ? 'left: 16px;' : 'right: 16px;' }}">
        <a href="{{ url('/locale/' . (app()->getLocale() === 'ar' ? 'en' : 'ar')) }}" style="font-size: 13px; color: var(--text-tertiary, #78716C); text-decoration: none; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border-default, #E7E5E4);">
            {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
        </a>
    </div>

    <div style="width: 100%; max-width: 420px; padding: 24px;">
        {{-- Logo --}}
        <div style="text-align: center; margin-bottom: 32px;">
            <img src="{{ asset('img/brand/efirm-logo.svg') }}" alt="{{ __('brand.logo_alt') }}" style="height: 48px; width: auto; margin-bottom: 12px;">
            <p style="color: var(--text-tertiary, #78716C); font-size: 14px; margin: 0;">{{ __('brand.tagline') }}</p>
        </div>

        <div style="background: var(--surface-card, #FFFFFF); border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border-default, #E7E5E4); padding: 32px;">
            <h1 style="font-size: 20px; font-weight: 700; margin: 0 0 24px; color: var(--text-primary, #1C1917); text-align: center;">{{ __('auth.login') }}</h1>

            @if ($errors->any())
                <div style="margin-bottom: 16px; padding: 12px; background: var(--color-danger-50, #FEF2F2); border: 1px solid var(--color-danger-500, #DC2626); border-radius: 8px; color: var(--color-danger-700, #B91C1C); font-size: 13px; text-align: start;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('error'))
                <div style="margin-bottom: 16px; padding: 12px; background: var(--color-danger-50, #FEF2F2); border: 1px solid var(--color-danger-500, #DC2626); border-radius: 8px; color: var(--color-danger-700, #B91C1C); font-size: 13px;">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Email/Password Form --}}
            <form method="POST" action="{{ route('login.submit') }}" style="text-align: start;">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label for="email" style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 6px;">{{ __('auth.email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus dir="ltr"
                           style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; font-size: 14px; color: var(--text-primary, #1C1917); outline: none; box-sizing: border-box;"
                           onfocus="this.style.borderColor='var(--border-focus, #0D5C2E)'; this.style.boxShadow='var(--ring-brand)'"
                           onblur="this.style.borderColor='var(--border-default, #E7E5E4)'; this.style.boxShadow='none'"
                           placeholder="name@example.com">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="password" style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 6px;">{{ __('auth.password') }}</label>
                    <input type="password" id="password" name="password" required dir="ltr"
                           style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; font-size: 14px; color: var(--text-primary, #1C1917); outline: none; box-sizing: border-box;"
                           onfocus="this.style.borderColor='var(--border-focus, #0D5C2E)'; this.style.boxShadow='var(--ring-brand)'"
                           onblur="this.style.borderColor='var(--border-default, #E7E5E4)'; this.style.boxShadow='none'">
                </div>

                <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="remember" name="remember" style="width: 16px; height: 16px; accent-color: var(--color-brand-500, #0D5C2E);">
                    <label for="remember" style="font-size: 13px; color: var(--text-tertiary, #78716C);">{{ __('auth.remember_me') }}</label>
                </div>

                <button type="submit"
                        style="width: 100%; padding: 12px; background: var(--color-brand-500, #0D5C2E); color: #FFFFFF; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    {{ __('auth.sign_in') }}
                </button>
            </form>

            {{-- Divider --}}
            <div style="display: flex; align-items: center; gap: 12px; margin: 24px 0;">
                <div style="flex: 1; height: 1px; background: var(--border-default, #E7E5E4);"></div>
                <span style="font-size: 12px; color: var(--text-tertiary, #78716C);">{{ __('auth.or') }}</span>
                <div style="flex: 1; height: 1px; background: var(--border-default, #E7E5E4);"></div>
            </div>

            {{-- Google OAuth --}}
            <a href="{{ route('auth.google.redirect') }}"
               style="display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; padding: 12px; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; color: var(--text-primary, #1C1917); font-size: 14px; font-weight: 500; text-decoration: none; box-sizing: border-box;">
                <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                {{ __('auth.sign_in_with_google') }}
            </a>
        </div>

        {{-- Footer --}}
        <p style="text-align: center; margin-top: 24px; font-size: 12px; color: var(--text-tertiary, #78716C);">
            {{ str_replace(':year', date('Y'), __('brand.copyright')) }}
        </p>
    </div>
</body>
</html>
