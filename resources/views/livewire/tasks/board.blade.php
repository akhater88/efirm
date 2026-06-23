@php
    $isRtl = app()->getLocale() === 'ar';

    $dotColors = [
        'gray' => '#9CA3AF', 'blue' => '#3B82F6', 'green' => '#10B981',
        'red' => '#EF4444', 'yellow' => '#F59E0B', 'indigo' => '#6366F1',
        'purple' => '#8B5CF6', 'info' => '#3B82F6', 'success' => '#10B981',
        'warning' => '#F59E0B', 'danger' => '#EF4444',
    ];

    $priorityBadge = [
        'low'    => ['bg' => '#F3F4F6', 'color' => '#4B5563'],
        'high'   => ['bg' => '#FEF3C7', 'color' => '#92400E'],
        'urgent' => ['bg' => '#FEE2E2', 'color' => '#991B1B'],
    ];
@endphp

<div>
    {{-- Toolbar --}}
    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; align-items: center;">
        <select wire:model.live="workflowId"
                style="font-size: 14px; border: 1px solid #D1D5DB; border-radius: 8px; padding: 6px 12px; background: #fff;">
            <option value="">{{ __('task_workflows.select_workflow') }}</option>
            @foreach ($workflows as $wf)
                <option value="{{ $wf->id }}">{{ $wf->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="priorityFilter"
                style="font-size: 14px; border: 1px solid #D1D5DB; border-radius: 8px; padding: 6px 12px; background: #fff;">
            <option value="">{{ __('task_workflows.all_priorities') }}</option>
            @foreach (\App\Enums\TaskPriority::cases() as $p)
                <option value="{{ $p->value }}">{{ $p->label() }}</option>
            @endforeach
        </select>
    </div>

    {{-- Board --}}
    @if (count($columns) > 0)
        <div style="display: flex; gap: 16px; overflow-x: auto; min-height: 65vh; padding-bottom: 16px; {{ $isRtl ? 'flex-direction: row-reverse;' : '' }}">
            @foreach ($columns as $column)
                @php $dotColor = $dotColors[$column['color']] ?? '#9CA3AF'; @endphp
                <div style="flex-shrink: 0; width: 300px; display: flex; flex-direction: column; background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden;"
                     id="column-{{ $column['id'] }}"
                     data-stage-id="{{ $column['id'] }}">

                    {{-- Column header --}}
                    <div style="padding: 12px 16px; border-bottom: 1px solid #E5E7EB; display: flex; align-items: center; justify-content: space-between; background: #fff;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $dotColor }}; display: inline-block;"></span>
                            <span style="font-size: 14px; font-weight: 600; color: #111827;">{{ $column['name'] }}</span>
                        </div>
                        <span style="font-size: 12px; color: #6B7280; background: #F3F4F6; border-radius: 12px; padding: 2px 10px; font-weight: 500;">{{ $column['task_count'] }}</span>
                    </div>

                    {{-- Cards area --}}
                    <div class="task-column"
                         data-stage-id="{{ $column['id'] }}"
                         style="flex: 1; padding: 8px; overflow-y: auto; min-height: 120px; display: flex; flex-direction: column; gap: 8px;">

                        @forelse ($column['tasks'] as $task)
                            <div class="task-card"
                                 data-task-id="{{ $task['id'] }}"
                                 draggable="true"
                                 style="background: #fff; border: 1px solid #E5E7EB; border-radius: 8px; padding: 12px; cursor: grab; box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: box-shadow 0.15s;"
                                 onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'"
                                 onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.06)'">

                                {{-- Title --}}
                                <div style="font-size: 14px; font-weight: 500; color: #111827; margin-bottom: 4px; line-height: 1.4;">{{ $task['title'] }}</div>

                                {{-- Parent entity --}}
                                @if ($task['taskable_label'])
                                    <div style="font-size: 12px; color: #9CA3AF; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $task['taskable_label'] }}</div>
                                @endif

                                {{-- Footer: assignee, due date, priority --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        @if ($task['assignee_name'])
                                            <span style="font-size: 11px; color: #6B7280;">{{ $task['assignee_name'] }}</span>
                                        @endif
                                        @if ($task['due_date'])
                                            <span style="font-size: 11px; color: #9CA3AF;">{{ $task['due_date'] }}</span>
                                        @endif
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        @if ($task['priority'] && $task['priority'] !== 'normal')
                                            @php $pb = $priorityBadge[$task['priority']] ?? $priorityBadge['low']; @endphp
                                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 4px; background: {{ $pb['bg'] }}; color: {{ $pb['color'] }}; font-weight: 500;">
                                                {{ $task['priority'] }}
                                            </span>
                                        @endif
                                        @if ($task['has_pending_approval'])
                                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 4px; background: #FEF3C7; color: #92400E; font-weight: 500;">
                                                {{ __('task_workflows.pending_approval') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 32px 16px; font-size: 13px; color: #9CA3AF;">
                                {{ __('task_workflows.no_tasks_in_stage') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 64px 16px; color: #9CA3AF; font-size: 15px;">
            {{ __('task_workflows.select_workflow_to_view_board') }}
        </div>
    @endif

    {{-- Drag-drop JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    @script
    <script>
        document.querySelectorAll('.task-column').forEach(column => {
            Sortable.create(column, {
                group: 'tasks',
                animation: 150,
                ghostClass: 'opacity-30',
                onEnd: function(evt) {
                    const taskId = evt.item.dataset.taskId;
                    const toStageId = evt.to.dataset.stageId;
                    const fromStageId = evt.from.dataset.stageId;

                    if (toStageId !== fromStageId) {
                        $wire.moveTask(taskId, toStageId);
                    }
                }
            });
        });
    </script>
    @endscript
</div>
