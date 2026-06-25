<?php

namespace App\Livewire\Dashboard\Widget;

use App\Models\Matter;
use Livewire\Component;

class LegalMattersWidget extends Component
{
    public function render()
    {
        $matters = Matter::query()
            ->with('client:id,display_name')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get(['id', 'title', 'status', 'client_id', 'updated_at']);

        return view('livewire.dashboard.widget.legal-matters-widget', [
            'matters' => $matters,
        ]);
    }
}
