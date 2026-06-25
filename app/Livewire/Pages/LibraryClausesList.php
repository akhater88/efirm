<?php

namespace App\Livewire\Pages;

use App\Models\LibraryClause;
use Livewire\Component;

class LibraryClausesList extends Component
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
        $query = LibraryClause::orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        $clauses = $query->cursorPaginate(15);

        return view('livewire.pages.library-clauses-list', [
            'clauses' => $clauses,
        ])->layout('components.layouts.dashboard');
    }
}
