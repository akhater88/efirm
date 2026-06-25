<div>
    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #1C1917); margin: 0;">
            {{ __('shell.obligations_list_title') }}
        </h1>
    </div>

    {{-- Filters --}}
    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('shell.obligations_search_placeholder') }}"
                style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #D6D3D1); border-radius: 8px; font-size: 14px; background: var(--surface-card, #FFFFFF); color: var(--text-primary, #1C1917); outline: none; box-sizing: border-box;"
            />
        </div>
        <div>
            <select
                wire:model.live="statusFilter"
                style="padding: 8px 12px; border: 1px solid var(--border-default, #D6D3D1); border-radius: 8px; font-size: 14px; background: var(--surface-card, #FFFFFF); color: var(--text-primary, #1C1917); outline: none; cursor: pointer;"
            >
                <option value="">{{ __('shell.filter_all_statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    @if ($obligations->count() > 0)
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: var(--surface-subtle, #F5F5F4); border-bottom: 1px solid var(--border-default, #D6D3D1);">
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_title') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_due_date') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_status') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_document') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($obligations as $obligation)
                        @php
                            $badgeColors = match($obligation->status) {
                                \App\Enums\ObligationStatus::Pending => ['bg' => '#FFFBEB', 'text' => '#B45309'],
                                \App\Enums\ObligationStatus::InProgress => ['bg' => '#EFF6FF', 'text' => '#1D4ED8'],
                                \App\Enums\ObligationStatus::Completed => ['bg' => '#F0FDF4', 'text' => '#166534'],
                                \App\Enums\ObligationStatus::Overdue => ['bg' => '#FEF2F2', 'text' => '#B91C1C'],
                                default => ['bg' => '#F5F5F4', 'text' => '#57534E'],
                            };
                        @endphp
                        <tr
                            onclick="window.location='/app/obligations/{{ $obligation->id }}/edit'"
                            style="border-bottom: 1px solid var(--border-default, #D6D3D1); cursor: pointer; transition: background 0.15s;"
                            onmouseover="this.style.background='var(--surface-subtle, #F5F5F4)'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding: 12px 16px; color: var(--text-primary, #1C1917); font-weight: 500;">
                                {{ $obligation->title }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $obligation->due_date?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <span style="display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 500; background: {{ $badgeColors['bg'] }}; color: {{ $badgeColors['text'] }};">
                                    {{ $obligation->status?->label() ?? '—' }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $obligation->document?->title ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $obligation->obligation_type?->label() ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 16px;">
            {{ $obligations->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div style="text-align: center; padding: 64px 24px; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted, #A8A29E)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 16px;">
                <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary, #1C1917); margin: 0 0 8px;">
                {{ __('shell.obligations_empty_title') }}
            </h3>
            <p style="font-size: 14px; color: var(--text-secondary, #57534E); margin: 0;">
                {{ __('shell.obligations_empty_description') }}
            </p>
        </div>
    @endif
</div>
