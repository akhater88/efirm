<?php

namespace App\Livewire\Pages;

use App\Enums\ClauseLanguage;
use App\Models\LibraryClause;
use Livewire\Component;

class LibraryClausesList extends Component
{
    public string $search = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $editingId = null;

    public string $formTitle = '';

    public string $formCategory = '';

    public ?string $formLanguage = null;

    public string $formBodyEn = '';

    public string $formBodyAr = '';

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
        $this->reset(['formTitle', 'formCategory', 'formLanguage', 'formBodyEn', 'formBodyAr', 'editingId']);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $clause = LibraryClause::findOrFail($id);
        $this->editingId = $clause->id;
        $this->formTitle = $clause->title ?? '';
        $this->formCategory = $clause->clause_type ?? '';
        $this->formLanguage = $clause->language?->value;
        $this->formBodyEn = is_array($clause->body_en) ? json_encode($clause->body_en) : ($clause->body_en ?? '');
        $this->formBodyAr = is_array($clause->body_ar) ? json_encode($clause->body_ar) : ($clause->body_ar ?? '');
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'formTitle' => 'required|string|max:255',
            'formCategory' => 'nullable|string|max:255',
            'formLanguage' => 'nullable|string',
            'formBodyEn' => 'nullable|string',
            'formBodyAr' => 'nullable|string',
        ]);

        $data = [
            'title' => $this->formTitle,
            'clause_type' => $this->formCategory ?: null,
            'language' => $this->formLanguage ?: null,
            'body_en' => $this->formBodyEn ?: null,
            'body_ar' => $this->formBodyAr ?: null,
        ];

        if ($this->isEditing && $this->editingId) {
            $clause = LibraryClause::findOrFail($this->editingId);
            $data['updated_by_user_id'] = auth()->id();
            $clause->update($data);
            session()->flash('message', __('common.saved'));
        } else {
            $data['workspace_id'] = auth()->user()->currentWorkspace()->id;
            $data['created_by_user_id'] = auth()->id();
            $data['updated_by_user_id'] = auth()->id();
            LibraryClause::create($data);
            session()->flash('message', __('common.created'));
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if ($this->editingId) {
            $clause = LibraryClause::findOrFail($this->editingId);
            $clause->delete();
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
        $query = LibraryClause::orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        $clauses = $query->cursorPaginate(15);

        return view('livewire.pages.library-clauses-list', [
            'clauses' => $clauses,
            'languages' => ClauseLanguage::cases(),
        ])->layout('components.layouts.dashboard');
    }
}
