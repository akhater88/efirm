@extends('layouts.dashboard')

@section('title', __('profile.title') . ' — ' . __('brand.app_name'))

@section('content')
    <div style="max-width: 560px;">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #1C1917); margin: 0 0 24px;">{{ __('profile.title') }}</h1>

        {{-- Read-only fields --}}
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; padding: 20px; margin-bottom: 24px;">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 500; color: var(--text-tertiary, #78716C); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em;">
                    {{ __('profile.email') }}
                </label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 14px; color: var(--text-primary, #1C1917);" dir="ltr">{{ $user->email }}</span>
                    <span style="font-size: 11px; color: var(--text-tertiary, #78716C);">({{ __('profile.managed_by_google') }})</span>
                </div>
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 500; color: var(--text-tertiary, #78716C); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em;">
                    {{ __('profile.avatar') }}
                </label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="" style="width: 48px; height: 48px; border-radius: 9999px; object-fit: cover;">
                    @else
                        <div style="width: 48px; height: 48px; border-radius: 9999px; background: var(--color-brand-50, #ECFAF1); display: flex; align-items: center; justify-content: center;">
                            <span style="color: var(--color-brand-700, #072E17); font-weight: 600; font-size: 18px;">
                                {{ mb_substr($user->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <span style="font-size: 11px; color: var(--text-tertiary, #78716C);">({{ __('profile.managed_by_google') }})</span>
                </div>
            </div>
        </div>

        {{-- Editable fields --}}
        <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; padding: 20px;">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 16px;">
                    <label for="name" style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 6px;">
                        {{ __('profile.name') }}
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255"
                           style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; font-size: 14px; color: var(--text-primary, #1C1917); outline: none; box-sizing: border-box;"
                           onfocus="this.style.borderColor='var(--border-focus, #0D5C2E)'; this.style.boxShadow='var(--ring-brand)'"
                           onblur="this.style.borderColor='var(--border-default, #E7E5E4)'; this.style.boxShadow='none'">
                    @error('name')
                        <p style="margin: 4px 0 0; font-size: 12px; color: var(--color-danger-500, #DC2626);">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="preferred_locale" style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary, #44403C); margin-bottom: 6px;">
                        {{ __('profile.preferred_locale') }}
                    </label>
                    <select id="preferred_locale" name="preferred_locale"
                            style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; font-size: 14px; color: var(--text-primary, #1C1917); outline: none; box-sizing: border-box; background: var(--surface-card, #FFFFFF);"
                            onfocus="this.style.borderColor='var(--border-focus, #0D5C2E)'"
                            onblur="this.style.borderColor='var(--border-default, #E7E5E4)'">
                        <option value="ar" {{ $user->preferred_locale === 'ar' ? 'selected' : '' }}>العربية</option>
                        <option value="en" {{ $user->preferred_locale === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>

                <button type="submit"
                        style="padding: 10px 24px; background: var(--color-brand-500, #0D5C2E); color: #FFFFFF; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    {{ __('profile.update') }}
                </button>
            </form>
        </div>
    </div>
@endsection
