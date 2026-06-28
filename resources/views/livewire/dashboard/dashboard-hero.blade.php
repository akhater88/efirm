<section style="margin-bottom: 24px;">
    {{-- Greeting + Date --}}
    <div style="margin-bottom: 16px;">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #2D0A0A); margin: 0 0 4px;">
            {{ $greeting }}
        </h1>
        <p style="font-size: 14px; color: var(--text-tertiary, #7A5050); margin: 0;">
            {{ $formattedDate }}
        </p>
    </div>

    {{-- AI Twin Card --}}
    <button
        wire:click="$set('showAiTwinModal', true)"
        style="
            width: 100%;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: linear-gradient(135deg, var(--color-brand-700, #330000) 0%, var(--color-brand-500, #520000) 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-align: start;
        "
    >
        {{-- AI icon --}}
        <div style="flex-shrink: 0; width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
        </div>
        <div style="flex: 1; min-width: 0;">
            <div style="font-size: 16px; font-weight: 600; color: #FFFFFF; margin-bottom: 2px;">
                {{ __('brand.ai_twin_title') }} — {{ __('brand.ai_twin_coming_soon') }}
            </div>
            <div style="font-size: 14px; color: rgba(255,255,255,0.8);">
                {{ __('brand.ai_twin_cta') }}
            </div>
        </div>
        {{-- Arrow --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="{{ app()->getLocale() === 'ar' ? 'transform: rotate(180deg);' : '' }}"><path d="m9 18 6-6-6-6"/></svg>
    </button>
</section>

{{-- AI Twin Modal --}}
@if ($showAiTwinModal)
    <div
        x-data
        x-init="$nextTick(() => $refs.emailInput?.focus())"
        @keydown.escape.window="$wire.set('showAiTwinModal', false)"
        style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px;"
        role="dialog"
        aria-modal="true"
        aria-labelledby="ai-twin-modal-title"
    >
        {{-- Backdrop --}}
        <div
            wire:click="$set('showAiTwinModal', false)"
            style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.4);"
        ></div>

        {{-- Modal --}}
        <div style="position: relative; background: #FFFFFF; border-radius: 16px; box-shadow: var(--shadow-xl); width: 100%; max-width: 440px; overflow: hidden;">
            {{-- Header --}}
            <div style="padding: 20px 24px 0;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div style="width: 40px; height: 40px; background: var(--color-brand-50, #FDF2F2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-brand-500, #520000)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
                    </div>
                    <div>
                        <h2 id="ai-twin-modal-title" style="font-size: 18px; font-weight: 700; color: var(--text-primary, #2D0A0A); margin: 0;">
                            {{ __('brand.ai_twin_title') }}
                        </h2>
                        <p style="font-size: 13px; color: var(--text-tertiary, #7A5050); margin: 0;">
                            {{ __('brand.ai_twin_coming_soon') }}
                        </p>
                    </div>
                </div>
                <p style="font-size: 14px; color: var(--text-secondary, #4A2020); margin: 12px 0 0; line-height: 1.5;">
                    {{ __('dashboard.ai_twin_modal_description') }}
                </p>
            </div>

            {{-- Form --}}
            <form wire:submit="submitWaitlist" style="padding: 16px 24px 24px;">
                <label for="waitlist-email" style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 6px;">
                    {{ __('brand.ai_twin_email_placeholder') }}
                </label>
                <input
                    x-ref="emailInput"
                    id="waitlist-email"
                    type="email"
                    wire:model="waitlistEmail"
                    dir="ltr"
                    placeholder="you@firm.com"
                    style="
                        width: 100%;
                        padding: 10px 12px;
                        border: 1px solid var(--border-default, #E7E5E4);
                        border-radius: 8px;
                        font-size: 14px;
                        color: var(--text-primary, #2D0A0A);
                        outline: none;
                        box-sizing: border-box;
                    "
                    onfocus="this.style.borderColor='var(--border-focus, #520000)'; this.style.boxShadow='var(--ring-brand, 0 0 0 3px rgba(13, 92, 46, 0.2))'"
                    onblur="this.style.borderColor='var(--border-default, #E7E5E4)'; this.style.boxShadow='none'"
                >
                @error('waitlistEmail')
                    <p style="font-size: 12px; color: var(--color-danger-500, #DC2626); margin: 4px 0 0;">{{ $message }}</p>
                @enderror

                <div style="display: flex; gap: 8px; margin-top: 16px;">
                    <button
                        type="button"
                        wire:click="$set('showAiTwinModal', false)"
                        style="flex: 1; padding: 10px; background: #FFFFFF; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; font-size: 14px; font-weight: 500; color: var(--text-secondary, #4A2020); cursor: pointer;"
                    >
                        {{ __('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        style="flex: 1; padding: 10px; background: var(--color-brand-500, #520000); border: none; border-radius: 8px; font-size: 14px; font-weight: 500; color: #FFFFFF; cursor: pointer;"
                    >
                        {{ __('brand.ai_twin_submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
