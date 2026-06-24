@php
    $statusColors = [
        'active' => ['bg' => '#F0FDF4', 'text' => '#166534'],
        'on_hold' => ['bg' => '#FFFBEB', 'text' => '#B45309'],
        'closed' => ['bg' => '#F5F5F4', 'text' => '#57534E'],
        'archived' => ['bg' => '#F5F5F4', 'text' => '#78716C'],
    ];
@endphp

<x-dashboard.widget-card
    :title="__('dashboard.widget_matters')"
    :state="$matters->isEmpty() ? 'empty' : 'default'"
    :empty-message="__('dashboard.no_recent_matters')"
    view-all-url="/app/matters"
    create-url="/app/matters/create"
    :create-label="__('shell.new_matter')"
>
    <div style="padding: 0;">
        @foreach ($matters as $matter)
            <a
                href="/app/matters/{{ $matter->id }}"
                style="
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 10px 16px;
                    text-decoration: none;
                    border-bottom: 1px solid var(--border-default, #E7E5E4);
                "
                onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'"
                onmouseout="this.style.background='transparent'"
            >
                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 13px; font-weight: 500; color: var(--text-primary, #1C1917); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $matter->title }}
                    </div>
                    @if ($matter->client)
                        <div style="font-size: 12px; color: var(--text-tertiary, #78716C); margin-top: 2px;">
                            {{ $matter->client->display_name }}
                        </div>
                    @endif
                </div>
                @php $colors = $statusColors[$matter->status->value] ?? $statusColors['active']; @endphp
                <span style="
                    flex-shrink: 0;
                    margin-inline-start: 12px;
                    font-size: 11px;
                    font-weight: 600;
                    padding: 2px 8px;
                    border-radius: 9999px;
                    background: {{ $colors['bg'] }};
                    color: {{ $colors['text'] }};
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                ">
                    {{ $matter->status->label() }}
                </span>
            </a>
        @endforeach
    </div>
</x-dashboard.widget-card>
