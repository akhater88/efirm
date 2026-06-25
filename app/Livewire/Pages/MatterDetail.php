<?php

namespace App\Livewire\Pages;

use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use App\Models\Contact;
use App\Models\Matter;
use Livewire\Component;

class MatterDetail extends Component
{
    public Matter $matter;

    public string $activeTab = 'overview';

    public bool $editing = false;

    public string $formTitle = '';

    public ?string $formClientId = null;

    public ?string $formPracticeArea = null;

    public ?string $formStatus = null;

    public ?string $formDescription = null;

    public ?string $formInternalReference = null;

    public ?string $formOpenedAt = null;

    public ?string $formClosedAt = null;

    public ?string $formStage = null;

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

    public function startEditing(): void
    {
        $this->formTitle = $this->matter->title ?? '';
        $this->formClientId = $this->matter->client_id;
        $this->formPracticeArea = $this->matter->practice_area?->value;
        $this->formStatus = $this->matter->status?->value;
        $this->formDescription = $this->matter->description;
        $this->formInternalReference = $this->matter->internal_reference;
        $this->formOpenedAt = $this->matter->opened_at?->format('Y-m-d');
        $this->formClosedAt = $this->matter->closed_at?->format('Y-m-d');
        $this->formStage = $this->matter->stage;
        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->editing = false;
    }

    public function save(): void
    {
        $this->validate([
            'formTitle' => 'required|string|max:255',
            'formClientId' => 'required|exists:contacts,id',
            'formPracticeArea' => 'nullable|string',
            'formStatus' => 'nullable|string',
            'formDescription' => 'nullable|string|max:5000',
            'formInternalReference' => 'nullable|string|max:100',
            'formOpenedAt' => 'nullable|date',
            'formClosedAt' => 'nullable|date',
            'formStage' => 'nullable|string|max:100',
        ]);

        $this->matter->update([
            'title' => $this->formTitle,
            'client_id' => $this->formClientId,
            'practice_area' => $this->formPracticeArea ?: null,
            'status' => $this->formStatus ?: null,
            'description' => $this->formDescription ?: null,
            'internal_reference' => $this->formInternalReference ?: null,
            'opened_at' => $this->formOpenedAt ?: null,
            'closed_at' => $this->formClosedAt ?: null,
            'stage' => $this->formStage ?: null,
            'updated_by_user_id' => auth()->id(),
        ]);

        $this->matter->refresh();
        $this->matter->load('client');
        $this->editing = false;
    }

    public function render()
    {
        $clients = $this->editing
            ? Contact::where('is_client', true)->get(['id', 'display_name'])
            : collect();

        return view('livewire.pages.matter-detail', [
            'clients' => $clients,
            'statuses' => MatterStatus::cases(),
            'practiceAreas' => PracticeArea::cases(),
        ])->layout('components.layouts.dashboard');
    }
}
