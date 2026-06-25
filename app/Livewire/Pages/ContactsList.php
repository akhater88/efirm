<?php

namespace App\Livewire\Pages;

use App\Models\Contact;
use Livewire\Component;

class ContactsList extends Component
{
    public string $search = '';

    public string $typeFilter = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $editingId = null;

    public string $formType = 'person';

    public string $formFirstName = '';

    public string $formLastName = '';

    public string $formOrganizationName = '';

    public string $formEmail = '';

    public string $formPhone = '';

    public bool $formIsClient = false;

    public bool $formIsCounterparty = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function resetPage(): void
    {
        // Reset cursor pagination by removing the cursor query param
    }

    public function openCreate(): void
    {
        $this->reset(['formType', 'formFirstName', 'formLastName', 'formOrganizationName', 'formEmail', 'formPhone', 'formIsClient', 'formIsCounterparty', 'editingId']);
        $this->formType = 'person';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $contact = Contact::findOrFail($id);
        $this->editingId = $contact->id;
        $this->formType = $contact->type ?? 'person';
        $this->formFirstName = $contact->first_name ?? '';
        $this->formLastName = $contact->last_name ?? '';
        $this->formOrganizationName = $contact->organization_name ?? '';
        $this->formEmail = $contact->email ?? '';
        $this->formPhone = $contact->phone ?? '';
        $this->formIsClient = (bool) $contact->is_client;
        $this->formIsCounterparty = (bool) $contact->is_counterparty;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'formType' => 'required|in:person,organization',
            'formEmail' => 'nullable|email|max:255',
            'formPhone' => 'nullable|string|max:50',
            'formIsClient' => 'boolean',
            'formIsCounterparty' => 'boolean',
        ];

        if ($this->formType === 'person') {
            $rules['formFirstName'] = 'required|string|max:255';
            $rules['formLastName'] = 'required|string|max:255';
        } else {
            $rules['formOrganizationName'] = 'required|string|max:255';
        }

        $this->validate($rules);

        $data = [
            'type' => $this->formType,
            'first_name' => $this->formType === 'person' ? $this->formFirstName : null,
            'last_name' => $this->formType === 'person' ? $this->formLastName : null,
            'organization_name' => $this->formType === 'organization' ? $this->formOrganizationName : null,
            'email' => $this->formEmail ?: null,
            'phone' => $this->formPhone ?: null,
            'is_client' => $this->formIsClient,
            'is_counterparty' => $this->formIsCounterparty,
        ];

        if ($this->isEditing && $this->editingId) {
            $contact = Contact::findOrFail($this->editingId);
            $data['updated_by_user_id'] = auth()->id();
            $contact->update($data);
            session()->flash('message', __('common.saved'));
        } else {
            $data['workspace_id'] = auth()->user()->currentWorkspace()->id;
            $data['created_by_user_id'] = auth()->id();
            $data['updated_by_user_id'] = auth()->id();
            Contact::create($data);
            session()->flash('message', __('common.created'));
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if ($this->editingId) {
            $contact = Contact::findOrFail($this->editingId);
            $contact->delete();
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
        $query = Contact::orderByDesc('updated_at');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('display_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->typeFilter !== '') {
            $query->where('type', $this->typeFilter);
        }

        $contacts = $query->cursorPaginate(15);

        return view('livewire.pages.contacts-list', [
            'contacts' => $contacts,
        ])->layout('components.layouts.dashboard');
    }
}
