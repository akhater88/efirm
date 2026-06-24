@extends('layouts.dashboard')

@section('title', __('dashboard.title') . ' — ' . __('common.app_name'))

@section('content')
    {{-- Hero Banner --}}
    <livewire:dashboard.dashboard-hero />

    {{-- Widget Grid --}}
    <x-dashboard.widget-grid>
        <x-slot:topLeft>
            <x-dashboard.widget-card
                :title="__('dashboard.widget_matters')"
                state="empty"
                :empty-message="__('dashboard.no_recent_matters')"
                view-all-url="/app/matters"
                create-url="/app/matters/create"
                :create-label="__('shell.new_matter')"
            />
        </x-slot:topLeft>

        <x-slot:topRight>
            <x-dashboard.widget-card
                :title="__('dashboard.widget_calendar')"
                state="empty"
                :empty-message="__('dashboard.no_upcoming_events')"
            />
        </x-slot:topRight>

        <x-slot:bottomLeft>
            <x-dashboard.widget-card
                :title="__('dashboard.widget_documents')"
                state="empty"
                :empty-message="__('dashboard.no_recent_documents')"
                view-all-url="/app/documents"
            />
        </x-slot:bottomLeft>

        <x-slot:bottomRight>
            <x-dashboard.widget-card
                :title="__('dashboard.widget_tasks')"
                state="empty"
                :empty-message="__('dashboard.no_recent_tasks')"
                view-all-url="/app/tasks"
                create-url="/app/tasks/create"
                :create-label="__('shell.new_task')"
            />
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
