<?php

namespace App\Livewire\Dashboard\Widget;

use App\Models\Document;
use Livewire\Component;

class DocumentsWidget extends Component
{
    public function render()
    {
        $documents = Document::query()
            ->with('matter:id,title')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get(['id', 'title', 'matter_id', 'updated_at']);

        return view('livewire.dashboard.widget.documents-widget', [
            'documents' => $documents,
        ]);
    }
}
