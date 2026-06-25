<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta --}}
    @php
        $locale = app()->getLocale();
        $seoTitle = $seoTitle ?? config("seo.defaults.title.{$locale}", config('seo.defaults.title.en'));
        $seoDescription = $seoDescription ?? config("seo.defaults.description.{$locale}", config('seo.defaults.description.en'));
        $canonicalUrl = url()->current();
        $baseUrl = config('app.url', 'https://efirm.io');
        $ogImage = asset(config('seo.defaults.og_image', '/img/og-image.jpg'));
    @endphp

    <title>{{ Str::limit($seoTitle, 60, '') }}</title>
    <meta name="description" content="{{ Str::limit($seoDescription, 160, '') }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ $locale === 'ar' ? 'ar_JO' : 'en_US' }}">
    <meta property="og:site_name" content="eFirm">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- hreflang --}}
    @if ($locale === 'ar')
        <link rel="alternate" hreflang="ar" href="{{ $canonicalUrl }}">
        <link rel="alternate" hreflang="en" href="{{ str_replace('/ar', '', $canonicalUrl) ?: $baseUrl }}">
        <link rel="alternate" hreflang="x-default" href="{{ str_replace('/ar', '', $canonicalUrl) ?: $baseUrl }}">
    @else
        <link rel="alternate" hreflang="en" href="{{ $canonicalUrl }}">
        <link rel="alternate" hreflang="ar" href="{{ $baseUrl }}/ar{{ request()->path() === '/' ? '' : '/' . request()->path() }}">
        <link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">
    @endif

    {{-- JSON-LD --}}
    <script type="application/ld+json">
    {!! json_encode(config('seo.json_ld'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- Favicons --}}
    <link rel="icon" href="{{ asset('img/brand/efirm-favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('img/brand/efirm-favicon-32.png') }}" sizes="32x32" type="image/png">
    <link rel="icon" href="{{ asset('img/brand/efirm-favicon-16.png') }}" sizes="16x16" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('img/brand/efirm-favicon-192.png') }}">

    {{-- Theme --}}
    <meta name="theme-color" content="#072E17">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Preload critical fonts --}}
    <link rel="preload" href="{{ asset('fonts/source-sans-pro-v21-latin-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/ibm-plex-sans-arabic-v12-arabic-regular.woff2') }}" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Reduced motion + smooth scroll --}}
    <style>
        html { scroll-behavior: smooth; }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-white antialiased">
    {{-- Skip to content --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 {{ $locale === 'ar' ? 'focus:right-4' : 'focus:left-4' }} focus:z-[100] focus:bg-white focus:px-4 focus:py-2 focus:rounded-md focus:shadow-lg focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:text-brand-700 focus:font-medium">
        {{ __('marketing.accessibility.skip_to_content') }}
    </a>

    {{ $slot }}

    {{-- Alpine.js for FAQ accordion + mobile menu --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
