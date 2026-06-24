<?php

namespace App\Livewire\Dashboard\Widget;

use App\Models\Document;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class DocumentsWidget extends Component
{
    public function render()
    {
        $user = auth()->user();
        $workspace = $user->currentWorkspace();

        $cacheKey = "dashboard:documents:{$workspace?->id}:{$user->id}";

        $documents = Cache::remember($cacheKey, 300, function () {
            return Document::query()
                ->with('matter:id,title')
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(['id', 'title', 'matter_id', 'updated_at']);
        });

        return view('livewire.dashboard.widget.documents-widget', [
            'documents' => $documents,
        ]);
    }
}
