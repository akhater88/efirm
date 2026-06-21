@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $workspace = auth()->user()->currentWorkspace();
@endphp

<div class="min-h-screen flex flex-col">
    {{-- ─── Top Bar ─────────────────────────────────────────────────────────── --}}
    <header class="h-16 bg-white border-b border-gray-200 flex items-center px-4 gap-4 shrink-0 z-20">
        {{-- Back / Breadcrumb --}}
        <a href="{{ $workspace ? url('/admin/workspace/' . $workspace->slug . '/matters/' . $matter->id . '/edit') : '#' }}"
           class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $isRtl ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7' }}" />
            </svg>
            <span>{{ __('matters.matters') }}</span>
        </a>

        <span class="text-gray-300">/</span>
        <span class="text-sm text-gray-500 truncate max-w-[200px]">{{ $matter->title }}</span>
        <span class="text-gray-300">/</span>

        {{-- Document Title (editable) --}}
        <input type="text"
               value="{{ $documentTitle }}"
               class="text-sm font-medium text-gray-900 border-0 bg-transparent focus:ring-0 focus:outline-none px-1 py-0.5 rounded hover:bg-gray-100 focus:bg-gray-100 truncate max-w-[300px]"
               wire:change="updateTitle($event.target.value)"
               wire:keydown.enter="updateTitle($event.target.value); $event.target.blur()">

        {{-- Save Status --}}
        <div class="flex items-center gap-2 ms-auto">
            <span id="save-status"
                  class="text-sm text-green-600"
                  data-text-saved="{{ __('documents.save_status_saved') }}"
                  data-text-saving="{{ __('documents.save_status_saving') }}"
                  data-text-unsaved="{{ __('documents.save_status_unsaved') }}"
                  data-text-error="{{ __('documents.save_status_error') }}"
                  data-text-conflict="{{ __('documents.save_status_conflict') }}">
                {{ __('documents.save_status_saved') }}
            </span>

            <span id="current-version" class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded">
                V{{ $currentVersionNumber }}
            </span>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2">
            <button wire:click="toggleShareModal"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
                {{ __('documents.share') }}
            </button>
            <a href="{{ url('/api/v1/documents/' . $document->id . '/export') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
               download>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                {{ __('documents.export_docx') }}
            </a>
            <button wire:click="toggleVersionHistory"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 {{ $showVersionHistory ? 'bg-indigo-50 text-indigo-700 border-indigo-300' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('documents.history') }}
            </button>
            <button onclick="document.getElementById('save-summary-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                {{ __('documents.save') }}
            </button>
        </div>
    </header>

    {{-- ─── Toolbar ─────────────────────────────────────────────────────────── --}}
    <div class="editor-toolbar sticky top-0 z-10 border-b border-gray-200 px-4 py-2 flex items-center gap-1 flex-wrap">
        {{-- Format group --}}
        <button data-editor-action="bold" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-sm font-bold" title="{{ __('documents.toolbar_bold') }} (Ctrl+B)">B</button>
        <button data-editor-action="italic" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-sm italic" title="{{ __('documents.toolbar_italic') }} (Ctrl+I)">I</button>
        <button data-editor-action="underline" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-sm underline" title="{{ __('documents.toolbar_underline') }} (Ctrl+U)">U</button>
        <button data-editor-action="strike" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-sm line-through" title="{{ __('documents.toolbar_strikethrough') }}">S</button>

        <div class="divider"></div>

        {{-- Heading group --}}
        <button data-editor-action="h1" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-xs font-bold" title="{{ __('documents.toolbar_h1') }}">H1</button>
        <button data-editor-action="h2" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-xs font-bold" title="{{ __('documents.toolbar_h2') }}">H2</button>
        <button data-editor-action="h3" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-xs font-bold" title="{{ __('documents.toolbar_h3') }}">H3</button>
        <button data-editor-action="paragraph" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-xs" title="{{ __('documents.toolbar_paragraph') }}">P</button>

        <div class="divider"></div>

        {{-- List group --}}
        <button data-editor-action="bulletList" class="w-8 h-8 flex items-center justify-center rounded text-gray-600" title="{{ __('documents.toolbar_bullet_list') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>
        </button>
        <button data-editor-action="orderedList" class="w-8 h-8 flex items-center justify-center rounded text-gray-600" title="{{ __('documents.toolbar_ordered_list') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>

        <div class="divider"></div>

        {{-- Direction group --}}
        <button data-editor-action="dirLtr" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-xs" title="{{ __('documents.toolbar_ltr') }}">LTR</button>
        <button data-editor-action="dirRtl" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 text-xs" title="{{ __('documents.toolbar_rtl') }}">RTL</button>

        <div class="divider"></div>

        {{-- Align group --}}
        <button data-editor-action="alignLeft" class="w-8 h-8 flex items-center justify-center rounded text-gray-600" title="{{ __('documents.toolbar_align_left') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h12M3 18h18" /></svg>
        </button>
        <button data-editor-action="alignCenter" class="w-8 h-8 flex items-center justify-center rounded text-gray-600" title="{{ __('documents.toolbar_align_center') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M6 12h12M3 18h18" /></svg>
        </button>
        <button data-editor-action="alignRight" class="w-8 h-8 flex items-center justify-center rounded text-gray-600" title="{{ __('documents.toolbar_align_right') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M9 12h12M3 18h18" /></svg>
        </button>

        <div class="divider"></div>

        {{-- Undo/Redo --}}
        <button data-editor-action="undo" class="w-8 h-8 flex items-center justify-center rounded text-gray-600" title="{{ __('documents.toolbar_undo') }} (Ctrl+Z)">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4" /></svg>
        </button>
        <button data-editor-action="redo" class="w-8 h-8 flex items-center justify-center rounded text-gray-600" title="{{ __('documents.toolbar_redo') }} (Ctrl+Y)">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a5 5 0 00-5 5v2M21 10l-4-4M21 10l-4 4" /></svg>
        </button>
    </div>

    {{-- ─── Editor Canvas + Version History Drawer ─────────────────────────── --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Main editor area --}}
        <main class="flex-1 overflow-y-auto bg-gray-100 flex justify-center py-8">
            @if ($showDiff)
                {{-- ─── Diff View ──────────────────────────────────────────────── --}}
                <div class="w-full max-w-[800px] bg-white shadow-sm rounded-lg min-h-[80vh] p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('documents.comparing_versions', [
                                'old' => collect($versionList)->firstWhere('id', $diffOldVersionId)['version_number'] ?? '?',
                                'new' => collect($versionList)->firstWhere('id', $diffNewVersionId)['version_number'] ?? '?',
                            ]) }}
                        </h2>
                        <button wire:click="closeDiff" class="text-sm text-gray-500 hover:text-gray-700">
                            {{ __('common.close') }}
                        </button>
                    </div>

                    @if (!empty($diffStats))
                        <div class="flex gap-4 mb-4 text-sm">
                            <span class="text-green-700">+{{ $diffStats['added_words'] ?? 0 }} {{ __('documents.words_added') }}</span>
                            <span class="text-red-700">-{{ $diffStats['removed_words'] ?? 0 }} {{ __('documents.words_removed') }}</span>
                        </div>
                    @endif

                    <div class="prose prose-lg max-w-none leading-relaxed" dir="auto">
                        @foreach ($diffBlocks as $block)
                            @if ($block['type'] === 'unchanged')
                                <span class="text-gray-900">{!! nl2br(e($block['text'])) !!}</span>
                            @elseif ($block['type'] === 'removed')
                                <span class="bg-red-100 text-red-800 line-through">{!! nl2br(e($block['text'])) !!}</span>
                            @elseif ($block['type'] === 'added')
                                <span class="bg-green-100 text-green-800 underline">{!! nl2br(e($block['text'])) !!}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                {{-- ─── Normal Editor ──────────────────────────────────────── --}}
                <div class="w-full max-w-[800px] bg-white shadow-sm rounded-lg min-h-[80vh]">
                    @if ($viewingVersionId && $viewingVersionId !== $currentVersionId)
                        <div class="bg-yellow-50 border-b border-yellow-200 px-6 py-3 flex items-center justify-between">
                            <span class="text-sm text-yellow-800">
                                {{ __('documents.viewing_old_version', ['version' => collect($versionList)->firstWhere('id', $viewingVersionId)['version_number'] ?? '?']) }}
                            </span>
                            <div class="flex gap-2">
                                <button wire:click="viewCurrentVersion" class="text-sm text-yellow-700 underline hover:text-yellow-900">
                                    {{ __('documents.back_to_current') }}
                                </button>
                                <button wire:click="restoreVersion('{{ $viewingVersionId }}')"
                                        class="text-sm text-indigo-700 font-medium hover:text-indigo-900">
                                    {{ __('documents.restore_this_version') }}
                                </button>
                            </div>
                        </div>
                    @endif
                    <div id="editor-canvas"
                         data-placeholder="{{ __('documents.editor_placeholder') }}"
                         wire:ignore>
                    </div>
                </div>
            @endif
        </main>

        {{-- ─── Version History Drawer ──────────────────────────────────────── --}}
        @if ($showVersionHistory)
            <aside class="w-[360px] bg-white border-s border-gray-200 flex flex-col shrink-0 overflow-hidden">
                <div class="h-14 px-4 flex items-center justify-between border-b border-gray-200 shrink-0">
                    <h3 class="font-semibold text-gray-900">{{ __('documents.version_history') }}</h3>
                    <button wire:click="toggleVersionHistory" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto">
                    @forelse ($versionList as $version)
                        <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer
                                    {{ $version['is_current'] ? 'bg-indigo-50 border-s-3 border-s-indigo-500' : '' }}
                                    {{ $viewingVersionId === $version['id'] ? 'bg-blue-50' : '' }}"
                             wire:click="viewVersion('{{ $version['id'] }}')">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-semibold text-gray-900 {{ $version['is_current'] ? 'text-indigo-700' : '' }}">
                                    V{{ $version['version_number'] }}
                                </span>
                                @if ($version['is_current'])
                                    <span class="text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded">{{ __('documents.current') }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $version['created_by'] }} &middot; {{ $version['created_at'] }}
                            </div>
                            @if ($version['change_summary'])
                                <div class="text-xs text-gray-600 mt-1 truncate">{{ $version['change_summary'] }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center text-sm text-gray-500">
                            {{ __('documents.no_versions') }}
                        </div>
                    @endforelse
                </div>

                {{-- Drawer footer --}}
                <div class="px-4 py-3 border-t border-gray-200 shrink-0 space-y-2">
                    @if (count($versionList) >= 2)
                        <button wire:click="showDiffBetween('{{ $versionList[1]['id'] ?? '' }}', '{{ $versionList[0]['id'] ?? '' }}')"
                                class="w-full text-sm text-center text-indigo-600 hover:text-indigo-800 font-medium py-1.5">
                            {{ __('documents.compare_latest_versions') }}
                        </button>
                    @endif
                </div>
            </aside>
        @endif
    </div>

    {{-- ─── Share Modal ──────────────────────────────────────────────────────── --}}
    @if ($showShareModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[80vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between shrink-0">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('documents.share_document') }}</h3>
                    <button wire:click="toggleShareModal" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4 overflow-y-auto">
                    {{-- Create new share --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('documents.share_recipient') }}</label>
                            <input type="email" wire:model="shareRecipientEmail"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="{{ __('documents.share_recipient_placeholder') }}">
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('documents.share_format') }}</label>
                                <select wire:model="shareFormat" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    <option value="docx">.docx</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('documents.share_expiry') }}</label>
                                <select wire:model="shareExpiry" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    <option value="">{{ __('documents.share_no_expiry') }}</option>
                                    <option value="7">{{ __('documents.share_7_days') }}</option>
                                    <option value="30">{{ __('documents.share_30_days') }}</option>
                                </select>
                            </div>
                        </div>
                        <button wire:click="createShare"
                                class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            {{ __('documents.create_share_link') }}
                        </button>
                    </div>

                    {{-- Newly created share URL --}}
                    @if ($lastCreatedShareUrl)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <p class="text-sm font-medium text-green-800 mb-1">{{ __('documents.share_link_created') }}</p>
                            <div class="flex items-center gap-2">
                                <input type="text" value="{{ $lastCreatedShareUrl }}" readonly
                                       class="flex-1 text-xs bg-white border border-green-300 rounded px-2 py-1"
                                       id="share-url-input">
                                <button onclick="navigator.clipboard.writeText(document.getElementById('share-url-input').value)"
                                        class="text-xs text-green-700 font-medium hover:text-green-900 px-2 py-1">
                                    {{ __('documents.copy_link') }}
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Active shares --}}
                    @if (count($shareList) > 0)
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('documents.active_shares') }}</h4>
                            <div class="space-y-2">
                                @foreach ($shareList as $share)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg text-sm {{ !$share['is_active'] ? 'opacity-50' : '' }}">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs text-gray-500 truncate">
                                                {{ $share['recipient_email'] ?? __('documents.share_no_recipient') }}
                                                &middot; {{ $share['download_count'] }} {{ __('documents.downloads') }}
                                                @if ($share['last_accessed_at'])
                                                    &middot; {{ __('documents.last_accessed') }}: {{ $share['last_accessed_at'] }}
                                                @endif
                                            </div>
                                            @if ($share['expires_at'])
                                                <div class="text-xs text-gray-400">{{ __('documents.expires') }}: {{ $share['expires_at'] }}</div>
                                            @endif
                                        </div>
                                        @if ($share['is_active'])
                                            <button wire:click="revokeShare('{{ $share['id'] }}')"
                                                    class="text-xs text-red-600 hover:text-red-800 font-medium ms-3 shrink-0">
                                                {{ __('documents.revoke') }}
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 ms-3">{{ __('documents.revoked') }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ─── Conflict Modal ──────────────────────────────────────────────────── --}}
    <div id="conflict-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('documents.conflict_title') }}</h3>
            <p class="text-sm text-gray-600 mb-6">{{ __('documents.conflict_description') }}</p>
            <div class="flex gap-3 justify-end">
                <button onclick="Livewire.dispatch('editor-reload-latest'); document.getElementById('conflict-modal').classList.add('hidden')"
                        wire:click="reloadLatest"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('documents.conflict_discard_mine') }}
                </button>
                <button onclick="if(window.__editor) { Livewire.dispatch('editor-force-save', { body: window.__editor.getJSON() }); } document.getElementById('conflict-modal').classList.add('hidden')"
                        wire:click="forceSave([])"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    {{ __('documents.conflict_keep_mine') }}
                </button>
            </div>
        </div>
    </div>

    {{-- ─── Save Summary Modal ──────────────────────────────────────────────── --}}
    <div id="save-summary-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('documents.save_with_summary') }}</h3>
            <textarea id="save-summary-input"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                      rows="3"
                      placeholder="{{ __('documents.save_summary_placeholder') }}"></textarea>
            <div class="flex gap-3 justify-end mt-4">
                <button onclick="document.getElementById('save-summary-modal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('common.cancel') }}
                </button>
                <button onclick="if(window.__editor) { Livewire.dispatch('editor-save', { body: window.__editor.getJSON(), isAutosave: false }); } document.getElementById('save-summary-modal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    {{ __('documents.save') }}
                </button>
            </div>
        </div>
    </div>

    {{-- ─── Pass initial content to JS ──────────────────────────────────────── --}}
    <script>
        window.__EDITOR_INITIAL_CONTENT__ = @json($this->editorContent);
    </script>
</div>
