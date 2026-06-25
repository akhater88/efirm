<x-public.layouts.marketing>
    <x-marketing.header />

    <main id="main-content" role="main" class="py-12 md:py-16 lg:py-24 px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4 text-center">
                {{ __('marketing.demo_form.title') }}
            </h1>
            <p class="text-lg text-neutral-600 mb-8 text-center">
                {{ __('marketing.demo_form.intro') }}
            </p>

            <form method="POST" action="/api/v1/public/demo-requests" class="space-y-6" id="demo-form">
                @csrf

                {{-- Honeypot --}}
                <div aria-hidden="true" class="hidden">
                    <input type="text" name="company_website" tabindex="-1" autocomplete="off" value="">
                </div>

                <input type="hidden" name="locale" value="{{ app()->getLocale() }}">

                {{-- Full Name --}}
                <div>
                    <label for="full_name" class="block text-sm font-medium text-neutral-700 mb-1">
                        {{ __('marketing.demo_form.full_name') }} <span class="text-danger-500">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" required maxlength="120"
                           placeholder="{{ __('marketing.demo_form.full_name_placeholder') }}"
                           class="w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 min-h-[44px]">
                </div>

                {{-- Firm Name --}}
                <div>
                    <label for="firm_name" class="block text-sm font-medium text-neutral-700 mb-1">
                        {{ __('marketing.demo_form.firm_name') }} <span class="text-danger-500">*</span>
                    </label>
                    <input type="text" id="firm_name" name="firm_name" required maxlength="200"
                           placeholder="{{ __('marketing.demo_form.firm_name_placeholder') }}"
                           class="w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 min-h-[44px]">
                </div>

                {{-- Number of Lawyers --}}
                <div>
                    <label for="lawyer_count" class="block text-sm font-medium text-neutral-700 mb-1">
                        {{ __('marketing.demo_form.lawyer_count') }} <span class="text-danger-500">*</span>
                    </label>
                    <select id="lawyer_count" name="lawyer_count" required
                            class="w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 min-h-[44px]">
                        <option value="">{{ __('marketing.demo_form.lawyer_count_placeholder') }}</option>
                        @foreach (__('marketing.demo_form.lawyer_count_options') as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Work Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-neutral-700 mb-1">
                        {{ __('marketing.demo_form.email') }} <span class="text-danger-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required maxlength="254" dir="ltr"
                           placeholder="{{ __('marketing.demo_form.email_placeholder') }}"
                           class="w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 min-h-[44px]">
                </div>

                {{-- Phone Number --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-neutral-700 mb-1">
                        {{ __('marketing.demo_form.phone') }}
                    </label>
                    <input type="tel" id="phone" name="phone" maxlength="30" dir="ltr"
                           placeholder="{{ __('marketing.demo_form.phone_placeholder') }}"
                           class="w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 min-h-[44px]">
                    <p class="text-xs text-neutral-500 mt-1">{{ __('marketing.demo_form.phone_helper') }}</p>
                </div>

                {{-- Country --}}
                <div>
                    <label for="country" class="block text-sm font-medium text-neutral-700 mb-1">
                        {{ __('marketing.demo_form.country') }} <span class="text-danger-500">*</span>
                    </label>
                    <select id="country" name="country" required
                            class="w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 min-h-[44px]">
                        <option value="">{{ __('marketing.demo_form.country_placeholder') }}</option>
                        @foreach (__('marketing.demo_form.country_options') as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Notes --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-neutral-700 mb-1">
                        {{ __('marketing.demo_form.notes') }}
                    </label>
                    <textarea id="notes" name="notes" rows="4" maxlength="1000"
                              placeholder="{{ __('marketing.demo_form.notes_placeholder') }}"
                              class="w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-y"></textarea>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full px-6 py-3 bg-brand-500 text-white text-base font-semibold rounded-lg hover:bg-brand-600 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 min-h-[44px]">
                    {{ __('marketing.demo_form.submit') }}
                </button>

                <p class="text-xs text-neutral-500 text-center">
                    {{ __('marketing.demo_form.privacy_note') }}
                </p>
            </form>
        </div>
    </main>

    <x-marketing.footer />
</x-public.layouts.marketing>
