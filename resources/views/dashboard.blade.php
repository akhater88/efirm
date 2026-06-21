<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('dashboard.title') }} — {{ __('common.app_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <span class="text-lg font-semibold">{{ __('common.app_name') }}</span>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                    {{ __('auth.logout') }}
                </button>
            </form>
        </div>
    </nav>
    <main class="max-w-4xl mx-auto mt-16 text-center">
        <h1 class="text-2xl font-bold mb-2">{{ __('dashboard.welcome_message') }}</h1>
        <p class="text-gray-500">{{ auth()->user()->currentWorkspace()?->name }}</p>
    </main>
</body>
</html>
