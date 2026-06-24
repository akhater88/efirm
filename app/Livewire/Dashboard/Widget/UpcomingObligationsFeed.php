<?php

namespace App\Livewire\Dashboard\Widget;

use App\Models\Obligation;
use Livewire\Component;

class UpcomingObligationsFeed extends Component
{
    public string $search = '';

    public int $daysAhead = 14;

    public function render()
    {
        $query = Obligation::query()
            ->with('document:id,title')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays($this->daysAhead))
            ->where('status', '!=', 'completed')
            ->orderBy('due_date');

        if ($this->search !== '') {
            $query->where('title', 'like', "%{$this->search}%");
        }

        $obligations = $query->cursorPaginate(10);

        return view('livewire.dashboard.widget.upcoming-obligations-feed', [
            'obligations' => $obligations,
        ]);
    }
}
