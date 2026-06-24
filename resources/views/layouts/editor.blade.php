<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('common.app_name'))</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#072E17">

    {{-- Font preload --}}
    <link rel="preload" href="{{ asset('fonts/source-sans-pro-v21-latin-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/ibm-plex-sans-arabic-v12-arabic-regular.woff2') }}" as="font" type="font/woff2" crossorigin>

    {{-- Favicons --}}
    <link rel="icon" href="{{ asset('img/brand/efirm-favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('img/brand/efirm-favicon-32.png') }}" sizes="32x32" type="image/png">

    @vite(['resources/css/editor.css', 'resources/js/editor.js'])
    @livewireStyles
    <style>
        body { font-family: 'Source Sans Pro', 'IBM Plex Sans Arabic', system-ui, sans-serif; }
        [dir="rtl"] body { font-family: 'IBM Plex Sans Arabic', 'Source Sans Pro', system-ui, sans-serif; }
        .ProseMirror { font-family: 'Source Sans Pro', 'IBM Plex Sans Arabic', system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    {{ $slot }}
    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
</body>
</html>
