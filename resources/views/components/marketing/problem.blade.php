<section aria-labelledby="problem-heading" class="py-12 md:py-16 lg:py-24 px-6 lg:px-8">
    <div class="max-w-3xl mx-auto text-center mb-12 lg:mb-16">
        <p class="text-sm font-semibold text-brand-500 uppercase tracking-wider mb-4">
            {{ __('marketing.problem.eyebrow') }}
        </p>
        <h2 id="problem-heading" class="text-2xl md:text-3xl lg:text-4xl font-bold text-neutral-900 mb-4">
            {{ __('marketing.problem.headline') }}
        </h2>
        <p class="text-lg text-neutral-600">
            {{ __('marketing.problem.intro') }}
        </p>
    </div>

    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach (__('marketing.problem.cards') as $index => $card)
            <div class="bg-white rounded-xl p-8 border border-neutral-200">
                {{-- Icon --}}
                <div class="w-12 h-12 bg-danger-50 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-danger-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-neutral-900 mb-3">
                    {{ $card['title'] }}
                </h3>
                <p class="text-base text-neutral-600">
                    {{ $card['body'] }}
                </p>
            </div>
        @endforeach
    </div>
</section>
