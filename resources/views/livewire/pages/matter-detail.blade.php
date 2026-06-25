@php
    $statusColors = [
        'active' => ['bg' => '#F0FDF4', 'text' => '#166534'],
        'on_hold' => ['bg' => '#FFFBEB', 'text' => '#B45309'],
        'closed' => ['bg' => '#F5F5F4', 'text' => '#57534E'],
        'archived' => ['bg' => '#F5F5F4', 'text' => '#78716C'],
    ];
    $sc = $statusColors[$matter->status->value] ?? $statusColors['active'];

    $tabs = [
        'overview' => __('shell.matter_tab_overview'),
        'documents' => __('shell.matter_tab_documents') . ' (' . $matter->documents->count() . ')',
        'tasks' => __('shell.matter_tab_tasks') . ' (' . $matter->tasks->count() . ')',
        'hearings' => __('shell.matter_tab_hearings') . ' (' . $matter->hearings->count() . ')',
        'ai' => __('shell.matter_tab_ai') . ' (' . $matter->aiDocumentGenerations->count() . ')',
        'team' => __('shell.matter_tab_team') . ' (' . $matter->matterLawyers->count() . ')',
    ];
@endphp

<div>
    {{-- Header --}}
    <div style="margin-bottom: 24px;">
        <a href="/matters" style="font-size: 13px; color: var(--text-tertiary, #78716C); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 12px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="{{ app()->getLocale() === 'ar' ? '' : 'transform: rotate(180deg);' }}"><path d="m9 18 6-6-6-6"/></svg>
            {{ __('shell.back_to_matters') }}
        </a>
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
            <div>
                <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #1C1917); margin: 0 0 8px;">{{ $matter->title }}</h1>
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span style="font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 9999px; background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}; text-transform: uppercase; letter-spacing: 0.04em;">
                        {{ $matter->status->label() }}
                    </span>
                    @if ($matter->client)
                        <span style="font-size: 13px; color: var(--text-secondary, #44403C);">{{ $matter->client->display_name }}</span>
                    @endif
                    @if ($matter->practice_area)
                        <span style="font-size: 13px; color: var(--text-tertiary, #78716C);">{{ $matter->practice_area->label() }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="display: flex; gap: 0; border-bottom: 2px solid var(--border-default, #E7E5E4); margin-bottom: 20px; overflow-x: auto;">
        @foreach ($tabs as $key => $label)
            <button wire:click="setTab('{{ $key }}')"
                style="padding: 10px 16px; font-size: 13px; font-weight: 500; border: none; background: none; cursor: pointer; white-space: nowrap;
                    {{ $activeTab === $key
                        ? 'color: var(--color-brand-500, #0D5C2E); border-bottom: 2px solid var(--color-brand-500, #0D5C2E); margin-bottom: -2px;'
                        : 'color: var(--text-tertiary, #78716C);' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tab Content --}}
    @if ($activeTab === 'overview')
        {{-- Overview --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; padding: 20px;">
                <h3 style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin: 0 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-default, #E7E5E4);">{{ __('shell.matter_details') }}</h3>
                @foreach ([
                    __('shell.matter_internal_ref') => $matter->internal_reference,
                    __('shell.matter_opened') => $matter->opened_at?->format('Y-m-d'),
                    __('shell.matter_closed') => $matter->closed_at?->format('Y-m-d'),
                    __('shell.matter_lead_lawyer') => $matter->leadLawyer?->name,
                    __('shell.matter_created_by') => $matter->createdBy?->name,
                ] as $label => $value)
                    @if ($value)
                        <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px;">
                            <span style="color: var(--text-tertiary, #78716C);">{{ $label }}</span>
                            <span style="color: var(--text-primary, #1C1917); font-weight: 500;">{{ $value }}</span>
                        </div>
                    @endif
                @endforeach
            </div>

            <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; padding: 20px;">
                <h3 style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin: 0 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-default, #E7E5E4);">{{ __('shell.matter_counterparties') }}</h3>
                @forelse ($matter->counterparties as $cp)
                    <div style="padding: 6px 0; font-size: 13px; color: var(--text-primary, #1C1917);">
                        {{ $cp->display_name }}
                    </div>
                @empty
                    <p style="font-size: 13px; color: var(--text-tertiary, #78716C);">{{ __('shell.matter_no_counterparties') }}</p>
                @endforelse
            </div>

            @if ($matter->description)
                <div style="grid-column: span 2; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; padding: 20px;">
                    <h3 style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin: 0 0 8px;">{{ __('shell.matter_description') }}</h3>
                    <p style="font-size: 14px; color: var(--text-secondary, #44403C); line-height: 1.6; margin: 0; white-space: pre-wrap;">{{ $matter->description }}</p>
                </div>
            @endif
        </div>

    @elseif ($activeTab === 'documents')
        {{-- Documents --}}
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; overflow: hidden;">
            @forelse ($matter->documents as $doc)
                <a href="/matters/{{ $matter->id }}/documents/{{ $doc->id }}"
                   style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--border-default, #E7E5E4); text-decoration: none;"
                   onmouseover="this.style.background='var(--surface-card-hover, #F5F5F4)'" onmouseout="this.style.background='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-tertiary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 14px; font-weight: 500; color: var(--text-primary, #1C1917);">{{ $doc->title }}</div>
                        <div style="font-size: 12px; color: var(--text-tertiary, #78716C);">{{ $doc->updated_at->diffForHumans() }}</div>
                    </div>
                </a>
            @empty
                <div style="padding: 40px; text-align: center; color: var(--text-tertiary, #78716C); font-size: 13px;">{{ __('shell.matter_no_documents') }}</div>
            @endforelse
        </div>

    @elseif ($activeTab === 'tasks')
        {{-- Tasks --}}
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; overflow: hidden;">
            @php $priorityColors = ['urgent' => '#DC2626', 'high' => '#F59E0B', 'medium' => '#2563EB', 'normal' => '#78716C', 'low' => '#A8A29E']; @endphp
            @forelse ($matter->tasks as $task)
                <div style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--border-default, #E7E5E4);">
                    <span style="width: 8px; height: 8px; border-radius: 9999px; background: {{ $priorityColors[$task->priority?->value ?? 'normal'] ?? '#78716C' }}; flex-shrink: 0;"></span>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 14px; font-weight: 500; color: var(--text-primary, #1C1917);">{{ $task->title }}</div>
                        <div style="font-size: 12px; color: var(--text-tertiary, #78716C); display: flex; gap: 8px;">
                            <span>{{ $task->status?->label() }}</span>
                            @if ($task->assignedTo) <span>— {{ $task->assignedTo->name }}</span> @endif
                            @if ($task->due_date) <span>— {{ $task->due_date->format('Y-m-d') }}</span> @endif
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding: 40px; text-align: center; color: var(--text-tertiary, #78716C); font-size: 13px;">{{ __('shell.matter_no_tasks') }}</div>
            @endforelse
        </div>

    @elseif ($activeTab === 'hearings')
        {{-- Hearings --}}
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; overflow: hidden;">
            @forelse ($matter->hearings->sortByDesc('hearing_date') as $hearing)
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--border-default, #E7E5E4);">
                    <div style="flex-shrink: 0; width: 44px; text-align: center; background: var(--color-brand-50, #ECFAF1); border-radius: 6px; padding: 6px 0;">
                        <div style="font-size: 16px; font-weight: 700; color: var(--color-brand-700, #072E17); line-height: 1;">{{ $hearing->hearing_date?->format('d') }}</div>
                        <div style="font-size: 10px; font-weight: 500; color: var(--color-brand-500); text-transform: uppercase;">{{ $hearing->hearing_date?->translatedFormat('M') }}</div>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 14px; font-weight: 500; color: var(--text-primary, #1C1917);">
                            {{ $hearing->title ?? __('shell.matter_hearing') . ' #' . $loop->iteration }}
                        </div>
                        <div style="font-size: 12px; color: var(--text-tertiary, #78716C); display: flex; gap: 8px;">
                            <span>{{ $hearing->status ?? '' }}</span>
                            @if ($hearing->assignedLawyer) <span>— {{ $hearing->assignedLawyer->name }}</span> @endif
                            @if ($hearing->hearing_time) <span>— {{ $hearing->hearing_time }}</span> @endif
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding: 40px; text-align: center; color: var(--text-tertiary, #78716C); font-size: 13px;">{{ __('shell.matter_no_hearings') }}</div>
            @endforelse
        </div>

    @elseif ($activeTab === 'ai')
        {{-- AI Generated Documents --}}
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; overflow: hidden;">
            @forelse ($matter->aiDocumentGenerations as $gen)
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--border-default, #E7E5E4);">
                    <div style="flex-shrink: 0; width: 32px; height: 32px; background: var(--color-brand-50, #ECFAF1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-brand-500)" stroke-width="2"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/></svg>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 14px; font-weight: 500; color: var(--text-primary, #1C1917);">{{ $gen->template_key }}</div>
                        <div style="font-size: 12px; color: var(--text-tertiary, #78716C);">
                            {{ $gen->created_at->diffForHumans() }}
                            @if ($gen->generatedDocument) — <a href="/matters/{{ $matter->id }}/documents/{{ $gen->generatedDocument->id }}" style="color: var(--text-link, #0D5C2E);">{{ $gen->generatedDocument->title }}</a> @endif
                        </div>
                    </div>
                    <span style="font-size: 11px; font-weight: 500; padding: 2px 8px; border-radius: 9999px; background: {{ $gen->status === 'completed' ? '#F0FDF4' : ($gen->status === 'failed' ? '#FEF2F2' : '#FFFBEB') }}; color: {{ $gen->status === 'completed' ? '#166534' : ($gen->status === 'failed' ? '#B91C1C' : '#B45309') }};">
                        {{ $gen->status }}
                    </span>
                </div>
            @empty
                <div style="padding: 40px; text-align: center; color: var(--text-tertiary, #78716C); font-size: 13px;">{{ __('shell.matter_no_ai') }}</div>
            @endforelse
        </div>

    @elseif ($activeTab === 'team')
        {{-- Lawyer Team --}}
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; overflow: hidden;">
            @forelse ($matter->matterLawyers as $ml)
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--border-default, #E7E5E4);">
                    <div style="width: 36px; height: 36px; border-radius: 9999px; background: var(--color-brand-50, #ECFAF1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span style="font-weight: 600; font-size: 14px; color: var(--color-brand-700, #072E17);">{{ mb_substr($ml->user?->name ?? '?', 0, 1) }}</span>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 14px; font-weight: 500; color: var(--text-primary, #1C1917);">{{ $ml->user?->name }}</div>
                        <div style="font-size: 12px; color: var(--text-tertiary, #78716C);">{{ $ml->user?->email }}</div>
                    </div>
                    <span style="font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.04em;
                        {{ $ml->role === 'lead' ? 'background: var(--color-brand-50, #ECFAF1); color: var(--color-brand-700, #072E17);' : 'background: #F5F5F4; color: #57534E;' }}">
                        {{ $ml->role === 'lead' ? __('shell.matter_role_lead') : __('shell.matter_role_supporting') }}
                    </span>
                </div>
            @empty
                <div style="padding: 40px; text-align: center; color: var(--text-tertiary, #78716C); font-size: 13px;">{{ __('shell.matter_no_team') }}</div>
            @endforelse
        </div>
    @endif
</div>
