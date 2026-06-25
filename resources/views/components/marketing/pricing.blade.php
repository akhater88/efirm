@php
    $tiers = config('marketing.pricing_tiers', []);
    $foundingBadgeEnabled = config('marketing.founding_firm_badge_enabled', true);
    $locale = app()->getLocale();
@endphp

<section id="pricing" aria-labelledby="pricing-heading" class="py-12 md:py-16 lg:py-24 px-6 lg:px-8 bg-neutral-50">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="max-w-3xl mx-auto text-center mb-12 lg:mb-16">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-wider mb-4">
                {{ __('marketing.pricing.eyebrow') }}
            </p>
            <h2 id="pricing-heading" class="text-2xl md:text-3xl lg:text-4xl font-bold text-neutral-900 mb-4">
                {{ __('marketing.pricing.headline') }}
            </h2>
            <p class="text-lg text-neutral-600">
                {{ __('marketing.pricing.body') }}
            </p>
        </div>

        {{-- Pricing cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto mb-16">
            @foreach (['starter', 'pro', 'enterprise'] as $tierSlug)
                @php $tier = $tiers[$tierSlug] ?? []; @endphp
                <div class="relative bg-white rounded-xl p-8 border {{ $tierSlug === 'pro' ? 'border-brand-500 border-2 shadow-lg scale-100 md:scale-105' : 'border-neutral-200' }}">
                    {{-- Founding-firm badge (Pro only) --}}
                    @if ($tierSlug === 'pro' && $foundingBadgeEnabled)
                        <div class="absolute -top-4 inset-x-4 flex justify-center">
                            <span class="inline-flex items-center px-4 py-1.5 bg-warning-50 text-warning-700 text-xs font-semibold rounded-full border border-warning-500/20">
                                {{ __('marketing.pricing.founding_badge') }}
                            </span>
                        </div>
                    @endif

                    {{-- Most Popular tag --}}
                    @if ($tierSlug === 'pro')
                        <div class="flex justify-center mb-2">
                            <span class="inline-flex items-center px-3 py-1 bg-brand-50 text-brand-700 text-xs font-semibold rounded-full">
                                {{ __('marketing.pricing.most_popular') }}
                            </span>
                        </div>
                    @endif

                    {{-- Tier name --}}
                    <h3 class="text-xl font-semibold text-neutral-900 text-center mb-4">
                        {{ __("marketing.pricing.tiers.{$tierSlug}") }}
                    </h3>

                    {{-- Price --}}
                    <div class="text-center mb-2">
                        <span class="text-5xl font-bold text-neutral-900">${{ $tier['price_usd'] ?? '0' }}</span>
                    </div>
                    <p class="text-sm text-neutral-500 text-center mb-2">
                        {{ __('marketing.pricing.per_seat') }}
                    </p>

                    {{-- JOD equivalent (AR only) --}}
                    @if ($locale === 'ar' && isset($tier['jod_equivalent']))
                        <p class="text-xs text-neutral-400 text-center mb-6" dir="ltr">
                            {{ __('marketing.pricing.per_seat') }} JOD ~{{ $tier['jod_equivalent'] }}
                        </p>
                    @else
                        <div class="mb-6"></div>
                    @endif

                    {{-- Feature bullets --}}
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ __('marketing.pricing.features.seats') }}: {{ $tier['seats'] ?? '-' }}
                        </li>
                        <li class="flex items-center gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ __('marketing.pricing.features.matters') }}: {{ $tier['matters'] ?? '-' }}
                        </li>
                        <li class="flex items-center gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ __('marketing.pricing.features.storage') }}: {{ $tier['storage_gb'] ?? '-' }} GB
                        </li>
                        <li class="flex items-center gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ __('marketing.pricing.features.ai_requests') }}: {{ $tier['ai_requests'] ?? '-' }}
                        </li>
                        <li class="flex items-center gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ __('marketing.pricing.features.audit_retention') }}: {{ $tier['audit_retention'] ?? '-' }}
                        </li>
                        <li class="flex items-center gap-2 text-sm text-neutral-700">
                            @if ($tier['trust_ledger'] ?? false)
                                <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            @else
                                <svg class="w-4 h-4 text-neutral-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            @endif
                            {{ __('marketing.pricing.features.trust_ledger') }}
                        </li>
                    </ul>

                    {{-- CTA --}}
                    <a href="/register?utm_source=pricing_table&plan={{ $tierSlug }}"
                       class="block w-full text-center px-6 py-3 {{ $tierSlug === 'pro' ? 'bg-brand-500 text-white hover:bg-brand-600' : 'bg-white border-2 border-brand-500 text-brand-500 hover:bg-brand-50' }} text-sm font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 min-h-[44px]">
                        {{ __('marketing.pricing.start_trial') }}
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Feature comparison matrix --}}
        <div class="max-w-5xl mx-auto">
            <h3 class="text-xl font-semibold text-neutral-900 text-center mb-8">
                {{ __('marketing.pricing.feature_matrix_title') }}
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200">
                            <th class="text-start rtl:text-end py-3 pe-4 font-medium text-neutral-500 sticky start-0 bg-neutral-50">{{ __('marketing.pricing.features.seats') }}</th>
                            @foreach (['starter', 'pro', 'enterprise'] as $tierSlug)
                                <th class="py-3 px-4 font-semibold text-neutral-900 text-center">{{ __("marketing.pricing.tiers.{$tierSlug}") }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (['seats', 'matters', 'storage', 'ai_requests', 'audit_retention', 'trust_ledger', 'pdpl_compliance', 'frankfurt_residency'] as $feature)
                            <tr class="border-b border-neutral-100">
                                <td class="py-3 pe-4 font-medium text-neutral-700 sticky start-0 bg-neutral-50">{{ __("marketing.pricing.features.{$feature}") }}</td>
                                @foreach (['starter', 'pro', 'enterprise'] as $tierSlug)
                                    @php $value = $tiers[$tierSlug][$feature] ?? null; @endphp
                                    <td class="py-3 px-4 text-center text-neutral-600">
                                        @if (is_bool($value))
                                            @if ($value)
                                                <svg class="w-5 h-5 text-brand-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                            @else
                                                <svg class="w-5 h-5 text-neutral-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            @endif
                                        @elseif ($feature === 'storage')
                                            {{ $value }} GB
                                        @else
                                            {{ $value ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
