<?php

namespace App\Livewire\Pages;

use App\Models\Matter;
use App\Models\TimeEntry;
use Livewire\Component;

class TimeEntriesList extends Component
{
    public string $search = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $editingId = null;

    public string $formDescription = '';

    public ?int $formDurationMinutes = null;

    public ?string $formMatterId = null;

    public bool $formIsBillable = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetPage(): void
    {
        // Reset cursor pagination by removing the cursor query param
    }

    public function openCreate(): void
    {
        $this->reset(['formDescription', 'formDurationMinutes', 'formMatterId', 'formIsBillable', 'editingId']);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $entry = TimeEntry::findOrFail($id);
        $this->editingId = $entry->id;
        $this->formDescription = $entry->description ?? '';
        $this->formDurationMinutes = $entry->duration_minutes;
        $this->formMatterId = $entry->matter_id;
        $this->formIsBillable = (bool) $entry->is_billable;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'formDescription' => 'nullable|string|max:1000',
            'formDurationMinutes' => 'required|integer|min:1',
            'formMatterId' => 'nullable|string',
            'formIsBillable' => 'boolean',
        ]);

        $data = [
            'description' => $this->formDescription ?: null,
            'duration_minutes' => $this->formDurationMinutes,
            'matter_id' => $this->formMatterId ?: null,
            'is_billable' => $this->formIsBillable,
        ];

        if ($this->isEditing && $this->editingId) {
            $entry = TimeEntry::findOrFail($this->editingId);
            $data['updated_by_user_id'] = auth()->id();
            $entry->update($data);
            session()->flash('message', __('common.saved'));
        } else {
            $data['workspace_id'] = auth()->user()->currentWorkspace()->id;
            $data['user_id'] = auth()->id();
            $data['created_by_user_id'] = auth()->id();
            $data['updated_by_user_id'] = auth()->id();
            $data['started_at'] = now();
            TimeEntry::create($data);
            session()->flash('message', __('common.created'));
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if ($this->editingId) {
            $entry = TimeEntry::findOrFail($this->editingId);
            $entry->delete();
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
        $query = TimeEntry::with('matter:id,title', 'user:id,name')
            ->orderByDesc('started_at');

        if ($this->search !== '') {
            $query->where('description', 'like', '%'.$this->search.'%');
        }

        $timeEntries = $query->cursorPaginate(15);

        $matters = Matter::orderBy('title')->get(['id', 'title']);

        return view('livewire.pages.time-entries-list', [
            'timeEntries' => $timeEntries,
            'matters' => $matters,
        ])->layout('components.layouts.dashboard');
    }
}
