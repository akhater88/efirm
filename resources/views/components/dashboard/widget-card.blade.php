<div style="
    background: var(--surface-card, #FFFFFF);
    border: 1px solid var(--border-default, #E7E5E4);
    border-radius: 8px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    min-height: 280px;
">
    {{-- Header --}}
    <div style="
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-default, #E7E5E4);
    ">
        <div style="display: flex; align-items: center; gap: 8px;">
            @if ($icon)
                <span style="color: var(--text-tertiary, #78716C); display: flex; align-items: center;">
                    {{ $icon }}
                </span>
            @endif
            <h3 style="font-size: 14px; font-weight: 600; color: var(--text-primary, #1C1917); margin: 0;">
                {{ $title }}
            </h3>
        </div>
        @if (isset($headerAction))
            {{ $headerAction }}
        @endif
    </div>

    {{-- Body --}}
    <div style="flex: 1; padding: 0;">
        @if ($state === 'loading')
            <div style="display: flex; align-items: center; justify-content: center; padding: 48px 16px; color: var(--text-tertiary, #78716C); font-size: 13px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite; margin-inline-end: 8px;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                {{ __('common.loading') }}
            </div>
        @elseif ($state === 'error')
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 16px; text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-danger-500, #DC2626)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 8px;"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <p style="font-size: 13px; color: var(--color-danger-700, #B91C1C); margin: 0;">
                    {{ $errorMessage ?: __('common.error_occurred') }}
                </p>
            </div>
        @elseif ($state === 'empty')
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 16px; text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--text-tertiary, #78716C)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 8px; opacity: 0.5;"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/></svg>
                <p style="font-size: 13px; color: var(--text-tertiary, #78716C); margin: 0;">
                    {{ $emptyMessage ?: __('common.no_items') }}
                </p>
                @if ($createUrl)
                    <a href="{{ $createUrl }}" style="margin-top: 12px; font-size: 13px; font-weight: 500; color: var(--text-link, #0D5C2E); text-decoration: none;">
                        + {{ $createLabel ?: __('common.create') }}
                    </a>
                @endif
            </div>
        @else
            {{ $slot }}
        @endif
    </div>

    {{-- Footer --}}
    @if ($viewAllUrl || $createUrl)
        <div style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            border-top: 1px solid var(--border-default, #E7E5E4);
        ">
            @if ($viewAllUrl)
                <a href="{{ $viewAllUrl }}" style="font-size: 13px; font-weight: 500; color: var(--text-link, #0D5C2E); text-decoration: none;">
                    {{ $viewAllLabel ?: __('common.view_all') }}
                </a>
            @else
                <span></span>
            @endif

            @if ($createUrl && $state !== 'empty')
                <a href="{{ $createUrl }}" style="font-size: 13px; font-weight: 500; color: var(--text-link, #0D5C2E); text-decoration: none;">
                    + {{ $createLabel ?: __('common.create') }}
                </a>
            @endif
        </div>
    @endif
</div>

@once
<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endonce
