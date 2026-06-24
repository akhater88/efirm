<div style="
    background: var(--surface-card, #FFFFFF);
    border: 1px solid var(--border-default, #E7E5E4);
    border-radius: 8px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    min-height: 320px;
">
    {{-- Header --}}
    <div style="padding: 14px 16px; border-bottom: 1px solid var(--border-default, #E7E5E4);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <h3 style="font-size: 14px; font-weight: 600; color: var(--text-primary, #1C1917); margin: 0;">
                {{ __('dashboard.widget_obligations') }}
            </h3>
            <select
                wire:model.live="daysAhead"
                style="font-size: 12px; padding: 4px 8px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 4px; background: #FFFFFF; color: var(--text-secondary, #44403C);"
            >
                <option value="7">7 {{ __('dashboard.days') }}</option>
                <option value="14">14 {{ __('dashboard.days') }}</option>
                <option value="30">30 {{ __('dashboard.days') }}</option>
                <option value="60">60 {{ __('dashboard.days') }}</option>
            </select>
        </div>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('dashboard.search_obligations') }}"
            style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 13px; color: var(--text-primary, #1C1917); box-sizing: border-box; outline: none;"
            onfocus="this.style.borderColor='var(--border-focus, #0D5C2E)'"
            onblur="this.style.borderColor='var(--border-default, #E7E5E4)'"
        >
    </div>

    {{-- Body --}}
    <div style="flex: 1; overflow-y: auto;">
        @forelse ($obligations as $obligation)
            <div style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-bottom: 1px solid var(--border-default, #E7E5E4);">
                @php $daysLeft = (int) now()->diffInDays($obligation->due_date, false); @endphp
                <div style="
                    flex-shrink: 0; width: 40px; text-align: center;
                    background: {{ $daysLeft <= 3 ? 'var(--color-danger-50, #FEF2F2)' : 'var(--color-brand-50, #ECFAF1)' }};
                    border-radius: 6px; padding: 4px 0;
                ">
                    <div style="font-size: 16px; font-weight: 700; color: {{ $daysLeft <= 3 ? 'var(--color-danger-700, #B91C1C)' : 'var(--color-brand-700, #072E17)' }}; line-height: 1;">
                        {{ $obligation->due_date->format('d') }}
                    </div>
                    <div style="font-size: 10px; font-weight: 500; color: {{ $daysLeft <= 3 ? 'var(--color-danger-500, #DC2626)' : 'var(--color-brand-500, #0D5C2E)' }}; text-transform: uppercase;">
                        {{ $obligation->due_date->translatedFormat('M') }}
                    </div>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 13px; font-weight: 500; color: var(--text-primary, #1C1917); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $obligation->title }}
                    </div>
                    @if ($obligation->document)
                        <div style="font-size: 12px; color: var(--text-tertiary, #78716C); margin-top: 1px;">
                            {{ $obligation->document->title }}
                        </div>
                    @endif
                </div>
                <span style="flex-shrink: 0; font-size: 11px; font-weight: 500; color: {{ $daysLeft <= 3 ? 'var(--color-danger-500, #DC2626)' : 'var(--text-tertiary, #78716C)' }};">
                    {{ trans_choice('dashboard.days_left', $daysLeft, ['count' => $daysLeft]) }}
                </span>
            </div>
        @empty
            <div style="display: flex; align-items: center; justify-content: center; padding: 48px 16px; color: var(--text-tertiary, #78716C); font-size: 13px;">
                {{ __('dashboard.no_upcoming_obligations') }}
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($obligations->hasMorePages())
        <div style="padding: 10px 16px; border-top: 1px solid var(--border-default, #E7E5E4); text-align: center;">
            <button wire:click="nextPage" style="font-size: 13px; font-weight: 500; color: var(--text-link, #0D5C2E); background: none; border: none; cursor: pointer;">
                {{ __('common.view_all') }}
            </button>
        </div>
    @endif
</div>
