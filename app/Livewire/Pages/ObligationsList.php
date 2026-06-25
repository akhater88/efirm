<?php

namespace App\Livewire\Pages;

use App\Enums\ObligationStatus;
use App\Models\Obligation;
use Livewire\Component;

class ObligationsList extends Component
{
    public string $search = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetPage(): void
    {
        // Reset cursor pagination by removing the cursor query param
    }

    public function render()
    {
        $query = Obligation::with('document:id,title')
            ->orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $obligations = $query->cursorPaginate(15);

        return view('livewire.pages.obligations-list', [
            'obligations' => $obligations,
            'statuses' => ObligationStatus::cases(),
        ])->layout('components.layouts.dashboard');
    }
}
