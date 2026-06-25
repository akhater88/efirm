<x-public.layouts.marketing>
    <x-marketing.header />

    <main id="main-content" role="main" class="py-12 md:py-16 lg:py-24 px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            {{-- Disclaimer banner --}}
            <div class="bg-warning-50 border border-warning-500/20 rounded-lg p-4 mb-8">
                <p class="text-sm text-warning-700">
                    {{ __('marketing.legal.stub_disclaimer') }}
                </p>
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-neutral-900 mb-2">
                {{ $title }}
            </h1>
            <p class="text-sm text-neutral-500 mb-8">
                {{ __('marketing.legal.last_updated', ['date' => '2026-06-24']) }}
            </p>

            <div class="prose prose-neutral max-w-none
                        prose-headings:text-neutral-900
                        prose-p:text-neutral-600
                        prose-a:text-brand-600 prose-a:no-underline hover:prose-a:underline
                        prose-strong:text-neutral-900
                        prose-ul:text-neutral-600
                        prose-ol:text-neutral-600">
                {!! $content !!}
            </div>
        </div>
    </main>

    <x-marketing.footer />
</x-public.layouts.marketing>
