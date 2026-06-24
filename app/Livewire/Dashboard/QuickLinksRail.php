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
            ['label' => __('shell.nav_matters'), 'icon' => 'briefcase', 'url' => '/app/matters'],
            ['label' => __('shell.nav_contacts'), 'icon' => 'users', 'url' => '/app/contacts'],
            ['label' => __('shell.nav_documents'), 'icon' => 'file-text', 'url' => '/app/documents'],
            ['label' => __('shell.nav_tasks'), 'icon' => 'check-circle', 'url' => '/app/tasks'],
            ['label' => __('shell.nav_obligations'), 'icon' => 'alert-circle', 'url' => '/app/obligations'],
            ['label' => __('shell.nav_clause_library'), 'icon' => 'book-open', 'url' => '/app/library-clauses'],
            ['label' => __('shell.nav_time_entries'), 'icon' => 'clock', 'url' => '/app/time-entries'],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.quick-links-rail', [
            'links' => $this->getLinks(),
        ]);
    }
}
