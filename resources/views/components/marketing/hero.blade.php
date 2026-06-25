<section aria-labelledby="hero-heading" class="relative bg-gradient-to-b from-neutral-50 to-white py-12 md:py-16 lg:py-24 px-6 lg:px-16">
    <div class="max-w-7xl mx-auto">
        <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-center">
            {{-- Text zone --}}
            <div class="lg:col-span-7 text-center lg:text-start rtl:lg:text-end">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-wider mb-4">
                    {{ __('marketing.hero.eyebrow') }}
                </p>
                <h1 id="hero-heading" class="text-3xl md:text-4xl lg:text-5xl font-bold text-neutral-900 leading-tight mb-6">
                    {{ __('marketing.hero.headline') }}
                </h1>
                <p class="text-lg md:text-xl text-neutral-600 mb-8 max-w-2xl mx-auto lg:mx-0 rtl:lg:ms-auto rtl:lg:me-0">
                    {{ __('marketing.hero.sub_headline') }}
                </p>

                {{-- CTAs --}}
                <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start rtl:lg:justify-end mb-6">
                    <a href="/register?utm_source=landing_hero"
                       class="inline-flex items-center justify-center px-6 py-3 bg-brand-500 text-white text-base font-semibold rounded-lg hover:bg-brand-600 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 min-w-[200px] min-h-[44px]">
                        {{ __('marketing.hero.primary_cta') }}
                    </a>
                    <a href="/demo-request?utm_source=landing_hero_demo"
                       class="inline-flex items-center justify-center px-6 py-3 border-2 border-brand-500 text-brand-500 text-base font-semibold rounded-lg hover:bg-brand-50 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 min-w-[200px] min-h-[44px]">
                        {{ __('marketing.hero.secondary_cta') }}
                    </a>
                </div>

                <p class="text-sm text-neutral-500">
                    {{ __('marketing.hero.trust_line') }}
                </p>
            </div>

            {{-- Visual zone --}}
            <div class="lg:col-span-5 mt-12 lg:mt-0">
                <div class="relative">
                    {{-- Browser chrome frame --}}
                    <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-neutral-200">
                        <div class="bg-neutral-100 px-4 py-3 flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="aspect-[3/2] bg-neutral-100 relative">
                            <img src="/img/hero-screenshot.webp"
                                 alt="{{ __('marketing.hero.screenshot_alt') }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            {{-- CSS fallback if image 404 --}}
                            <div class="w-full h-full bg-neutral-100 items-center justify-center hidden" style="display: none;">
                                <svg class="w-16 h-16 text-neutral-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
