<?php

namespace App\Livewire\Pages\Settings;

use App\Models\TaskType;
use App\Models\TaskWorkflow;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dashboard')]
class TaskTypesSettings extends Component
{
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $editingId = null;

    public string $formNameEn = '';

    public string $formNameAr = '';

    public string $formSlug = '';

    public string $formIcon = 'clipboard';

    public string $formColor = '#0D5C2E';

    public ?string $formDefaultWorkflowId = null;

    public bool $formIsActive = true;

    public int $formSortOrder = 0;

    /** @var array<int, array<string, mixed>> */
    public array $formCustomFields = [];

    public function openCreate(): void
    {
        $this->reset([
            'formNameEn', 'formNameAr', 'formSlug', 'formIcon', 'formColor',
            'formDefaultWorkflowId', 'formIsActive', 'formSortOrder',
            'formCustomFields', 'editingId',
        ]);
        $this->formIcon = 'clipboard';
        $this->formColor = '#0D5C2E';
        $this->formIsActive = true;
        $this->formSortOrder = 0;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $taskType = TaskType::findOrFail($id);
        $this->editingId = $taskType->id;
        $this->formNameEn = $taskType->name_en;
        $this->formNameAr = $taskType->name_ar;
        $this->formSlug = $taskType->slug;
        $this->formIcon = $taskType->icon;
        $this->formColor = $taskType->color;
        $this->formDefaultWorkflowId = $taskType->default_workflow_id;
        $this->formIsActive = $taskType->is_active;
        $this->formSortOrder = $taskType->sort_order;
        $this->formCustomFields = $taskType->custom_fields ?? [];
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function updatedFormNameEn(): void
    {
        if (! $this->isEditing) {
            $this->formSlug = Str::slug($this->formNameEn);
        }
    }

    public function save(): void
    {
        $this->validate([
            'formNameEn' => 'required|string|max:100',
            'formNameAr' => 'required|string|max:100',
            'formSlug' => 'required|string|max:50',
            'formIcon' => 'required|string|max:50',
            'formColor' => 'required|string|max:20',
            'formDefaultWorkflowId' => 'nullable|string',
            'formSortOrder' => 'required|integer|min:0',
            'formCustomFields' => 'nullable|array',
            'formCustomFields.*.key' => 'required|string|max:50',
            'formCustomFields.*.label_en' => 'required|string|max:100',
            'formCustomFields.*.label_ar' => 'required|string|max:100',
            'formCustomFields.*.type' => 'required|string|in:text,number,date,select,textarea,checkbox',
            'formCustomFields.*.required' => 'boolean',
            'formCustomFields.*.options' => 'nullable|string',
        ]);

        $customFields = collect($this->formCustomFields)->map(function (array $field): array {
            $options = null;
            if ($field['type'] === 'select' && ! empty($field['options'])) {
                $options = array_map('trim', explode(',', (string) $field['options']));
            }

            return [
                'key' => $field['key'],
                'label_en' => $field['label_en'],
                'label_ar' => $field['label_ar'],
                'type' => $field['type'],
                'required' => (bool) ($field['required'] ?? false),
                'options' => $options,
            ];
        })->values()->all();

        $data = [
            'name_en' => $this->formNameEn,
            'name_ar' => $this->formNameAr,
            'slug' => $this->formSlug,
            'icon' => $this->formIcon,
            'color' => $this->formColor,
            'default_workflow_id' => $this->formDefaultWorkflowId ?: null,
            'custom_fields' => ! empty($customFields) ? $customFields : null,
            'is_active' => $this->formIsActive,
            'sort_order' => $this->formSortOrder,
        ];

        if ($this->isEditing && $this->editingId) {
            $taskType = TaskType::findOrFail($this->editingId);
            $taskType->update($data);
            session()->flash('message', __('common.saved'));
        } else {
            $workspace = auth()->user()->currentWorkspace();
            $data['workspace_id'] = $workspace->id;
            TaskType::create($data);
            session()->flash('message', __('common.created'));
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if ($this->editingId) {
            $taskType = TaskType::findOrFail($this->editingId);
            $taskType->delete();
            session()->flash('message', __('common.deleted'));
        }

        $this->closeModal();
    }

    public function addCustomField(): void
    {
        $this->formCustomFields[] = [
            'key' => '',
            'label_en' => '',
            'label_ar' => '',
            'type' => 'text',
            'required' => false,
            'options' => '',
        ];
    }

    public function removeCustomField(int $index): void
    {
        unset($this->formCustomFields[$index]);
        $this->formCustomFields = array_values($this->formCustomFields);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->editingId = null;
    }

    public function render()
    {
        $taskTypes = TaskType::orderBy('sort_order')
            ->orderBy('name_en')
            ->get();

        $workflows = TaskWorkflow::orderBy('name')->get(['id', 'name']);

        return view('livewire.pages.settings.task-types-settings', [
            'taskTypes' => $taskTypes,
            'workflows' => $workflows,
        ]);
    }
}
