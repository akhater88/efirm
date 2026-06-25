<div>
    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #1C1917); margin: 0;">
            {{ __('shell.library_clauses_list_title') }}
        </h1>
    </div>

    {{-- Filters --}}
    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('shell.library_clauses_search_placeholder') }}"
                style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #D6D3D1); border-radius: 8px; font-size: 14px; background: var(--surface-card, #FFFFFF); color: var(--text-primary, #1C1917); outline: none; box-sizing: border-box;"
            />
        </div>
    </div>

    {{-- Table --}}
    @if ($clauses->count() > 0)
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: var(--surface-subtle, #F5F5F4); border-bottom: 1px solid var(--border-default, #D6D3D1);">
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_title') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_category') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_language') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_risk_position') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_updated') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clauses as $clause)
                        @php
                            $riskColors = match($clause->risk_position) {
                                \App\Enums\RiskPosition::Favourable => ['bg' => '#F0FDF4', 'text' => '#166534'],
                                \App\Enums\RiskPosition::Balanced => ['bg' => '#EFF6FF', 'text' => '#1D4ED8'],
                                \App\Enums\RiskPosition::Adverse => ['bg' => '#FEF2F2', 'text' => '#B91C1C'],
                                default => ['bg' => '#F5F5F4', 'text' => '#57534E'],
                            };
                        @endphp
                        <tr
                            onclick="window.location='/app/library/clauses/{{ $clause->id }}/edit'"
                            style="border-bottom: 1px solid var(--border-default, #D6D3D1); cursor: pointer; transition: background 0.15s;"
                            onmouseover="this.style.background='var(--surface-subtle, #F5F5F4)'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding: 12px 16px; color: var(--text-primary, #1C1917); font-weight: 500;">
                                {{ $clause->title }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $clause->clause_type ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $clause->language?->label() ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px;">
                                @if ($clause->risk_position)
                                    <span style="display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 500; background: {{ $riskColors['bg'] }}; color: {{ $riskColors['text'] }};">
                                        {{ $clause->risk_position->label() }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-muted, #A8A29E); font-size: 13px;">
                                {{ $clause->updated_at?->diffForHumans() ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 16px;">
            {{ $clauses->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div style="text-align: center; padding: 64px 24px; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted, #A8A29E)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 16px;">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary, #1C1917); margin: 0 0 8px;">
                {{ __('shell.library_clauses_empty_title') }}
            </h3>
            <p style="font-size: 14px; color: var(--text-secondary, #57534E); margin: 0;">
                {{ __('shell.library_clauses_empty_description') }}
            </p>
        </div>
    @endif
</div>
