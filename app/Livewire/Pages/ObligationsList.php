<?php

namespace App\Livewire\Pages;

use App\Enums\ObligationStatus;
use App\Models\Obligation;
use Livewire\Component;

class ObligationsList extends Component
{
    public string $search = '';

    public string $statusFilter = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $editingId = null;

    public string $formTitle = '';

    public ?string $formDueDate = null;

    public ?string $formStatus = null;

    public string $formDescription = '';

    public string $formResponsibleParty = '';

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
        $this->reset(['formTitle', 'formDueDate', 'formStatus', 'formDescription', 'formResponsibleParty', 'editingId']);
        $this->formStatus = ObligationStatus::Pending->value;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $obligation = Obligation::findOrFail($id);
        $this->editingId = $obligation->id;
        $this->formTitle = $obligation->title ?? '';
        $this->formDueDate = $obligation->due_date?->format('Y-m-d');
        $this->formStatus = $obligation->status?->value;
        $this->formDescription = $obligation->description ?? '';
        $this->formResponsibleParty = $obligation->responsible_party?->value ?? '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'formTitle' => 'required|string|max:255',
            'formDueDate' => 'required|date',
            'formStatus' => 'nullable|string',
            'formDescription' => 'nullable|string|max:5000',
            'formResponsibleParty' => 'nullable|string|max:255',
        ]);

        $data = [
            'title' => $this->formTitle,
            'due_date' => $this->formDueDate,
            'status' => $this->formStatus ?: null,
            'description' => $this->formDescription ?: null,
            'responsible_party' => $this->formResponsibleParty ?: null,
        ];

        if ($this->isEditing && $this->editingId) {
            $obligation = Obligation::findOrFail($this->editingId);
            $data['updated_by_user_id'] = auth()->id();
            $obligation->update($data);
            session()->flash('message', __('common.saved'));
        } else {
            $data['workspace_id'] = auth()->user()->currentWorkspace()->id;
            $data['created_by_user_id'] = auth()->id();
            $data['updated_by_user_id'] = auth()->id();
            Obligation::create($data);
            session()->flash('message', __('common.created'));
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if ($this->editingId) {
            $obligation = Obligation::findOrFail($this->editingId);
            $obligation->delete();
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
        $query = Obligation::with('document:id,title')
            ->orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $obligations = $query->cursorPaginate(15);

        return view('livewire.pages.obligations-list', [
            'obligations' => $obligations,
            'statuses' => ObligationStatus::cases(),
        ])->layout('components.layouts.dashboard');
    }
}
