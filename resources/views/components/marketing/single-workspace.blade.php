<section aria-labelledby="solution-heading" class="py-12 md:py-16 lg:py-24 px-6 lg:px-8 bg-neutral-50">
    <div class="max-w-7xl mx-auto">
        <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-center">
            {{-- Text zone --}}
            <div class="lg:col-span-5 text-center lg:text-start rtl:lg:text-end mb-12 lg:mb-0">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-wider mb-4">
                    {{ __('marketing.solution.eyebrow') }}
                </p>
                <h2 id="solution-heading" class="text-2xl md:text-3xl lg:text-4xl font-bold text-neutral-900 mb-4">
                    {{ __('marketing.solution.headline') }}
                </h2>
                <p class="text-lg text-neutral-600 mb-8">
                    {{ __('marketing.solution.body') }}
                </p>
                <ul class="space-y-3 text-start rtl:text-end">
                    @foreach (__('marketing.solution.bullets') as $bullet)
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span class="text-neutral-700">{{ $bullet }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Visual zone --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-neutral-200">
                    <div class="aspect-[16/10] bg-neutral-100 flex items-center justify-center">
                        <img src="/img/hero-screenshot.webp"
                             alt="{{ __('marketing.solution.screenshot_alt') }}"
                             loading="lazy"
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
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
</section>
