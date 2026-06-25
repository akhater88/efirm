<?php

namespace App\Livewire\Pages;

use App\Models\TimeEntry;
use Livewire\Component;

class TimeEntriesList extends Component
{
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetPage(): void
    {
        // Reset cursor pagination by removing the cursor query param
    }

    public function render()
    {
        $query = TimeEntry::with('matter:id,title', 'user:id,name')
            ->orderByDesc('started_at');

        if ($this->search !== '') {
            $query->where('description', 'like', '%'.$this->search.'%');
        }

        $timeEntries = $query->cursorPaginate(15);

        return view('livewire.pages.time-entries-list', [
            'timeEntries' => $timeEntries,
        ])->layout('layouts.dashboard')
            ->section('content');
    }
}
