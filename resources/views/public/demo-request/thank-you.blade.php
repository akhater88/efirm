<x-public.layouts.marketing>
    <x-marketing.header />

    <main id="main-content" role="main" class="py-24 md:py-32 px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center">
            {{-- Checkmark icon --}}
            <div class="w-16 h-16 bg-success-50 rounded-full flex items-center justify-center mx-auto mb-8">
                <svg class="w-8 h-8 text-success-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4">
                {{ __('marketing.thank_you.headline') }}
            </h1>
            <p class="text-lg text-neutral-600 mb-8">
                {{ __('marketing.thank_you.body') }}
            </p>
            <a href="{{ app()->getLocale() === 'ar' ? '/ar' : '/' }}"
               class="inline-flex items-center px-6 py-3 bg-brand-500 text-white text-base font-semibold rounded-lg hover:bg-brand-600 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 min-h-[44px]">
                {{ __('marketing.thank_you.return_home') }}
            </a>
        </div>
    </main>

    <x-marketing.footer />
</x-public.layouts.marketing>
