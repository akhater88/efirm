<?php

namespace App\Livewire\Pages;

use App\Models\Matter;
use Livewire\Component;

class MatterDetail extends Component
{
    public Matter $matter;

    public string $activeTab = 'overview';

    public function mount(string $id): void
    {
        $this->matter = Matter::with([
            'client',
            'counterparties',
            'leadLawyer',
            'documents',
            'hearings.assignedLawyer',
            'tasks.assignedTo',
            'aiDocumentGenerations.generatedDocument',
            'matterLawyers.user',
            'createdBy',
        ])->findOrFail($id);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.pages.matter-detail')
            ->layout('components.layouts.dashboard');
    }
}
