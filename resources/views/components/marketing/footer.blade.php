<footer role="contentinfo" class="bg-neutral-50 border-t border-neutral-200 py-12 md:py-16 px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 mb-12">
            {{-- Brand / Address --}}
            <div>
                <a href="{{ app()->getLocale() === 'ar' ? '/ar' : '/' }}" class="inline-block mb-4">
                    <img src="{{ asset('img/brand/efirm-favicon.svg') }}" alt="eFirm" class="h-8 w-auto">
                </a>
                <p class="text-sm text-neutral-600 mb-2">
                    {{ __('marketing.footer.brand_description') }}
                </p>
                <p class="text-sm text-neutral-500">
                    {{ __('marketing.footer.address') }}
                </p>
            </div>

            {{-- Product links --}}
            <div>
                <h3 class="text-sm font-semibold text-neutral-900 mb-4">
                    {{ __('marketing.footer.product.title') }}
                </h3>
                <ul class="space-y-2">
                    <li><a href="#features" class="text-sm text-neutral-600 hover:text-neutral-900 transition-colors">{{ __('marketing.footer.product.features') }}</a></li>
                    <li><a href="#pricing" class="text-sm text-neutral-600 hover:text-neutral-900 transition-colors">{{ __('marketing.footer.product.pricing') }}</a></li>
                    <li><a href="#security" class="text-sm text-neutral-600 hover:text-neutral-900 transition-colors">{{ __('marketing.footer.product.security') }}</a></li>
                    <li><a href="#faq" class="text-sm text-neutral-600 hover:text-neutral-900 transition-colors">{{ __('marketing.footer.product.faq') }}</a></li>
                </ul>
            </div>

            {{-- Legal links --}}
            <div>
                <h3 class="text-sm font-semibold text-neutral-900 mb-4">
                    {{ __('marketing.footer.legal.title') }}
                </h3>
                <ul class="space-y-2">
                    <li><a href="{{ app()->getLocale() === 'ar' ? '/ar/terms' : '/terms' }}" class="text-sm text-neutral-600 hover:text-neutral-900 transition-colors">{{ __('marketing.footer.legal.terms') }}</a></li>
                    <li><a href="{{ app()->getLocale() === 'ar' ? '/ar/privacy' : '/privacy' }}" class="text-sm text-neutral-600 hover:text-neutral-900 transition-colors">{{ __('marketing.footer.legal.privacy') }}</a></li>
                    <li><a href="{{ app()->getLocale() === 'ar' ? '/ar/dpa' : '/dpa' }}" class="text-sm text-neutral-600 hover:text-neutral-900 transition-colors">{{ __('marketing.footer.legal.dpa') }}</a></li>
                    <li><a href="{{ app()->getLocale() === 'ar' ? '/ar/ai-disclaimer' : '/ai-disclaimer' }}" class="text-sm text-neutral-600 hover:text-neutral-900 transition-colors">{{ __('marketing.footer.legal.ai_disclaimer') }}</a></li>
                </ul>
            </div>

            {{-- Company / Contact --}}
            <div>
                <h3 class="text-sm font-semibold text-neutral-900 mb-4">
                    {{ __('marketing.footer.company.title') }}
                </h3>
                <ul class="space-y-2">
                    <li><span class="text-sm text-neutral-600">{{ __('marketing.footer.company.contact') }}</span></li>
                    <li><a href="mailto:{{ __('marketing.footer.company.email') }}" class="text-sm text-brand-600 hover:text-brand-700 transition-colors">{{ __('marketing.footer.company.email') }}</a></li>
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-neutral-200 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-neutral-500">
                {{ str_replace(':year', date('Y'), __('marketing.footer.copyright')) }}
            </p>
            <div class="flex items-center gap-4">
                <p class="text-xs text-neutral-400">
                    {{ __('marketing.footer.legal_stub_disclaimer') }}
                </p>
                <button type="button"
                        class="text-sm text-neutral-600 hover:text-neutral-900 transition-colors underline"
                        onclick="document.dispatchEvent(new CustomEvent('open-cookie-settings'))">
                    {{ __('marketing.footer.cookie_settings') }}
                </button>
            </div>
        </div>
    </div>
</footer>
