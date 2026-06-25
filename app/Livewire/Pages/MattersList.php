<?php

namespace App\Livewire\Pages;

use App\Enums\MatterStatus;
use App\Models\Matter;
use Livewire\Component;

class MattersList extends Component
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
        $query = Matter::with('client:id,display_name')
            ->orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $matters = $query->cursorPaginate(15);

        return view('livewire.pages.matters-list', [
            'matters' => $matters,
            'statuses' => MatterStatus::cases(),
        ])->layout('components.layouts.dashboard');
    }
}
