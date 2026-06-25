@php
    $icons = [
        'home' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
        'briefcase' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>',
        'users' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'check-circle' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>',
        'file-text' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>',
        'alert-circle' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
        'book-open' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
        'list' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>',
        'clock' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        'settings' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
    ];
@endphp

<aside
    style="
        position: fixed;
        top: 56px;
        {{ app()->getLocale() === 'ar' ? 'right: 0;' : 'left: 0;' }}
        bottom: 0;
        width: {{ $collapsed ? '64px' : '240px' }};
        background: var(--surface-sidebar, #072E17);
        transition: width 0.2s ease;
        display: flex;
        flex-direction: column;
        z-index: 30;
        overflow-y: auto;
        overflow-x: hidden;
    "
>
    {{-- Logo block --}}
    <div style="padding: 16px {{ $collapsed ? '12px' : '16px' }}; display: flex; align-items: center; {{ $collapsed ? 'justify-content: center;' : '' }} border-bottom: 1px solid rgba(255,255,255,0.1);">
        @if ($collapsed)
            <img src="{{ asset('img/brand/efirm-mark-reversed.svg') }}" alt="{{ __('brand.mark_alt') }}" style="width: 32px; height: 32px;">
        @else
            <img src="{{ asset('img/brand/efirm-horizontal-compact-reversed.svg') }}" alt="{{ __('brand.logo_alt_dark') }}" style="height: 32px; width: auto;">
        @endif
    </div>

    {{-- Navigation --}}
    <nav style="flex: 1; padding: 8px 0;">
        @foreach ($navGroups as $groupIndex => $group)
            @if ($groupIndex > 0)
                <div style="height: 1px; background: rgba(255,255,255,0.08); margin: 8px {{ $collapsed ? '8px' : '12px' }};"></div>
            @endif

            @if (!$collapsed)
                <div style="padding: 8px 16px 4px; font-size: 11px; font-weight: 600; color: var(--text-on-dark-dim, #D6D3D1); text-transform: uppercase; letter-spacing: 0.08em;">
                    {{ $group['group'] }}
                </div>
            @endif

            @foreach ($group['items'] as $item)
                <a
                    href="{{ $item['url'] }}"
                    style="
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        padding: 8px {{ $collapsed ? '0' : '16px' }};
                        {{ $collapsed ? 'justify-content: center;' : '' }}
                        color: {{ $item['active'] ? 'var(--text-on-dark, #FAFAF9)' : 'var(--text-on-dark-dim, #D6D3D1)' }};
                        text-decoration: none;
                        font-size: 14px;
                        font-weight: {{ $item['active'] ? '600' : '400' }};
                        background: {{ $item['active'] ? 'var(--surface-sidebar-active, #094B26)' : 'transparent' }};
                        border-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: {{ $item['active'] ? '3px solid var(--color-brand-300, #5FC588)' : '3px solid transparent' }};
                        transition: background 0.15s ease;
                    "
                    title="{{ $collapsed ? $item['label'] : '' }}"
                    onmouseover="if(!{{ $item['active'] ? 'true' : 'false' }}) this.style.background='var(--surface-sidebar-hover, #052015)'"
                    onmouseout="if(!{{ $item['active'] ? 'true' : 'false' }}) this.style.background='transparent'"
                >
                    <span style="flex-shrink: 0; display: flex; align-items: center;">
                        {!! $icons[$item['icon']] !!}
                    </span>
                    @if (!$collapsed)
                        <span>{{ $item['label'] }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    {{-- Collapse toggle --}}
    <div style="padding: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
        <button
            wire:click="toggleCollapse"
            style="
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                padding: 8px;
                background: transparent;
                border: none;
                color: var(--text-on-dark-dim, #D6D3D1);
                cursor: pointer;
                border-radius: 6px;
            "
            title="{{ $collapsed ? __('shell.expand_sidebar') : __('shell.collapse_sidebar') }}"
            onmouseover="this.style.background='var(--surface-sidebar-hover, #052015)'"
            onmouseout="this.style.background='transparent'"
        >
            @if ($collapsed)
                {{-- Expand icon (chevron right in LTR, left in RTL) --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="{{ app()->getLocale() === 'ar' ? 'transform: rotate(180deg);' : '' }}"><path d="m9 18 6-6-6-6"/></svg>
            @else
                {{-- Collapse icon (chevron left in LTR, right in RTL) --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="{{ app()->getLocale() === 'ar' ? 'transform: rotate(180deg);' : '' }}"><path d="m15 18-6-6 6-6"/></svg>
                <span style="margin-inline-start: 8px; font-size: 13px;">{{ __('shell.collapse_sidebar') }}</span>
            @endif
        </button>
    </div>
</aside>
