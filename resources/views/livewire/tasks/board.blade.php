@php
    $isRtl = app()->getLocale() === 'ar';

    // Map color names to actual Tailwind classes (dynamic classes get purged)
    $colorMap = [
        'gray' => 'bg-gray-500',
        'blue' => 'bg-blue-500',
        'green' => 'bg-green-500',
        'red' => 'bg-red-500',
        'yellow' => 'bg-yellow-500',
        'indigo' => 'bg-indigo-500',
        'purple' => 'bg-purple-500',
        'pink' => 'bg-pink-500',
        'orange' => 'bg-orange-500',
        'info' => 'bg-blue-500',
        'success' => 'bg-green-500',
        'warning' => 'bg-yellow-500',
        'danger' => 'bg-red-500',
    ];

    $priorityColorMap = [
        'gray' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700'],
        'info' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
        'warning' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
        'danger' => ['bg' => 'bg-red-100', 'text' => 'text-red-700'],
    ];
@endphp

<div>
    {{-- Toolbar --}}
    <div class="flex items-center gap-3 mb-4 flex-wrap">
        <select wire:model.live="workflowId" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
            <option value="">{{ __('task_workflows.select_workflow') }}</option>
            @foreach ($workflows as $wf)
                <option value="{{ $wf->id }}">{{ $wf->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="priorityFilter" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
            <option value="">{{ __('task_workflows.all_priorities') }}</option>
            @foreach (\App\Enums\TaskPriority::cases() as $p)
                <option value="{{ $p->value }}">{{ $p->label() }}</option>
            @endforeach
        </select>
    </div>

    {{-- Board --}}
    @if (count($columns) > 0)
        <div style="display: flex; gap: 1rem; overflow-x: auto; min-height: 60vh; padding-bottom: 1rem; {{ $isRtl ? 'flex-direction: row-reverse;' : '' }}">
            @foreach ($columns as $column)
                <div style="flex-shrink: 0; width: 288px; display: flex; flex-direction: column;"
                     class="bg-gray-50 rounded-lg border border-gray-200"
                     id="column-{{ $column['id'] }}"
                     data-stage-id="{{ $column['id'] }}">
                    {{-- Column header --}}
                    <div class="px-3 py-2.5 border-b border-gray-200 flex items-center justify-between bg-white rounded-t-lg">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $colorMap[$column['color']] ?? 'bg-gray-500' }}"></span>
                            <span class="text-sm font-semibold text-gray-900">{{ $column['name'] }}</span>
                        </div>
                        <span class="text-xs text-gray-500 bg-gray-100 rounded-full px-2 py-0.5 font-medium">{{ $column['task_count'] }}</span>
                    </div>

                    {{-- Cards --}}
                    <div class="flex-1 p-2 space-y-2 overflow-y-auto task-column min-h-[100px]"
                         data-stage-id="{{ $column['id'] }}">
                        @forelse ($column['tasks'] as $task)
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm cursor-grab active:cursor-grabbing task-card hover:shadow-md transition-shadow"
                                 data-task-id="{{ $task['id'] }}"
                                 draggable="true">
                                <div class="text-sm font-medium text-gray-900 mb-1">{{ $task['title'] }}</div>

                                @if ($task['taskable_label'])
                                    <div class="text-xs text-gray-500 mb-2 truncate">{{ $task['taskable_label'] }}</div>
                                @endif

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        @if ($task['assignee_name'])
                                            <span class="text-xs text-gray-600">{{ $task['assignee_name'] }}</span>
                                        @endif
                                        @if ($task['due_date'])
                                            <span class="text-xs text-gray-400">{{ $task['due_date'] }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @if ($task['priority'] && $task['priority'] !== 'normal')
                                            @php $pc = $priorityColorMap[$task['priority_color']] ?? $priorityColorMap['gray']; @endphp
                                            <span class="text-xs px-1.5 py-0.5 rounded {{ $pc['bg'] }} {{ $pc['text'] }}">
                                                {{ $task['priority'] }}
                                            </span>
                                        @endif
                                        @if ($task['has_pending_approval'])
                                            <span class="text-xs px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700">
                                                {{ __('task_workflows.pending_approval') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-xs text-gray-400">
                                {{ __('task_workflows.no_tasks_in_stage') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-16 text-gray-400">
            {{ __('task_workflows.select_workflow_to_view_board') }}
        </div>
    @endif

    {{-- Drag-drop JS — SortableJS loaded from CDN --}}
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
