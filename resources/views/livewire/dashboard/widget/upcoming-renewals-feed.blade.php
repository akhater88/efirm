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
            <h3 style="font-size: 14px; font-weight: 600; color: var(--text-primary, #2D0A0A); margin: 0;">
                {{ __('dashboard.widget_renewals') }}
            </h3>
            <select
                wire:model.live="daysAhead"
                style="font-size: 12px; padding: 4px 8px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 4px; background: #FFFFFF; color: var(--text-secondary, #4A2020);"
            >
                <option value="30">30 {{ __('dashboard.days') }}</option>
                <option value="60">60 {{ __('dashboard.days') }}</option>
                <option value="90">90 {{ __('dashboard.days') }}</option>
                <option value="180">180 {{ __('dashboard.days') }}</option>
            </select>
        </div>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('dashboard.search_renewals') }}"
            style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 13px; color: var(--text-primary, #2D0A0A); box-sizing: border-box; outline: none;"
            onfocus="this.style.borderColor='var(--border-focus, #520000)'"
            onblur="this.style.borderColor='var(--border-default, #E7E5E4)'"
        >
    </div>

    {{-- Body --}}
    <div style="flex: 1; overflow-y: auto;">
        @forelse ($renewals as $renewal)
            <div style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-bottom: 1px solid var(--border-default, #E7E5E4);">
                @php $daysLeft = (int) now()->diffInDays($renewal->expiry_date, false); @endphp
                <div style="
                    flex-shrink: 0; width: 40px; text-align: center;
                    background: {{ $daysLeft <= 14 ? 'var(--color-warning-50, #FFFBEB)' : 'var(--color-brand-50, #FDF2F2)' }};
                    border-radius: 6px; padding: 4px 0;
                ">
                    <div style="font-size: 16px; font-weight: 700; color: {{ $daysLeft <= 14 ? 'var(--color-warning-700, #B45309)' : 'var(--color-brand-700, #330000)' }}; line-height: 1;">
                        {{ $renewal->expiry_date->format('d') }}
                    </div>
                    <div style="font-size: 10px; font-weight: 500; color: {{ $daysLeft <= 14 ? 'var(--color-warning-500, #F59E0B)' : 'var(--color-brand-500, #520000)' }}; text-transform: uppercase;">
                        {{ $renewal->expiry_date->translatedFormat('M') }}
                    </div>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 13px; font-weight: 500; color: var(--text-primary, #2D0A0A); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $renewal->document?->title ?? '—' }}
                    </div>
                    @if ($renewal->document?->matter)
                        <div style="font-size: 12px; color: var(--text-tertiary, #7A5050); margin-top: 1px;">
                            {{ $renewal->document->matter->title }}
                        </div>
                    @endif
                </div>
                <span style="flex-shrink: 0; font-size: 11px; font-weight: 500; color: {{ $daysLeft <= 14 ? 'var(--color-warning-700, #B45309)' : 'var(--text-tertiary, #7A5050)' }};">
                    {{ trans_choice('dashboard.days_left', $daysLeft, ['count' => $daysLeft]) }}
                </span>
            </div>
        @empty
            <div style="display: flex; align-items: center; justify-content: center; padding: 48px 16px; color: var(--text-tertiary, #7A5050); font-size: 13px;">
                {{ __('dashboard.no_upcoming_renewals') }}
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($renewals->hasMorePages())
        <div style="padding: 10px 16px; border-top: 1px solid var(--border-default, #E7E5E4); text-align: center;">
            <button wire:click="nextPage" style="font-size: 13px; font-weight: 500; color: var(--text-link, #520000); background: none; border: none; cursor: pointer;">
                {{ __('common.view_all') }}
            </button>
        </div>
    @endif
</div>
