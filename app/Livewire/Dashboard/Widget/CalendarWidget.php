<?php

namespace App\Livewire\Dashboard\Widget;

use App\Models\Obligation;
use Livewire\Component;

class CalendarWidget extends Component
{
    public function render()
    {
        $events = Obligation::query()
            ->with('document:id,title')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(14))
            ->where('status', '!=', 'completed')
            ->orderBy('due_date')
            ->limit(5)
            ->get(['id', 'title', 'due_date', 'status', 'document_id']);

        return view('livewire.dashboard.widget.calendar-widget', [
            'events' => $events,
        ]);
    }
}
