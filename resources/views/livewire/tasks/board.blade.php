@php $isRtl = app()->getLocale() === 'ar'; @endphp

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
        <div class="flex gap-4 overflow-x-auto pb-4 {{ $isRtl ? 'flex-row-reverse' : '' }}" style="min-height: 60vh;">
            @foreach ($columns as $column)
                <div class="flex-shrink-0 w-72 bg-gray-50 rounded-lg flex flex-col"
                     id="column-{{ $column['id'] }}"
                     data-stage-id="{{ $column['id'] }}">
                    {{-- Column header --}}
                    <div class="px-3 py-2 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-{{ $column['color'] }}-500"></span>
                            <span class="text-sm font-semibold text-gray-900">{{ $column['name'] }}</span>
                        </div>
                        <span class="text-xs text-gray-400 bg-gray-200 rounded-full px-2 py-0.5">{{ $column['task_count'] }}</span>
                    </div>

                    {{-- Cards --}}
                    <div class="flex-1 p-2 space-y-2 overflow-y-auto task-column"
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
                                            <span class="text-xs px-1.5 py-0.5 rounded bg-{{ $task['priority_color'] }}-100 text-{{ $task['priority_color'] }}-700">
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

    {{-- Drag-drop JS — SortableJS loaded from CDN (cannot use ES import in @script) --}}
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
