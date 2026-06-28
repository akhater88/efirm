<div>
    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #2D0A0A); margin: 0;">
            {{ __('shell.task_types_title') }}
        </h1>
        <button wire:click="openCreate"
           style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--color-brand-500, #520000); color: #fff; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; border: none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            {{ __('shell.task_type_create') }}
        </button>
    </div>

    {{-- Flash message --}}
    @if (session()->has('message'))
        <div style="padding: 12px 16px; background: var(--color-success-50, #F0FDF4); border: 1px solid var(--color-success-300, #86EFAC); border-radius: 8px; margin-bottom: 16px; color: var(--color-success-700, #15803D); font-size: 14px;">
            {{ session('message') }}
        </div>
    @endif

    {{-- Task Type Cards --}}
    @if ($taskTypes->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
            @foreach ($taskTypes as $taskType)
                <div
                    wire:click="openEdit('{{ $taskType->id }}')"
                    style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px; padding: 20px; cursor: pointer; transition: box-shadow 0.15s, border-color 0.15s; position: relative;"
                    onmouseover="this.style.borderColor='var(--color-brand-300, #E07070)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'"
                    onmouseout="this.style.borderColor='var(--border-default, #D6D3D1)'; this.style.boxShadow='none'"
                >
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        {{-- Color swatch --}}
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: {{ $taskType->color }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                @if ($taskType->icon === 'file-text')
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>
                                @elseif ($taskType->icon === 'briefcase')
                                    <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/>
                                @else
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                                @endif
                            </svg>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 16px; font-weight: 600; color: var(--text-primary, #2D0A0A); margin-bottom: 2px;">
                                {{ $taskType->localizedName() }}
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted, #A89090);">
                                {{ $taskType->slug }}
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        {{-- Active badge --}}
                        @if ($taskType->is_active)
                            <span style="display: inline-flex; align-items: center; padding: 2px 8px; background: var(--color-success-50, #F0FDF4); color: var(--color-success-700, #15803D); font-size: 12px; border-radius: 9999px; font-weight: 500;">
                                {{ __('shell.task_type_active') }}
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; padding: 2px 8px; background: var(--surface-subtle, #F5F5F4); color: var(--text-muted, #A89090); font-size: 12px; border-radius: 9999px; font-weight: 500;">
                                {{ __('common.inactive') }}
                            </span>
                        @endif

                        {{-- Custom fields count --}}
                        @if (is_array($taskType->custom_fields) && count($taskType->custom_fields) > 0)
                            <span style="display: inline-flex; align-items: center; padding: 2px 8px; background: var(--surface-subtle, #F5F5F4); color: var(--text-secondary, #5C3535); font-size: 12px; border-radius: 9999px;">
                                {{ trans_choice('shell.task_type_custom_field_count', count($taskType->custom_fields), ['count' => count($taskType->custom_fields)]) }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div style="text-align: center; padding: 64px 24px; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted, #A89090)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 16px;">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
            </svg>
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary, #2D0A0A); margin: 0 0 8px;">
                {{ __('shell.task_type_no_types') }}
            </h3>
            <button wire:click="openCreate"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--color-brand-500, #520000); color: #fff; border-radius: 8px; font-size: 14px; font-weight: 500; border: none; cursor: pointer; margin-top: 16px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('shell.task_type_create') }}
            </button>
        </div>
    @endif

    {{-- Modal --}}
    @if ($showModal)
    <div style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px;"
         @keydown.escape.window="$wire.closeModal()">
        <div wire:click="closeModal" style="position: absolute; inset: 0; background: rgba(0,0,0,0.4);"></div>
        <div style="position: relative; background: #FFFFFF; border-radius: 12px; box-shadow: var(--shadow-xl); width: 100%; max-width: 700px; max-height: 85vh; overflow-y: auto; padding: 24px;">
            <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0 0 20px;">
                {{ $isEditing ? __('common.edit') : __('common.create') }} — {{ __('shell.task_types_title') }}
            </h2>
            <form wire:submit="save">
                {{-- Name EN --}}
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.task_type_name_en') }}</label>
                    <input type="text" wire:model.live.debounce.300ms="formNameEn" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                    @error('formNameEn') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>

                {{-- Name AR --}}
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.task_type_name_ar') }}</label>
                    <input type="text" wire:model="formNameAr" dir="rtl" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                    @error('formNameAr') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>

                {{-- Slug --}}
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.task_type_slug') }}</label>
                    <input type="text" wire:model="formSlug" dir="ltr" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                    @error('formSlug') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>

                {{-- Icon + Color row --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.task_type_icon') }}</label>
                        <input type="text" wire:model="formIcon" dir="ltr" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                        @error('formIcon') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.task_type_color') }}</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="color" wire:model="formColor" style="width: 40px; height: 36px; padding: 2px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; cursor: pointer;" />
                            <input type="text" wire:model="formColor" dir="ltr" style="flex: 1; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                        </div>
                        @error('formColor') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Default Workflow --}}
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.task_type_workflow') }}</label>
                    <select wire:model="formDefaultWorkflowId" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                        <option value="">{{ __('shell.label_select') }}</option>
                        @foreach ($workflows as $workflow)
                            <option value="{{ $workflow->id }}">{{ $workflow->name }}</option>
                        @endforeach
                    </select>
                    @error('formDefaultWorkflowId') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>

                {{-- Active + Sort Order row --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); cursor: pointer;">
                            <input type="checkbox" wire:model="formIsActive" style="width: 16px; height: 16px; accent-color: var(--color-brand-500, #520000);" />
                            {{ __('shell.task_type_active') }}
                        </label>
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.task_type_sort') }}</label>
                        <input type="number" wire:model="formSortOrder" min="0" dir="ltr" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                        @error('formSortOrder') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Custom Fields Builder --}}
                <div style="margin-bottom: 14px; padding-top: 16px; border-top: 1px solid var(--border-default, #E7E5E4);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <label style="font-size: 14px; font-weight: 600; color: var(--text-primary, #2D0A0A);">{{ __('shell.task_type_custom_fields') }}</label>
                        <button type="button" wire:click="addCustomField"
                            style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; background: var(--surface-subtle, #F5F5F4); border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 13px; cursor: pointer; color: var(--text-secondary, #5C3535);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            {{ __('shell.task_type_add_field') }}
                        </button>
                    </div>

                    @forelse ($formCustomFields as $index => $field)
                        <div style="background: var(--surface-subtle, #F5F5F4); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; padding: 12px; margin-bottom: 8px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: var(--text-muted, #A89090); margin-bottom: 2px;">{{ __('shell.task_type_field_key') }}</label>
                                    <input type="text" wire:model="formCustomFields.{{ $index }}.key" dir="ltr" style="width: 100%; padding: 6px 8px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 4px; font-size: 13px; box-sizing: border-box;" />
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: var(--text-muted, #A89090); margin-bottom: 2px;">{{ __('shell.task_type_field_label_en') }}</label>
                                    <input type="text" wire:model="formCustomFields.{{ $index }}.label_en" style="width: 100%; padding: 6px 8px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 4px; font-size: 13px; box-sizing: border-box;" />
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: var(--text-muted, #A89090); margin-bottom: 2px;">{{ __('shell.task_type_field_label_ar') }}</label>
                                    <input type="text" wire:model="formCustomFields.{{ $index }}.label_ar" dir="rtl" style="width: 100%; padding: 6px 8px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 4px; font-size: 13px; box-sizing: border-box;" />
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 8px; align-items: end;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: var(--text-muted, #A89090); margin-bottom: 2px;">{{ __('shell.task_type_field_type') }}</label>
                                    <select wire:model="formCustomFields.{{ $index }}.type" style="width: 100%; padding: 6px 8px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 4px; font-size: 13px; box-sizing: border-box;">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="select">Select</option>
                                        <option value="textarea">Textarea</option>
                                        <option value="checkbox">Checkbox</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: var(--text-muted, #A89090); margin-bottom: 2px;">{{ __('shell.task_type_field_options') }}</label>
                                    <input type="text" wire:model="formCustomFields.{{ $index }}.options" placeholder="Option1, Option2, ..." dir="ltr" style="width: 100%; padding: 6px 8px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 4px; font-size: 13px; box-sizing: border-box;" />
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; padding-bottom: 2px;">
                                    <label style="display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-secondary, #5C3535); cursor: pointer;">
                                        <input type="checkbox" wire:model="formCustomFields.{{ $index }}.required" style="accent-color: var(--color-brand-500, #520000);" />
                                        {{ __('shell.task_type_field_required') }}
                                    </label>
                                    <button type="button" wire:click="removeCustomField({{ $index }})"
                                        style="padding: 4px 8px; background: var(--color-danger-50); color: var(--color-danger-700); border: 1px solid var(--color-danger-300); border-radius: 4px; font-size: 12px; cursor: pointer;">
                                        {{ __('common.delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="font-size: 13px; color: var(--text-muted, #A89090); text-align: center; padding: 12px 0;">
                            {{ __('shell.task_type_no_types') }}
                        </p>
                    @endforelse
                </div>

                {{-- Actions --}}
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
