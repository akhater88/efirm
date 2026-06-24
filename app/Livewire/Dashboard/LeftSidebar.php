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
                        'url' => '/app/matters',
                        'active' => str_starts_with($currentPath, 'app/matters'),
                    ],
                    [
                        'label' => __('shell.nav_contacts'),
                        'icon' => 'users',
                        'url' => '/app/contacts',
                        'active' => str_starts_with($currentPath, 'app/contacts'),
                    ],
                ],
            ],
            [
                'group' => __('shell.nav_work'),
                'items' => [
                    [
                        'label' => __('shell.nav_tasks'),
                        'icon' => 'check-circle',
                        'url' => '/app/tasks',
                        'active' => str_starts_with($currentPath, 'app/tasks'),
                    ],
                    [
                        'label' => __('shell.nav_documents'),
                        'icon' => 'file-text',
                        'url' => '/app/documents',
                        'active' => str_starts_with($currentPath, 'app/documents'),
                    ],
                    [
                        'label' => __('shell.nav_obligations'),
                        'icon' => 'alert-circle',
                        'url' => '/app/obligations',
                        'active' => str_starts_with($currentPath, 'app/obligations'),
                    ],
                ],
            ],
            [
                'group' => __('shell.nav_library'),
                'items' => [
                    [
                        'label' => __('shell.nav_clause_library'),
                        'icon' => 'book-open',
                        'url' => '/app/library-clauses',
                        'active' => str_starts_with($currentPath, 'app/library-clauses'),
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
                        'url' => '/app/time-entries',
                        'active' => str_starts_with($currentPath, 'app/time-entries'),
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
