@extends('layouts.dashboard')

@section('title', __('dashboard.title') . ' — ' . __('common.app_name'))

@section('content')
    {{-- Hero Banner --}}
    <livewire:dashboard.dashboard-hero />

    {{-- Widget grid placeholder --}}
    <div style="text-align: center; padding: 48px 0; color: var(--text-tertiary, #78716C); font-size: 14px;">
        {{ __('dashboard.empty_state') }}
    </div>
@endsection
