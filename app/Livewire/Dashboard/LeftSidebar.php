<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class LeftSidebar extends Component
{
    public bool $collapsed = false;

    public function mount(): void
    {
        $this->collapsed = (bool) request()->cookie('sidebar_collapsed', false);
    }

    public function toggleCollapse(): void
    {
        $this->collapsed = ! $this->collapsed;
        cookie()->queue('sidebar_collapsed', $this->collapsed ? '1' : '0', 60 * 24 * 365);
    }

    /**
     * @return array<int, array{group: string, items: array<int, array{label: string, icon: string, url: string, active: bool}>}>
     */
    public function getNavGroups(): array
    {
        $currentPath = request()->path();

        return [
            [
                'group' => __('shell.nav_dashboard'),
                'items' => [
                    [
                        'label' => __('shell.nav_dashboard'),
                        'icon' => 'home',
                        'url' => '/dashboard',
                        'active' => $currentPath === 'dashboard',
                    ],
                ],
            ],
            [
                'group' => __('shell.nav_practice'),
                'items' => [
                    [
                        'label' => __('shell.nav_matters'),
                        'icon' => 'briefcase',
                        'url' => '/matters',
                        'active' => str_starts_with($currentPath, 'matters'),
                    ],
                    [
                        'label' => __('shell.nav_contacts'),
                        'icon' => 'users',
                        'url' => '/contacts',
                        'active' => str_starts_with($currentPath, 'contacts'),
                    ],
                ],
            ],
            [
                'group' => __('shell.nav_work'),
                'items' => [
                    [
                        'label' => __('shell.nav_tasks'),
                        'icon' => 'check-circle',
                        'url' => '/tasks',
                        'active' => str_starts_with($currentPath, 'tasks'),
                    ],
                    [
                        'label' => __('shell.nav_documents'),
                        'icon' => 'file-text',
                        'url' => '/documents',
                        'active' => str_starts_with($currentPath, 'documents'),
                    ],
                    [
                        'label' => __('shell.nav_obligations'),
                        'icon' => 'alert-circle',
                        'url' => '/obligations',
                        'active' => str_starts_with($currentPath, 'obligations'),
                    ],
                ],
            ],
            [
                'group' => __('shell.nav_library'),
                'items' => [
                    [
                        'label' => __('shell.nav_clause_library'),
                        'icon' => 'book-open',
                        'url' => '/library-clauses',
                        'active' => str_starts_with($currentPath, 'library-clauses'),
                    ],
                    [
                        'label' => __('shell.nav_smart_lists'),
                        'icon' => 'list',
                        'url' => '/app/smart-lists',
                        'active' => str_starts_with($currentPath, 'app/smart-lists'),
                    ],
                ],
            ],
            [
                'group' => __('shell.nav_time'),
                'items' => [
                    [
                        'label' => __('shell.nav_time_entries'),
                        'icon' => 'clock',
                        'url' => '/time-entries',
                        'active' => str_starts_with($currentPath, 'time-entries'),
                    ],
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.left-sidebar', [
            'navGroups' => $this->getNavGroups(),
        ]);
    }
}
