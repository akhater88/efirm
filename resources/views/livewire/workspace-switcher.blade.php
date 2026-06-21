<div class="relative" x-data="{ open: false }">
    {{-- Trigger button --}}
    <button @click="open = !open"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700
                   bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
        <span>{{ $currentWorkspace?->name ?? __('workspace.no_workspace') }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="open" @click.outside="open = false" x-transition
         class="absolute mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-50
                {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }}">
        <div class="py-1">
            @foreach ($workspaces as $workspace)
                <button wire:click="switchTo('{{ $workspace->id }}')"
                        class="w-full text-start px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2
                               {{ $currentWorkspace?->id === $workspace->id ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                    @if ($currentWorkspace?->id === $workspace->id)
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <span class="w-4"></span>
                    @endif
                    {{ $workspace->name }}
                </button>
            @endforeach
        </div>
        <div class="border-t border-gray-200 py-1">
            <button wire:click="$set('showCreateModal', true)"
                    class="w-full text-start px-4 py-2 text-sm text-blue-600 hover:bg-blue-50">
                + {{ __('workspace.create') }}
            </button>
        </div>
    </div>

    {{-- Create Workspace Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             @keydown.escape.window="$wire.set('showCreateModal', false)">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6"
                 @click.outside="$wire.set('showCreateModal', false)">
                <h2 class="text-lg font-semibold mb-4">{{ __('workspace.create') }}</h2>

                <form wire:submit="createWorkspace">
                    <div class="mb-4">
                        <label for="newWorkspaceName" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('workspace.name') }}
                        </label>
                        <input type="text"
                               id="newWorkspaceName"
                               wire:model="newWorkspaceName"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                      focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required
                               autofocus>
                        @error('newWorkspaceName')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="newWorkspaceLocale" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('workspace.default_locale') }}
                        </label>
                        <select id="newWorkspaceLocale"
                                wire:model="newWorkspaceLocale"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="ar">العربية</option>
                            <option value="en">English</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button"
                                wire:click="$set('showCreateModal', false)"
                                class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            {{ __('common.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
