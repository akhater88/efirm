<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('common.app_name'))</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/editor.css', 'resources/js/editor.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', 'IBM Plex Sans Arabic', system-ui, sans-serif; }
        [dir="rtl"] body { font-family: 'IBM Plex Sans Arabic', 'Inter', system-ui, sans-serif; }
        .ProseMirror { font-family: 'Inter', 'IBM Plex Sans Arabic', system-ui, sans-serif; }
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
