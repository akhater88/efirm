<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('common.app_name'))</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50">
    {{-- Top Navigation --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo + Workspace Switcher --}}
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="text-lg font-bold text-gray-900">
                        {{ __('common.app_name') }}
                    </a>
                    @auth
                        <livewire:workspace-switcher />
                    @endauth
                </div>

                {{-- Right side: Locale + User Menu --}}
                <div class="flex items-center gap-4">
                    {{-- Locale Switcher --}}
                    <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                        @csrf
                        @if (app()->getLocale() === 'ar')
                            <input type="hidden" name="locale" value="en">
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">English</button>
                        @else
                            <input type="hidden" name="locale" value="ar">
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">العربية</button>
                        @endif
                    </form>

                    {{-- User Menu --}}
                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}"
                                         alt="{{ auth()->user()->name }}"
                                         class="w-8 h-8 rounded-full">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-700 font-medium text-xs">
                                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                                <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50
                                        {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }}">
                                <div class="py-1">
                                    <a href="{{ route('profile') }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        {{ __('nav.profile') }}
                                    </a>

                                    @if (auth()->user()->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')))
                                        <a href="/admin"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            {{ __('nav.admin') }}
                                        </a>
                                    @endif

                                    <div class="border-t border-gray-100"></div>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="w-full text-start px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            {{ __('nav.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
</body>
</html>
