<header role="banner" class="fixed top-0 inset-x-0 z-50 bg-white transition-shadow duration-200" x-data="{ mobileOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 100" :class="{ 'shadow-sm': scrolled }">
    <nav role="navigation" aria-label="{{ __('marketing.header.features') }}" class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-[72px]">
            {{-- Logo --}}
            <a href="{{ app()->getLocale() === 'ar' ? '/ar' : '/' }}" class="flex-shrink-0">
                <img src="{{ asset('img/brand/efirm-horizontal-compact.svg') }}" alt="eFirm" class="h-12 w-auto">
            </a>

            {{-- Desktop Nav Links (centre) --}}
            <div class="hidden lg:flex items-center gap-8">
                <a href="#features" class="text-sm font-medium text-neutral-600 hover:text-neutral-900 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 rounded-md px-2 py-1">
                    {{ __('marketing.header.features') }}
                </a>
                <a href="#pricing" class="text-sm font-medium text-neutral-600 hover:text-neutral-900 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 rounded-md px-2 py-1">
                    {{ __('marketing.header.pricing') }}
                </a>
                <a href="#security" class="text-sm font-medium text-neutral-600 hover:text-neutral-900 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 rounded-md px-2 py-1">
                    {{ __('marketing.header.security') }}
                </a>
                <a href="#faq" class="text-sm font-medium text-neutral-600 hover:text-neutral-900 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 rounded-md px-2 py-1">
                    {{ __('marketing.header.faq') }}
                </a>
            </div>

            {{-- Desktop Right: Locale Toggle + Sign In + CTA --}}
            <div class="hidden lg:flex items-center gap-4">
                <a href="{{ app()->getLocale() === 'ar' ? '/' : '/ar' }}"
                   class="text-sm font-medium text-neutral-600 hover:text-neutral-900 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 rounded-md px-2 py-1">
                    {{ __('marketing.header.locale_toggle') }}
                </a>
                <a href="/login"
                   class="text-sm font-medium text-neutral-700 hover:text-neutral-900 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 rounded-md px-2 py-1">
                    {{ __('marketing.header.sign_in') }}
                </a>
                <a href="/register?utm_source=landing_header"
                   class="inline-flex items-center px-4 py-2 bg-brand-500 text-white text-sm font-semibold rounded-lg hover:bg-brand-600 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    {{ __('marketing.header.start_trial') }}
                </a>
            </div>

            {{-- Mobile hamburger --}}
            <button @click="mobileOpen = !mobileOpen"
                    class="lg:hidden p-2 rounded-md text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                    :aria-label="mobileOpen ? '{{ __('marketing.header.menu_close') }}' : '{{ __('marketing.header.menu_open') }}'"
                    aria-expanded="false"
                    :aria-expanded="mobileOpen.toString()">
                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden border-t border-neutral-200 py-4 space-y-2">
            <a href="#features" @click="mobileOpen = false" class="block px-4 py-2 text-base font-medium text-neutral-700 hover:bg-neutral-50 rounded-md">{{ __('marketing.header.features') }}</a>
            <a href="#pricing" @click="mobileOpen = false" class="block px-4 py-2 text-base font-medium text-neutral-700 hover:bg-neutral-50 rounded-md">{{ __('marketing.header.pricing') }}</a>
            <a href="#security" @click="mobileOpen = false" class="block px-4 py-2 text-base font-medium text-neutral-700 hover:bg-neutral-50 rounded-md">{{ __('marketing.header.security') }}</a>
            <a href="#faq" @click="mobileOpen = false" class="block px-4 py-2 text-base font-medium text-neutral-700 hover:bg-neutral-50 rounded-md">{{ __('marketing.header.faq') }}</a>
            <div class="border-t border-neutral-200 pt-4 mt-4 space-y-2">
                <a href="{{ app()->getLocale() === 'ar' ? '/' : '/ar' }}" class="block px-4 py-2 text-base font-medium text-neutral-700 hover:bg-neutral-50 rounded-md">{{ __('marketing.header.locale_toggle') }}</a>
                <a href="/login" class="block px-4 py-2 text-base font-medium text-neutral-700 hover:bg-neutral-50 rounded-md">{{ __('marketing.header.sign_in') }}</a>
                <a href="/register?utm_source=landing_header" class="block mx-4 text-center px-4 py-2 bg-brand-500 text-white text-sm font-semibold rounded-lg hover:bg-brand-600">{{ __('marketing.header.start_trial') }}</a>
            </div>
        </div>
    </nav>
</header>
{{-- Spacer for fixed header --}}
<div class="h-[72px]" aria-hidden="true"></div>
