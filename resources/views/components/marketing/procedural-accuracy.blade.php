<section aria-labelledby="procedure-heading" class="py-12 md:py-16 lg:py-24 px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-center">
            {{-- Text zone --}}
            <div class="lg:col-span-6 text-center lg:text-start rtl:lg:text-end mb-12 lg:mb-0">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-wider mb-4">
                    {{ __('marketing.procedure.eyebrow') }}
                </p>
                <h2 id="procedure-heading" class="text-2xl md:text-3xl lg:text-4xl font-bold text-neutral-900 mb-4">
                    {{ __('marketing.procedure.headline') }}
                </h2>
                <p class="text-lg text-neutral-600 mb-8">
                    {{ __('marketing.procedure.body') }}
                </p>
                <ul class="space-y-4 text-start rtl:text-end">
                    @foreach (__('marketing.procedure.claims') as $claim)
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span class="text-neutral-700">{{ $claim }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Visual zone --}}
            <div class="lg:col-span-6 hidden md:block">
                <div class="bg-neutral-50 rounded-xl p-8 border border-neutral-200">
                    {{-- Stylised timeline SVG --}}
                    <div class="space-y-6">
                        @php $steps = ['Filing', 'First Hearing', 'Evidence', 'Verdict', 'Appeal Window']; @endphp
                        @foreach ($steps as $i => $step)
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full {{ $i < 3 ? 'bg-brand-500' : 'bg-neutral-300' }} flex items-center justify-center flex-shrink-0">
                                    @if ($i < 3)
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    @else
                                        <span class="text-sm font-medium text-white">{{ $i + 1 }}</span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="h-2 rounded-full {{ $i < 3 ? 'bg-brand-200' : 'bg-neutral-200' }}"></div>
                                </div>
                                <span class="text-sm font-medium {{ $i < 3 ? 'text-brand-700' : 'text-neutral-500' }}">{{ $step }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
