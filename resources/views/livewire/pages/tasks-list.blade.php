<div>
    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #1C1917); margin: 0;">
            {{ __('shell.tasks_list_title') }}
        </h1>
        <div style="display: flex; align-items: center; gap: 12px;">
            {{-- View Toggle --}}
            <div style="display: flex; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; overflow: hidden;">
                <button wire:click="setViewMode('list')"
                    style="padding: 6px 12px; font-size: 13px; font-weight: 500; border: none; cursor: pointer;
                        {{ $viewMode === 'list' ? 'background: var(--color-brand-500, #520000); color: #FFFFFF;' : 'background: var(--surface-card, #FFFFFF); color: var(--text-secondary, #44403C);' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
                </button>
                <button wire:click="setViewMode('board')"
                    style="padding: 6px 12px; font-size: 13px; font-weight: 500; border: none; border-inline-start: 1px solid var(--border-default, #E7E5E4); cursor: pointer;
                        {{ $viewMode === 'board' ? 'background: var(--color-brand-500, #520000); color: #FFFFFF;' : 'background: var(--surface-card, #FFFFFF); color: var(--text-secondary, #44403C);' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><rect width="7" height="18" x="3" y="3" rx="1"/><rect width="7" height="10" x="14" y="3" rx="1"/><rect width="7" height="5" x="14" y="16" rx="1"/></svg>
                </button>
            </div>

            <button wire:click="openCreate"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--color-brand-500, #520000); color: #fff; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; border: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('shell.tasks_create') }}
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('shell.tasks_search_placeholder') }}"
                style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #D6D3D1); border-radius: 8px; font-size: 14px; background: var(--surface-card, #FFFFFF); color: var(--text-primary, #1C1917); outline: none; box-sizing: border-box;"
            />
        </div>
        <div>
            <select
                wire:model.live="priorityFilter"
                style="padding: 8px 12px; border: 1px solid var(--border-default, #D6D3D1); border-radius: 8px; font-size: 14px; background: var(--surface-card, #FFFFFF); color: var(--text-primary, #1C1917); outline: none; cursor: pointer;"
            >
                <option value="">{{ __('shell.filter_all_priorities') }}</option>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                @endforeach
            </select>
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
        <div>
            <select
                wire:model.live="taskTypeFilter"
                style="padding: 8px 12px; border: 1px solid var(--border-default, #D6D3D1); border-radius: 8px; font-size: 14px; background: var(--surface-card, #FFFFFF); color: var(--text-primary, #1C1917); outline: none; cursor: pointer;"
            >
                <option value="">{{ __('shell.tasks_all_types') }}</option>
                @foreach ($taskTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->localizedName() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($viewMode === 'board')
    {{-- Board View (Kanban) --}}
    <div style="margin-bottom: 12px;">
        <select wire:model.live="boardWorkflowId" style="padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; font-size: 13px; background: var(--surface-card, #FFFFFF); color: var(--text-primary);">
            <option value="">{{ __('shell.tasks_select_workflow') }}</option>
            @foreach ($workflows as $wf)
                <option value="{{ $wf->id }}">{{ $wf->name }}</option>
            @endforeach
        </select>
    </div>

    @if (count($boardColumns) > 0)
        <div style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 16px; min-height: 400px;">
            @foreach ($boardColumns as $column)
                <div style="min-width: 260px; max-width: 300px; flex: 1; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; display: flex; flex-direction: column;">
                    {{-- Column header --}}
                    <div style="padding: 12px 14px; border-bottom: 2px solid {{ $column['color'] ?? '#E7E5E4' }}; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px; font-weight: 600; color: var(--text-primary, #1C1917);">{{ $column['name'] }}</span>
                        <span style="font-size: 11px; font-weight: 600; color: var(--text-tertiary, #78716C); background: var(--surface-page, #FAFAF9); padding: 2px 8px; border-radius: 9999px;">{{ $column['count'] }}</span>
                    </div>

                    {{-- Column tasks (droppable) --}}
                    <div class="task-column" style="padding: 8px; flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; min-height: 60px;"
                         data-stage-id="{{ $column['id'] }}">
                        @foreach ($column['tasks'] as $task)
                            <div data-task-id="{{ $task['id'] }}"
                                 wire:click="openEdit('{{ $task['id'] }}')"
                                 style="padding: 10px 12px; background: var(--surface-page, #FAFAF9); border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; cursor: grab; border-inline-start: 3px solid {{ $task['priority_color'] }};"
                                 onmouseover="this.style.background='#F5F5F4'"
                                 onmouseout="this.style.background='var(--surface-page, #FAFAF9)'">
                                <div style="font-size: 13px; font-weight: 500; color: var(--text-primary, #1C1917); margin-bottom: 6px; line-height: 1.3;">
                                    {{ $task['title'] }}
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        @if ($task['type_name'])
                                            <span style="font-size: 10px; font-weight: 500; padding: 1px 6px; border-radius: 4px; background: {{ $task['type_color'] }}20; color: {{ $task['type_color'] }};">
                                                {{ $task['type_name'] }}
                                            </span>
                                        @endif
                                        @if ($task['due_date'])
                                            <span style="font-size: 11px; color: var(--text-tertiary, #78716C);">
                                                {{ $task['due_date'] }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($task['assignee'])
                                        <span style="font-size: 10px; font-weight: 500; color: var(--text-tertiary, #78716C); background: var(--surface-card, #FFFFFF); padding: 2px 6px; border-radius: 9999px; border: 1px solid var(--border-default, #E7E5E4);">
                                            {{ mb_substr($task['assignee'], 0, 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if (empty($column['tasks']))
                            <div style="padding: 20px; text-align: center; color: var(--text-tertiary, #78716C); font-size: 12px;">
                                {{ __('shell.tasks_column_empty') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 48px; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; color: var(--text-tertiary, #78716C); font-size: 14px;">
            {{ __('shell.tasks_no_workflow') }}
        </div>
    @endif

    {{-- Drag-drop JS for board view (SortableJS loaded in layout head) --}}
    @script
    <script>
        function initSortable() {
            document.querySelectorAll('.task-column').forEach(column => {
                if (column._sortable) return; // already initialized
                column._sortable = Sortable.create(column, {
                    group: 'tasks',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onStart: function() {
                        // Prevent wire:click from firing during drag
                        document.querySelectorAll('[data-task-id]').forEach(el => {
                            el.style.cursor = 'grabbing';
                        });
                    },
                    onEnd: function(evt) {
                        document.querySelectorAll('[data-task-id]').forEach(el => {
                            el.style.cursor = 'grab';
                        });

                        const taskId = evt.item.dataset.taskId;
                        const toStageId = evt.to.dataset.stageId;
                        const fromStageId = evt.from.dataset.stageId;

                        if (toStageId !== fromStageId && taskId) {
                            $wire.moveTask(taskId, toStageId);
                        }
                    }
                });
            });
        }

        // Init on first load and after Livewire updates
        initSortable();
        Livewire.hook('morph.updated', () => {
            setTimeout(initSortable, 100);
        });
    </script>
    @endscript
    <style>
        .sortable-ghost {
            opacity: 0.3;
        }
        .sortable-drag {
            opacity: 0.9;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>

    @else
    {{-- List View (Table) --}}
    @if ($tasks->count() > 0)
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: var(--surface-subtle, #F5F5F4); border-bottom: 1px solid var(--border-default, #D6D3D1);">
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_title') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.tasks_task_type') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_priority') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_status') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_assigned_to') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_due_date') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_updated') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                        @php
                            $priorityDotColor = match($task->priority) {
                                \App\Enums\TaskPriority::Urgent => '#DC2626',
                                \App\Enums\TaskPriority::High => '#F59E0B',
                                \App\Enums\TaskPriority::Normal => '#2563EB',
                                \App\Enums\TaskPriority::Low => '#78716C',
                                default => '#78716C',
                            };
                        @endphp
                        <tr
                            wire:click="openEdit('{{ $task->id }}')"
                            style="border-bottom: 1px solid var(--border-default, #D6D3D1); cursor: pointer; transition: background 0.15s;"
                            onmouseover="this.style.background='var(--surface-subtle, #F5F5F4)'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding: 12px 16px; color: var(--text-primary, #1C1917); font-weight: 500;">
                                {{ $task->title }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                @if ($task->taskType)
                                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 2px; background: {{ $task->taskType->color }};"></span>
                                        {{ $task->taskType->localizedName() }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted, #A8A29E);">{{ __('shell.task_type_none') }}</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px;">
                                <span style="display: inline-flex; align-items: center; gap: 6px; color: var(--text-secondary, #57534E);">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $priorityDotColor }};"></span>
                                    {{ $task->priority?->label() ?? '—' }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $task->status?->label() ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $task->assignedTo?->name ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $task->due_date?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-muted, #A8A29E); font-size: 13px;">
                                {{ $task->updated_at?->diffForHumans() ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 16px;">
            {{ $tasks->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div style="text-align: center; padding: 64px 24px; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted, #A8A29E)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 16px;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
            </svg>
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary, #1C1917); margin: 0 0 8px;">
                {{ __('shell.tasks_empty_title') }}
            </h3>
            <p style="font-size: 14px; color: var(--text-secondary, #57534E); margin: 0 0 24px;">
                {{ __('shell.tasks_empty_description') }}
            </p>
            <button wire:click="openCreate"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--color-brand-500, #520000); color: #fff; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; border: none; cursor: pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('shell.tasks_create') }}
            </button>
        </div>
    @endif
    @endif {{-- end viewMode --}}

    {{-- Modal --}}
    @if ($showModal)
    <div style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px;"
         @keydown.escape.window="$wire.closeModal()">
        <div wire:click="closeModal" style="position: absolute; inset: 0; background: rgba(0,0,0,0.4);"></div>
        <div style="position: relative; background: #FFFFFF; border-radius: 12px; box-shadow: var(--shadow-xl); width: 100%; max-width: 600px; max-height: 80vh; overflow-y: auto; padding: 24px;">
            <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0 0 20px;">
                {{ $isEditing ? __('common.edit') : __('common.create') }} — {{ __('shell.tasks_list_title') }}
            </h2>
            <form wire:submit="save">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_title') }}</label>
                    <input type="text" wire:model="formTitle" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                    @error('formTitle') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_description') }}</label>
                    <textarea wire:model="formDescription" rows="3" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box; resize: vertical;"></textarea>
                    @error('formDescription') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_priority') }}</label>
                    <select wire:model="formPriority" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                        <option value="">{{ __('shell.label_select') }}</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                        @endforeach
                    </select>
                    @error('formPriority') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_status') }}</label>
                    <select wire:model="formStatus" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                        <option value="">{{ __('shell.label_select') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @error('formStatus') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_due_date') }}</label>
                    <input type="date" wire:model="formDueDate" dir="ltr" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                    @error('formDueDate') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.tasks_task_type') }}</label>
                    <select wire:model.live="formTaskTypeId" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                        <option value="">{{ __('shell.task_type_none') }}</option>
                        @foreach ($taskTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->localizedName() }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($selectedTaskType && is_array($selectedTaskType->custom_fields) && count($selectedTaskType->custom_fields) > 0)
                    <div style="margin-bottom: 14px; padding: 12px; background: var(--surface-subtle, #F5F5F4); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-primary, #1C1917); margin-bottom: 8px;">{{ __('shell.tasks_custom_fields') }}</label>
                        @foreach ($selectedTaskType->custom_fields as $field)
                            <div style="margin-bottom: 10px;">
                                <label style="display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 3px;">
                                    {{ app()->getLocale() === 'ar' ? $field['label_ar'] : $field['label_en'] }}
                                    @if ($field['required'] ?? false)
                                        <span style="color: var(--color-danger-500);">*</span>
                                    @endif
                                </label>
                                @if ($field['type'] === 'text')
                                    <input type="text" wire:model="formCustomFieldValues.{{ $field['key'] }}" style="width: 100%; padding: 6px 10px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 13px; box-sizing: border-box;" />
                                @elseif ($field['type'] === 'number')
                                    <input type="number" wire:model="formCustomFieldValues.{{ $field['key'] }}" dir="ltr" style="width: 100%; padding: 6px 10px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 13px; box-sizing: border-box;" />
                                @elseif ($field['type'] === 'date')
                                    <input type="date" wire:model="formCustomFieldValues.{{ $field['key'] }}" dir="ltr" style="width: 100%; padding: 6px 10px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 13px; box-sizing: border-box;" />
                                @elseif ($field['type'] === 'textarea')
                                    <textarea wire:model="formCustomFieldValues.{{ $field['key'] }}" rows="2" style="width: 100%; padding: 6px 10px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 13px; box-sizing: border-box; resize: vertical;"></textarea>
                                @elseif ($field['type'] === 'select' && is_array($field['options'] ?? null))
                                    <select wire:model="formCustomFieldValues.{{ $field['key'] }}" style="width: 100%; padding: 6px 10px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                                        <option value="">{{ __('shell.label_select') }}</option>
                                        @foreach ($field['options'] as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($field['type'] === 'checkbox')
                                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                        <input type="checkbox" wire:model="formCustomFieldValues.{{ $field['key'] }}" style="accent-color: var(--color-brand-500, #520000);" />
                                    </label>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_assigned_to') }}</label>
                    <select wire:model="formAssignedToUserId" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                        <option value="">{{ __('shell.label_select') }}</option>
                        @foreach ($workspaceMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @error('formAssignedToUserId') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-default, #E7E5E4);">
                    @if ($isEditing)
                        <button type="button" wire:click="delete" wire:confirm="{{ __('common.confirm_delete') }}"
                            style="margin-inline-end: auto; padding: 8px 16px; background: var(--color-danger-50); color: var(--color-danger-700); border: 1px solid var(--color-danger-500); border-radius: 8px; font-size: 13px; cursor: pointer;">
                            {{ __('common.delete') }}
                        </button>
                    @endif
                    <button type="button" wire:click="closeModal"
                        style="padding: 8px 16px; background: #FFFFFF; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; font-size: 13px; cursor: pointer;">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit"
                        style="padding: 8px 16px; background: var(--color-brand-500, #520000); color: #FFFFFF; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
