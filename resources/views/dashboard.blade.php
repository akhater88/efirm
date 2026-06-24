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
            <livewire:dashboard.widget.upcoming-obligations-feed />
        </x-slot:feedLeft>

        <x-slot:feedRight>
            <livewire:dashboard.widget.upcoming-renewals-feed />
        </x-slot:feedRight>
    </x-dashboard.widget-grid>
@endsection
