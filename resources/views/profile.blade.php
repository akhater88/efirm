@extends('layouts.app')

@section('title', __('profile.title') . ' — ' . __('common.app_name'))

@section('content')
    <div class="max-w-lg mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">{{ __('profile.title') }}</h1>

        {{-- Read-only fields (managed by Google) --}}
        <div class="mb-8 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">
                    {{ __('profile.email') }}
                </label>
                <div class="flex items-center gap-2">
                    <span class="text-gray-900">{{ $user->email }}</span>
                    <span class="text-xs text-gray-400">({{ __('profile.managed_by_google') }})</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">
                    {{ __('profile.avatar') }}
                </label>
                <div class="flex items-center gap-3">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="" class="w-12 h-12 rounded-full">
                    @else
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-700 font-medium">
                                {{ mb_substr($user->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <span class="text-xs text-gray-400">({{ __('profile.managed_by_google') }})</span>
                </div>
            </div>
        </div>

        {{-- Editable fields --}}
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('profile.name') }}
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $user->name) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       required
                       maxlength="255">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="preferred_locale" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('profile.preferred_locale') }}
                </label>
                <select id="preferred_locale"
                        name="preferred_locale"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="ar" {{ $user->preferred_locale === 'ar' ? 'selected' : '' }}>العربية</option>
                    <option value="en" {{ $user->preferred_locale === 'en' ? 'selected' : '' }}>English</option>
                </select>
            </div>

            <button type="submit"
                    class="px-6 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                {{ __('profile.update') }}
            </button>
        </form>
    </div>
@endsection
