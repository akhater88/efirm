<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class QuickLinksRail extends Component
{
    /**
     * @return array<int, array{label: string, icon: string, url: string}>
     */
    public function getLinks(): array
    {
        return [
            ['label' => __('shell.nav_matters'), 'icon' => 'briefcase', 'url' => '/matters'],
            ['label' => __('shell.nav_contacts'), 'icon' => 'users', 'url' => '/contacts'],
            ['label' => __('shell.nav_documents'), 'icon' => 'file-text', 'url' => '/documents'],
            ['label' => __('shell.nav_tasks'), 'icon' => 'check-circle', 'url' => '/tasks'],
            ['label' => __('shell.nav_obligations'), 'icon' => 'alert-circle', 'url' => '/obligations'],
            ['label' => __('shell.nav_clause_library'), 'icon' => 'book-open', 'url' => '/library-clauses'],
            ['label' => __('shell.nav_time_entries'), 'icon' => 'clock', 'url' => '/time-entries'],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.quick-links-rail', [
            'links' => $this->getLinks(),
        ]);
    }
}
