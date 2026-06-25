<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? '' }}@yield('title', __('common.app_name'))</title>
    <link rel="manifest" href="/manifest.json">

    {{-- Font preload --}}
    <link rel="preload" href="{{ asset('fonts/source-sans-pro-v21-latin-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/ibm-plex-sans-arabic-v12-arabic-regular.woff2') }}" as="font" type="font/woff2" crossorigin>

    {{-- Favicons --}}
    <link rel="icon" href="{{ asset('img/brand/efirm-favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('img/brand/efirm-favicon-32.png') }}" sizes="32x32" type="image/png">
    <link rel="icon" href="{{ asset('img/brand/efirm-favicon-16.png') }}" sizes="16x16" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('img/brand/efirm-favicon-192.png') }}">

    {{-- PWA / theme color --}}
    <meta name="theme-color" content="#072E17">

    {{-- SEO / brand --}}
    <meta name="description" content="{{ __('brand.tagline') }}">
    <meta property="og:site_name" content="{{ __('brand.app_name') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="min-height: 100vh; background: var(--surface-page, #FAFAF9); margin: 0;">
    {{-- Top Chrome --}}
    <livewire:dashboard.top-chrome />

    {{-- Left Sidebar --}}
    <livewire:dashboard.left-sidebar />

    {{-- Quick Links Rail --}}
    <livewire:dashboard.quick-links-rail />

    {{-- Flash Messages --}}
    @if (session('success'))
        <div style="max-width: 1280px; margin: 16px auto; padding: 0 16px;">
            <div style="padding: 12px 16px; background: var(--color-success-50, #F0FDF4); border: 1px solid var(--color-success-500, #15803D); border-radius: 8px; color: var(--color-success-700, #166534); font-size: 14px;">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div style="max-width: 1280px; margin: 16px auto; padding: 0 16px;">
            <div style="padding: 12px 16px; background: var(--color-danger-50, #FEF2F2); border: 1px solid var(--color-danger-500, #DC2626); border-radius: 8px; color: var(--color-danger-700, #B91C1C); font-size: 14px;">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main Content (offset by sidebar + quick links rail) --}}
    <main class="dashboard-main" style="margin-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: 240px; margin-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: 72px; padding: 24px 24px; min-height: calc(100vh - 56px);">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    @livewireScripts
    <style>
        @media (max-width: 1279px) {
            .dashboard-main {
                margin-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: 0 !important;
            }
        }
    </style>
</body>
</html>
