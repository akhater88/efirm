@php
    $icons = [
        'briefcase' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>',
        'users' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'file-text' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>',
        'check-circle' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>',
        'alert-circle' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
        'book-open' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
        'clock' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    ];
@endphp

<aside class="quick-links-rail" style="
    position: fixed;
    top: 56px;
    {{ app()->getLocale() === 'ar' ? 'left: 0;' : 'right: 0;' }}
    bottom: 0;
    width: 72px;
    background: var(--surface-card, #FFFFFF);
    border-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: 1px solid var(--border-default, #E7E5E4);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px 0;
    gap: 4px;
    z-index: 20;
    overflow-y: auto;
">
    <div style="font-size: 10px; font-weight: 600; color: var(--text-tertiary, #78716C); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; writing-mode: horizontal-tb;">
        {{ __('shell.quick_links') }}
    </div>

    @foreach ($links as $link)
        <a
            href="{{ $link['url'] }}"
            style="
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 3px;
                padding: 8px 4px;
                border-radius: 6px;
                text-decoration: none;
                color: var(--text-tertiary, #78716C);
                width: 60px;
                text-align: center;
            "
            title="{{ $link['label'] }}"
            onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'; this.style.color='var(--color-brand-500, #0D5C2E)'"
            onmouseout="this.style.background='transparent'; this.style.color='var(--text-tertiary, #78716C)'"
        >
            <span style="display: flex; align-items: center; justify-content: center;">
                {!! $icons[$link['icon']] !!}
            </span>
            <span style="font-size: 10px; font-weight: 500; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 56px;">
                {{ $link['label'] }}
            </span>
        </a>
    @endforeach
</aside>

@once
<style>
    @media (max-width: 1279px) {
        .quick-links-rail {
            display: none !important;
        }
    }
</style>
@endonce
