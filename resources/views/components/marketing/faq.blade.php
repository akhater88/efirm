@php
    $faqItems = __('marketing.faq.items');
    if (!is_array($faqItems)) $faqItems = [];
    $faqItems = array_slice($faqItems, 0, 15); // Max 15 items
@endphp

@if (count($faqItems) > 0)
<section id="faq" aria-labelledby="faq-heading" class="py-12 md:py-16 lg:py-24 px-6 lg:px-8 bg-neutral-50">
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-12 lg:mb-16">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-wider mb-4">
                {{ __('marketing.faq.eyebrow') }}
            </p>
            <h2 id="faq-heading" class="text-2xl md:text-3xl lg:text-4xl font-bold text-neutral-900">
                {{ __('marketing.faq.headline') }}
            </h2>
        </div>

        {{-- Accordion --}}
        <div x-data="{
            openItems: [],
            isMobile: window.innerWidth < 1024,
            init() {
                this.checkMobile();
                window.addEventListener('resize', () => this.checkMobile());
            },
            checkMobile() {
                this.isMobile = window.innerWidth < 1024;
            },
            toggle(index) {
                if (this.isMobile) {
                    // Single-open on mobile
                    this.openItems = this.openItems.includes(index) ? [] : [index];
                } else {
                    // Multi-open on desktop
                    if (this.openItems.includes(index)) {
                        this.openItems = this.openItems.filter(i => i !== index);
                    } else {
                        this.openItems.push(index);
                    }
                }
            },
            isOpen(index) {
                return this.openItems.includes(index);
            }
        }" class="space-y-3">
            @foreach ($faqItems as $index => $item)
                <div class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
                    <h3>
                        <button @click="toggle({{ $index }})"
                                :aria-expanded="isOpen({{ $index }}).toString()"
                                aria-controls="faq-answer-{{ $index }}"
                                class="w-full flex items-center justify-between px-6 py-4 text-start rtl:text-end text-neutral-900 font-medium hover:bg-neutral-50 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 rounded-xl min-h-[44px]"
                                id="faq-question-{{ $index }}">
                            <span>{{ $item['question'] }}</span>
                            <svg class="w-5 h-5 text-neutral-500 flex-shrink-0 ms-4 transition-transform duration-200"
                                 :class="{ 'rotate-180': isOpen({{ $index }}) }"
                                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </h3>
                    <div x-show="isOpen({{ $index }})"
                         x-collapse
                         id="faq-answer-{{ $index }}"
                         role="region"
                         aria-labelledby="faq-question-{{ $index }}">
                        <div class="px-6 pb-4 text-neutral-600 leading-relaxed">
                            {{ $item['answer'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
