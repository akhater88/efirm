<div>
    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #1C1917); margin: 0;">
            {{ __('shell.contacts_list_title') }}
        </h1>
        <button wire:click="openCreate"
           style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--color-brand-500, #0D5C2E); color: #fff; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; cursor: pointer; border: none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            {{ __('shell.contacts_create') }}
        </button>
    </div>

    {{-- Filters --}}
    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('shell.contacts_search_placeholder') }}"
                style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #D6D3D1); border-radius: 8px; font-size: 14px; background: var(--surface-card, #FFFFFF); color: var(--text-primary, #1C1917); outline: none; box-sizing: border-box;"
            />
        </div>
        <div>
            <select
                wire:model.live="typeFilter"
                style="padding: 8px 12px; border: 1px solid var(--border-default, #D6D3D1); border-radius: 8px; font-size: 14px; background: var(--surface-card, #FFFFFF); color: var(--text-primary, #1C1917); outline: none; cursor: pointer;"
            >
                <option value="">{{ __('shell.filter_all_types') }}</option>
                <option value="person">{{ __('shell.contact_type_person') }}</option>
                <option value="organization">{{ __('shell.contact_type_organization') }}</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    @if ($contacts->count() > 0)
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: var(--surface-subtle, #F5F5F4); border-bottom: 1px solid var(--border-default, #D6D3D1);">
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_display_name') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_type') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_email') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_phone') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_flags') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #57534E);">{{ __('shell.col_updated') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contacts as $contact)
                        <tr
                            wire:click="openEdit('{{ $contact->id }}')"
                            style="border-bottom: 1px solid var(--border-default, #D6D3D1); cursor: pointer; transition: background 0.15s;"
                            onmouseover="this.style.background='var(--surface-subtle, #F5F5F4)'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding: 12px 16px; color: var(--text-primary, #1C1917); font-weight: 500;">
                                {{ $contact->display_name }}
                            </td>
                            <td style="padding: 12px 16px;">
                                @php
                                    $typeBadge = match($contact->type) {
                                        'person' => ['bg' => '#EFF6FF', 'text' => '#1D4ED8', 'label' => __('shell.contact_type_person')],
                                        'organization' => ['bg' => '#F0FDF4', 'text' => '#166534', 'label' => __('shell.contact_type_organization')],
                                        default => ['bg' => '#F5F5F4', 'text' => '#57534E', 'label' => $contact->type ?? '—'],
                                    };
                                @endphp
                                <span style="display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 500; background: {{ $typeBadge['bg'] }}; color: {{ $typeBadge['text'] }};">
                                    {{ $typeBadge['label'] }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $contact->email ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #57534E);">
                                {{ $contact->phone ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    @if ($contact->is_client)
                                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-secondary, #57534E);" title="{{ __('shell.flag_client') }}">
                                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #16A34A;"></span>
                                            {{ __('shell.flag_client') }}
                                        </span>
                                    @endif
                                    @if ($contact->is_counterparty)
                                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-secondary, #57534E);" title="{{ __('shell.flag_counterparty') }}">
                                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #D97706;"></span>
                                            {{ __('shell.flag_counterparty') }}
                                        </span>
                                    @endif
                                    @if (!$contact->is_client && !$contact->is_counterparty)
                                        <span style="color: var(--text-muted, #A8A29E);">—</span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-muted, #A8A29E); font-size: 13px;">
                                {{ $contact->updated_at?->diffForHumans() ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 16px;">
            {{ $contacts->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div style="text-align: center; padding: 64px 24px; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted, #A8A29E)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 16px;">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary, #1C1917); margin: 0 0 8px;">
                {{ __('shell.contacts_empty_title') }}
            </h3>
            <p style="font-size: 14px; color: var(--text-secondary, #57534E); margin: 0 0 24px;">
                {{ __('shell.contacts_empty_description') }}
            </p>
            <button wire:click="openCreate"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--color-brand-500, #0D5C2E); color: #fff; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; border: none; cursor: pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('shell.contacts_create') }}
            </button>
        </div>
    @endif

    {{-- Modal --}}
    @if ($showModal)
    <div style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px;"
         @keydown.escape.window="$wire.closeModal()">
        <div wire:click="closeModal" style="position: absolute; inset: 0; background: rgba(0,0,0,0.4);"></div>
        <div style="position: relative; background: #FFFFFF; border-radius: 12px; box-shadow: var(--shadow-xl); width: 100%; max-width: 600px; max-height: 80vh; overflow-y: auto; padding: 24px;">
            <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0 0 20px;">
                {{ $isEditing ? __('common.edit') : __('common.create') }} — {{ __('shell.contacts_list_title') }}
            </h2>
            <form wire:submit="save">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_type') }}</label>
                    <select wire:model.live="formType" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                        <option value="person">{{ __('shell.contact_type_person') }}</option>
                        <option value="organization">{{ __('shell.contact_type_organization') }}</option>
                    </select>
                    @error('formType') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                @if ($formType === 'person')
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_first_name') }}</label>
                        <input type="text" wire:model="formFirstName" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                        @error('formFirstName') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_last_name') }}</label>
                        <input type="text" wire:model="formLastName" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                        @error('formLastName') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_organization_name') }}</label>
                        <input type="text" wire:model="formOrganizationName" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                        @error('formOrganizationName') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                    </div>
                @endif
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_email') }}</label>
                    <input type="email" wire:model="formEmail" dir="ltr" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                    @error('formEmail') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 4px;">{{ __('shell.label_phone') }}</label>
                    <input type="text" wire:model="formPhone" dir="ltr" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                    @error('formPhone') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px; display: flex; gap: 16px;">
                    <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); cursor: pointer;">
                        <input type="checkbox" wire:model="formIsClient" />
                        {{ __('shell.label_is_client') }}
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); cursor: pointer;">
                        <input type="checkbox" wire:model="formIsCounterparty" />
                        {{ __('shell.label_is_counterparty') }}
                    </label>
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
                        style="padding: 8px 16px; background: var(--color-brand-500, #0D5C2E); color: #FFFFFF; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
