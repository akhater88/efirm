@php
    $priorityColors = [
        'urgent' => '#DC2626',
        'high' => '#F59E0B',
        'medium' => '#2563EB',
        'low' => '#78716C',
    ];
@endphp

<x-dashboard.widget-card
    :title="__('dashboard.widget_tasks')"
    :state="$tasks->isEmpty() ? 'empty' : 'default'"
    :empty-message="__('dashboard.no_recent_tasks')"
    view-all-url="/app/tasks"
    create-url="/app/tasks/create"
    :create-label="__('shell.new_task')"
>
    <div style="padding: 0;">
        @foreach ($tasks as $task)
            <div style="
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 16px;
                border-bottom: 1px solid var(--border-default, #E7E5E4);
            ">
                {{-- Priority dot --}}
                <span style="
                    flex-shrink: 0;
                    width: 8px;
                    height: 8px;
                    border-radius: 9999px;
                    background: {{ $priorityColors[$task->priority->value ?? $task->priority] ?? '#78716C' }};
                "></span>

                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 13px; font-weight: 500; color: var(--text-primary, #1C1917); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $task->title }}
                    </div>
                </div>

                <span style="flex-shrink: 0; font-size: 11px; color: var(--text-tertiary, #78716C);">
                    {{ $task->updated_at->diffForHumans() }}
                </span>
            </div>
        @endforeach
    </div>
</x-dashboard.widget-card>
