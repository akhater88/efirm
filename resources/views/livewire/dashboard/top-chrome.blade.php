<header style="background: #FFFFFF; border-bottom: 1px solid var(--border-default, #E7E5E4); height: 56px; display: flex; align-items: center; padding: 0 16px; gap: 12px; position: sticky; top: 0; z-index: 40;">
    {{-- Firm name --}}
    <div style="font-weight: 600; font-size: 16px; color: var(--text-primary, #1C1917); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; max-width: 200px;">
        {{ $workspace?->name ?? __('shell.no_workspace') }}
    </div>

    {{-- Spacer --}}
    <div style="flex: 1;"></div>

    {{-- Global search trigger --}}
    <button
        wire:click="$set('showSearchModal', true)"
        style="display: flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; background: var(--surface-page, #FAFAF9); color: var(--text-tertiary, #78716C); font-size: 13px; cursor: pointer; min-width: 200px;"
        title="{{ __('shell.search') }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <span>{{ __('shell.search_placeholder') }}</span>
        <kbd style="margin-inline-start: auto; font-size: 11px; padding: 1px 5px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 3px; background: #FFFFFF; color: var(--text-tertiary, #78716C);">⌘K</kbd>
    </button>

    {{-- Spacer --}}
    <div style="flex: 1;"></div>

    {{-- Quick Add --}}
    <div x-data="{ open: false }" style="position: relative;">
        <button
            @click="open = !open"
            style="display: flex; align-items: center; gap: 4px; padding: 6px 12px; background: var(--color-brand-500, #520000); color: #FFFFFF; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            {{ __('shell.quick_add') }}
        </button>

        <div
            x-show="open"
            @click.outside="open = false"
            x-transition
            style="position: absolute; top: 100%; margin-top: 4px; background: #FFFFFF; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; box-shadow: var(--shadow-lg); min-width: 180px; padding: 4px; z-index: 50; {{ app()->getLocale() === 'ar' ? 'left: 0;' : 'right: 0;' }}"
        >
            <a href="/app/matters/create" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 13px; color: var(--text-primary, #1C1917); text-decoration: none; border-radius: 4px;" onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'" onmouseout="this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                {{ __('shell.new_matter') }}
            </a>
            <a href="/app/contacts/create" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 13px; color: var(--text-primary, #1C1917); text-decoration: none; border-radius: 4px;" onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'" onmouseout="this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                {{ __('shell.new_contact') }}
            </a>
            <a href="/app/tasks/create" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 13px; color: var(--text-primary, #1C1917); text-decoration: none; border-radius: 4px;" onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'" onmouseout="this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                {{ __('shell.new_task') }}
            </a>
        </div>
    </div>

    {{-- Timer --}}
    <div x-data="{ open: false }" style="position: relative;">
        @if ($activeTimerId)
            {{-- Active timer indicator --}}
            <button
                wire:click="stopTimer"
                style="display: flex; align-items: center; gap: 6px; padding: 6px 10px; background: var(--color-success-50, #F0FDF4); border: 1px solid var(--color-success-500, #15803D); border-radius: 6px; font-size: 13px; color: var(--color-success-700, #166534); cursor: pointer; font-weight: 500;"
                title="{{ __('shell.stop_timer') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="1"/></svg>
                <span wire:poll.10s="refreshTimer">{{ $activeTimerElapsed }}</span>
            </button>
        @else
            {{-- Start timer split button --}}
            <button
                @click="open = !open"
                style="display: flex; align-items: center; gap: 4px; padding: 6px 10px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; background: #FFFFFF; font-size: 13px; color: var(--text-secondary, #44403C); cursor: pointer;"
                title="{{ __('shell.start_timer') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ __('shell.start_timer') }}
            </button>

            {{-- Timer matter dropdown --}}
            <div
                x-show="open"
                @click.outside="open = false"
                x-transition
                style="position: absolute; top: 100%; margin-top: 4px; background: #FFFFFF; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; box-shadow: var(--shadow-lg); min-width: 220px; padding: 4px; z-index: 50; {{ app()->getLocale() === 'ar' ? 'left: 0;' : 'right: 0;' }}"
            >
                <div style="padding: 8px 12px; font-size: 11px; font-weight: 600; color: var(--text-tertiary, #78716C); text-transform: uppercase; letter-spacing: 0.08em;">
                    {{ __('shell.select_matter') }}
                </div>
                @forelse ($recentMatters as $matter)
                    <button
                        wire:click="startTimerForMatter('{{ $matter->id }}')"
                        @click="open = false"
                        style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 13px; color: var(--text-primary, #1C1917); background: transparent; border: none; width: 100%; text-align: start; cursor: pointer; border-radius: 4px;"
                        onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'"
                        onmouseout="this.style.background='transparent'"
                    >
                        {{ $matter->title }}
                    </button>
                @empty
                    <div style="padding: 8px 12px; font-size: 13px; color: var(--text-tertiary, #78716C);">
                        {{ __('shell.no_recent_matters') }}
                    </div>
                @endforelse
            </div>
        @endif
    </div>

    {{-- Chat placeholder --}}
    <button
        wire:click="openChat"
        style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; background: #FFFFFF; color: var(--text-tertiary, #78716C); cursor: pointer;"
        title="{{ __('shell.chat') }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
    </button>

    {{-- Notifications --}}
    <div x-data="{ open: false }" style="position: relative;">
        <button
            @click="open = !open"
            style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; background: #FFFFFF; color: var(--text-tertiary, #78716C); cursor: pointer; position: relative;"
            title="{{ __('shell.notifications') }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            @if ($notificationCount > 0)
                <span style="position: absolute; top: -4px; {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: -4px; background: var(--color-danger-500, #DC2626); color: #FFFFFF; font-size: 10px; font-weight: 600; min-width: 16px; height: 16px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; padding: 0 4px;">
                    {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                </span>
            @endif
        </button>

        <div
            x-show="open"
            @click.outside="open = false"
            x-transition
            style="position: absolute; top: 100%; margin-top: 4px; background: #FFFFFF; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; box-shadow: var(--shadow-lg); width: 320px; max-height: 400px; overflow-y: auto; z-index: 50; {{ app()->getLocale() === 'ar' ? 'left: 0;' : 'right: 0;' }}"
        >
            <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-default, #E7E5E4); font-weight: 600; font-size: 14px; color: var(--text-primary, #1C1917);">
                {{ __('shell.notifications') }}
            </div>
            <div style="padding: 24px 16px; text-align: center; color: var(--text-tertiary, #78716C); font-size: 13px;">
                {{ __('shell.no_notifications') }}
            </div>
        </div>
    </div>

    {{-- User menu --}}
    <div x-data="{ open: false }" style="position: relative;">
        <button
            @click="open = !open"
            style="display: flex; align-items: center; gap: 8px; padding: 4px; border: none; background: transparent; cursor: pointer;"
        >
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 32px; height: 32px; border-radius: 9999px; object-fit: cover;">
            @else
                <div style="width: 32px; height: 32px; border-radius: 9999px; background: var(--color-brand-50, #FDF2F2); display: flex; align-items: center; justify-content: center;">
                    <span style="color: var(--color-brand-700, #330000); font-weight: 600; font-size: 13px;">{{ mb_substr($user->name, 0, 1) }}</span>
                </div>
            @endif
            <span style="font-size: 13px; color: var(--text-primary, #1C1917); font-weight: 500; display: none;" class="sm:inline">{{ $user->name }}</span>
        </button>

        <div
            x-show="open"
            @click.outside="open = false"
            x-transition
            style="position: absolute; top: 100%; margin-top: 4px; background: #FFFFFF; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; box-shadow: var(--shadow-lg); min-width: 200px; padding: 4px; z-index: 50; {{ app()->getLocale() === 'ar' ? 'left: 0;' : 'right: 0;' }}"
        >
            {{-- User info --}}
            <div style="padding: 8px 12px; border-bottom: 1px solid var(--border-default, #E7E5E4); margin-bottom: 4px;">
                <div style="font-size: 13px; font-weight: 600; color: var(--text-primary, #1C1917);">{{ $user->name }}</div>
                <div style="font-size: 12px; color: var(--text-tertiary, #78716C);">{{ $user->email }}</div>
            </div>

            <a href="{{ route('profile') }}" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 13px; color: var(--text-primary, #1C1917); text-decoration: none; border-radius: 4px;" onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'" onmouseout="this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {{ __('shell.profile') }}
            </a>

            @if ($user->canAccessPanel(\Filament\Facades\Filament::getPanel('app')))
                <a href="/app" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 13px; color: var(--text-primary, #1C1917); text-decoration: none; border-radius: 4px;" onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'" onmouseout="this.style.background='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    {{ __('shell.workspace_panel') }}
                </a>
            @endif

            {{-- Locale switcher --}}
            <a href="{{ url('/locale/' . (app()->getLocale() === 'ar' ? 'en' : 'ar')) }}" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 13px; color: var(--text-primary, #1C1917); text-decoration: none; border-radius: 4px;" onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'" onmouseout="this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
            </a>

            <div style="border-top: 1px solid var(--border-default, #E7E5E4); margin-top: 4px; padding-top: 4px;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 13px; color: var(--color-danger-500, #DC2626); background: transparent; border: none; width: 100%; text-align: start; cursor: pointer; border-radius: 4px;" onmouseover="this.style.background='var(--color-danger-50, #FEF2F2)'" onmouseout="this.style.background='transparent'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        {{ __('shell.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- Search modal --}}
@if ($showSearchModal)
    <div
        x-data
        x-init="$refs.searchInput.focus()"
        @keydown.escape.window="$wire.set('showSearchModal', false)"
        style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: flex-start; justify-content: center; padding-top: 120px;"
    >
        {{-- Backdrop --}}
        <div
            wire:click="$set('showSearchModal', false)"
            style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.3);"
        ></div>

        {{-- Modal --}}
        <div style="position: relative; background: #FFFFFF; border-radius: 12px; box-shadow: var(--shadow-xl); width: 100%; max-width: 560px; overflow: hidden;">
            <div style="display: flex; align-items: center; gap: 12px; padding: 16px; border-bottom: 1px solid var(--border-default, #E7E5E4);">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-tertiary, #78716C)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input
                    x-ref="searchInput"
                    type="text"
                    placeholder="{{ __('shell.search_full_placeholder') }}"
                    style="flex: 1; border: none; outline: none; font-size: 16px; color: var(--text-primary, #1C1917); background: transparent;"
                >
            </div>
            <div style="padding: 32px 16px; text-align: center; color: var(--text-tertiary, #78716C); font-size: 14px;">
                {{ __('shell.search_coming_soon') }}
            </div>
        </div>
    </div>
@endif
