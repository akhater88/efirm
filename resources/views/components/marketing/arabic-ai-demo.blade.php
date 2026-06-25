<section aria-labelledby="ai-demo-heading" class="py-12 md:py-16 lg:py-24 px-6 lg:px-8 bg-neutral-900">
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-12 lg:mb-16">
            <p class="text-sm font-semibold text-brand-300 uppercase tracking-wider mb-4">
                {{ __('marketing.ai_demo.eyebrow') }}
            </p>
            <h2 id="ai-demo-heading" class="text-2xl md:text-3xl lg:text-4xl font-bold text-white mb-4">
                {{ __('marketing.ai_demo.headline') }}
            </h2>
            <p class="text-lg text-neutral-300 max-w-3xl mx-auto">
                {{ __('marketing.ai_demo.body') }}
            </p>
        </div>

        {{-- Demo Card --}}
        <div class="bg-neutral-800 rounded-xl overflow-hidden border border-neutral-700">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                {{-- Prompt side --}}
                <div class="p-6 lg:p-8 border-b lg:border-b-0 lg:border-e border-neutral-700">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        <span class="text-sm font-medium text-brand-400">{{ __('marketing.ai_demo.prompt_label') }}</span>
                    </div>
                    <p class="text-neutral-200 leading-relaxed font-arabic" dir="rtl" lang="ar">
                        {{ __('marketing.ai_demo.prompt_content') }}
                    </p>
                </div>

                {{-- Output side --}}
                <div class="p-6 lg:p-8 bg-neutral-800/50">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>
                        <span class="text-sm font-medium text-brand-400">{{ __('marketing.ai_demo.output_label') }}</span>
                    </div>
                    <div class="text-neutral-200 leading-relaxed font-arabic whitespace-pre-line" dir="rtl" lang="ar">{{ __('marketing.ai_demo.output_content') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>
