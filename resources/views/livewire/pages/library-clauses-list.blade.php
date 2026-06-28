<div>
    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #2D0A0A); margin: 0;">
            {{ __('shell.library_clauses_list_title') }}
        </h1>
        <button wire:click="openCreate"
           style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--color-brand-500, #520000); color: #fff; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; cursor: pointer; border: none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            {{ __('shell.library_clauses_create') }}
        </button>
    </div>

    {{-- Filters --}}
    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('shell.library_clauses_search_placeholder') }}"
                style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #D6D3D1); border-radius: 8px; font-size: 14px; background: var(--surface-card, #FFFFFF); color: var(--text-primary, #2D0A0A); outline: none; box-sizing: border-box;"
            />
        </div>
    </div>

    {{-- Table --}}
    @if ($clauses->count() > 0)
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: var(--surface-subtle, #F5F5F4); border-bottom: 1px solid var(--border-default, #D6D3D1);">
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #5C3535);">{{ __('shell.col_title') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #5C3535);">{{ __('shell.col_category') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #5C3535);">{{ __('shell.col_language') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #5C3535);">{{ __('shell.col_risk_position') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: 600; color: var(--text-secondary, #5C3535);">{{ __('shell.col_updated') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clauses as $clause)
                        @php
                            $riskColors = match($clause->risk_position) {
                                \App\Enums\RiskPosition::Favourable => ['bg' => '#F0FDF4', 'text' => '#166534'],
                                \App\Enums\RiskPosition::Balanced => ['bg' => '#EFF6FF', 'text' => '#1D4ED8'],
                                \App\Enums\RiskPosition::Adverse => ['bg' => '#FEF2F2', 'text' => '#B91C1C'],
                                default => ['bg' => '#F5F5F4', 'text' => '#5C3535'],
                            };
                        @endphp
                        <tr
                            wire:click="openEdit('{{ $clause->id }}')"
                            style="border-bottom: 1px solid var(--border-default, #D6D3D1); cursor: pointer; transition: background 0.15s;"
                            onmouseover="this.style.background='var(--surface-subtle, #F5F5F4)'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding: 12px 16px; color: var(--text-primary, #2D0A0A); font-weight: 500;">
                                {{ $clause->title }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #5C3535);">
                                {{ $clause->clause_type ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-secondary, #5C3535);">
                                {{ $clause->language?->label() ?? '—' }}
                            </td>
                            <td style="padding: 12px 16px;">
                                @if ($clause->risk_position)
                                    <span style="display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 500; background: {{ $riskColors['bg'] }}; color: {{ $riskColors['text'] }};">
                                        {{ $clause->risk_position->label() }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-muted, #A89090); font-size: 13px;">
                                {{ $clause->updated_at?->diffForHumans() ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 16px;">
            {{ $clauses->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div style="text-align: center; padding: 64px 24px; background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #D6D3D1); border-radius: 12px;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted, #A89090)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 16px;">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary, #2D0A0A); margin: 0 0 8px;">
                {{ __('shell.library_clauses_empty_title') }}
            </h3>
            <p style="font-size: 14px; color: var(--text-secondary, #5C3535); margin: 0;">
                {{ __('shell.library_clauses_empty_description') }}
            </p>
        </div>
    @endif

    {{-- Modal --}}
    @if ($showModal)
    <div style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px;"
         @keydown.escape.window="$wire.closeModal()">
        <div wire:click="closeModal" style="position: absolute; inset: 0; background: rgba(0,0,0,0.4);"></div>
        <div style="position: relative; background: #FFFFFF; border-radius: 12px; box-shadow: var(--shadow-xl); width: 100%; max-width: 600px; max-height: 80vh; overflow-y: auto; padding: 24px;">
            <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0 0 20px;">
                {{ $isEditing ? __('common.edit') : __('common.create') }} — {{ __('shell.library_clauses_list_title') }}
            </h2>
            <form wire:submit="save">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.label_title') }}</label>
                    <input type="text" wire:model="formTitle" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                    @error('formTitle') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.label_category') }}</label>
                    <input type="text" wire:model="formCategory" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;" />
                    @error('formCategory') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.label_language') }}</label>
                    <select wire:model="formLanguage" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                        <option value="">{{ __('shell.label_select') }}</option>
                        @foreach ($languages as $language)
                            <option value="{{ $language->value }}">{{ $language->label() }}</option>
                        @endforeach
                    </select>
                    @error('formLanguage') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.label_body_en') }}</label>
                    <textarea wire:model="formBodyEn" rows="4" dir="ltr" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box; resize: vertical;"></textarea>
                    @error('formBodyEn') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #4A2020); margin-bottom: 4px;">{{ __('shell.label_body_ar') }}</label>
                    <textarea wire:model="formBodyAr" rows="4" dir="rtl" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 6px; font-size: 14px; box-sizing: border-box; resize: vertical;"></textarea>
                    @error('formBodyAr') <span style="font-size: 12px; color: var(--color-danger-500);">{{ $message }}</span> @enderror
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
