<?php

namespace App\Livewire\Pages;

use App\Models\Contact;
use Livewire\Component;

class ContactsList extends Component
{
    public string $search = '';

    public string $typeFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function resetPage(): void
    {
        // Reset cursor pagination by removing the cursor query param
    }

    public function render()
    {
        $query = Contact::orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('display_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->typeFilter !== '') {
            $query->where('type', $this->typeFilter);
        }

        $contacts = $query->cursorPaginate(15);

        return view('livewire.pages.contacts-list', [
            'contacts' => $contacts,
        ])->layout('layouts.dashboard')
            ->section('content');
    }
}
