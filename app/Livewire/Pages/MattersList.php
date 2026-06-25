<?php

namespace App\Livewire\Pages;

use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use App\Models\Contact;
use App\Models\Matter;
use Livewire\Component;

class MattersList extends Component
{
    public string $search = '';

    public string $statusFilter = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $editingId = null;

    public string $formTitle = '';

    public ?string $formClientId = null;

    public ?string $formPracticeArea = null;

    public ?string $formStatus = null;

    public string $formDescription = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetPage(): void
    {
        // Reset cursor pagination by removing the cursor query param
    }

    public function openCreate(): void
    {
        $this->reset(['formTitle', 'formClientId', 'formPracticeArea', 'formStatus', 'formDescription', 'editingId']);
        $this->isEditing = false;
        $this->formStatus = MatterStatus::Active->value;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $matter = Matter::findOrFail($id);
        $this->editingId = $matter->id;
        $this->formTitle = $matter->title ?? '';
        $this->formClientId = $matter->client_id;
        $this->formPracticeArea = $matter->practice_area?->value;
        $this->formStatus = $matter->status?->value;
        $this->formDescription = $matter->description ?? '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'formTitle' => 'required|string|max:255',
            'formClientId' => 'nullable|string',
            'formPracticeArea' => 'nullable|string',
            'formStatus' => 'nullable|string',
            'formDescription' => 'nullable|string|max:5000',
        ]);

        $data = [
            'title' => $this->formTitle,
            'client_id' => $this->formClientId ?: null,
            'practice_area' => $this->formPracticeArea ?: null,
            'status' => $this->formStatus ?: null,
            'description' => $this->formDescription ?: null,
        ];

        if ($this->isEditing && $this->editingId) {
            $matter = Matter::findOrFail($this->editingId);
            $data['updated_by_user_id'] = auth()->id();
            $matter->update($data);
            session()->flash('message', __('common.saved'));
        } else {
            $data['workspace_id'] = auth()->user()->currentWorkspace()->id;
            $data['created_by_user_id'] = auth()->id();
            $data['updated_by_user_id'] = auth()->id();
            Matter::create($data);
            session()->flash('message', __('common.created'));
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if ($this->editingId) {
            $matter = Matter::findOrFail($this->editingId);
            $matter->delete();
            session()->flash('message', __('common.deleted'));
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->editingId = null;
    }

    public function render()
    {
        $query = Matter::with('client:id,display_name')
            ->orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $matters = $query->cursorPaginate(15);

        $clients = Contact::where('is_client', true)->orderBy('display_name')->get(['id', 'display_name']);

        return view('livewire.pages.matters-list', [
            'matters' => $matters,
            'statuses' => MatterStatus::cases(),
            'practiceAreas' => PracticeArea::cases(),
            'clients' => $clients,
        ])->layout('components.layouts.dashboard');
    }
}
