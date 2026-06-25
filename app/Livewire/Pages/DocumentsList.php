<?php

namespace App\Livewire\Pages;

use App\Models\Document;
use Livewire\Component;

class DocumentsList extends Component
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
        $query = Document::with('matter:id,title')
            ->orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        $documents = $query->cursorPaginate(15);

        return view('livewire.pages.documents-list', [
            'documents' => $documents,
        ])->layout('layouts.dashboard')
            ->section('content');
    }
}
