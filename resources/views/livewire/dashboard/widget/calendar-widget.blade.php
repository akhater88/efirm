<x-dashboard.widget-card
    :title="__('dashboard.widget_calendar')"
    :state="$events->isEmpty() ? 'empty' : 'default'"
    :empty-message="__('dashboard.no_upcoming_events')"
>
    <div style="padding: 0;">
        @foreach ($events as $event)
            <div style="
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 16px;
                border-bottom: 1px solid var(--border-default, #E7E5E4);
            ">
                {{-- Date chip --}}
                <div style="
                    flex-shrink: 0;
                    width: 40px;
                    text-align: center;
                    background: var(--color-brand-50, #FDF2F2);
                    border-radius: 6px;
                    padding: 4px 0;
                ">
                    <div style="font-size: 16px; font-weight: 700; color: var(--color-brand-700, #330000); line-height: 1;">
                        {{ $event->due_date->format('d') }}
                    </div>
                    <div style="font-size: 10px; font-weight: 500; color: var(--color-brand-500, #520000); text-transform: uppercase;">
                        {{ $event->due_date->translatedFormat('M') }}
                    </div>
                </div>

                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 13px; font-weight: 500; color: var(--text-primary, #2D0A0A); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $event->title }}
                    </div>
                    @if ($event->document)
                        <div style="font-size: 12px; color: var(--text-tertiary, #7A5050); margin-top: 1px;">
                            {{ $event->document->title }}
                        </div>
                    @endif
                </div>

                {{-- Days remaining --}}
                @php $daysLeft = (int) now()->diffInDays($event->due_date, false); @endphp
                <span style="
                    flex-shrink: 0;
                    font-size: 11px;
                    font-weight: 500;
                    color: {{ $daysLeft <= 3 ? 'var(--color-danger-500, #DC2626)' : 'var(--text-tertiary, #7A5050)' }};
                ">
                    {{ trans_choice('dashboard.days_left', $daysLeft, ['count' => $daysLeft]) }}
                </span>
            </div>
        @endforeach
    </div>
</x-dashboard.widget-card>
