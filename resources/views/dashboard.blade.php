@extends('layouts.dashboard')

@section('title', __('dashboard.title') . ' — ' . __('common.app_name'))

@section('content')
    {{-- Hero Banner --}}
    <livewire:dashboard.dashboard-hero />

    {{-- Widget Grid --}}
    <x-dashboard.widget-grid>
        <x-slot:topLeft>
            <livewire:dashboard.widget.legal-matters-widget />
        </x-slot:topLeft>

        <x-slot:topRight>
            <livewire:dashboard.widget.calendar-widget />
        </x-slot:topRight>

        <x-slot:bottomLeft>
            <livewire:dashboard.widget.documents-widget />
        </x-slot:bottomLeft>

        <x-slot:bottomRight>
            <livewire:dashboard.widget.tasks-widget />
        </x-slot:bottomRight>

        <x-slot:feedLeft>
            <x-dashboard.widget-card
                :title="__('dashboard.widget_obligations')"
                state="empty"
                :empty-message="__('dashboard.no_upcoming_obligations')"
                view-all-url="/app/obligations"
            />
        </x-slot:feedLeft>

        <x-slot:feedRight>
            <x-dashboard.widget-card
                :title="__('dashboard.widget_renewals')"
                state="empty"
                :empty-message="__('dashboard.no_upcoming_renewals')"
            />
        </x-slot:feedRight>
    </x-dashboard.widget-grid>
@endsection
