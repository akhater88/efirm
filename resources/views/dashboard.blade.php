@extends('layouts.dashboard')

@section('title', __('dashboard.title') . ' — ' . __('common.app_name'))

@section('content')
    <div class="text-center mt-16">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">
            {{ __('dashboard.welcome_message') }}
        </h1>
        <p class="text-lg text-gray-600 mb-8">
            {{ $workspace?->name }}
        </p>
        <p class="text-gray-400">
            {{ __('dashboard.empty_state') }}
        </p>
    </div>
@endsection
