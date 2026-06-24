<?php

namespace App\Livewire\Dashboard\Widget;

use App\Models\ContractMetadata;
use Livewire\Component;

class UpcomingRenewalsFeed extends Component
{
    public string $search = '';

    public int $daysAhead = 60;

    public function render()
    {
        $query = ContractMetadata::query()
            ->with('document:id,title,matter_id', 'document.matter:id,title')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($this->daysAhead))
            ->orderBy('expiry_date');

        if ($this->search !== '') {
            $query->whereHas('document', function ($q) {
                $q->where('title', 'like', "%{$this->search}%");
            });
        }

        $renewals = $query->cursorPaginate(10);

        return view('livewire.dashboard.widget.upcoming-renewals-feed', [
            'renewals' => $renewals,
        ]);
    }
}
