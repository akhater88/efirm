<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\Matter;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

class DocumentEditor extends Component
{
    public Document $document;

    public Matter $matter;

    public string $documentTitle;

    public string $currentVersionId;

    public int $currentVersionNumber;

    public string $lastSavedAt;

    public function mount(Matter $matter, Document $document): void
    {
        Gate::authorize('view', $document);

        $this->document = $document->load('currentVersion');
        $this->matter = $matter;
        $this->documentTitle = $document->title;
        $this->currentVersionId = $document->current_version_id ?? '';
        $this->currentVersionNumber = $document->currentVersion?->version_number ?? 1;
        $this->lastSavedAt = $document->updated_at?->toIso8601String() ?? now()->toIso8601String();
    }

    #[On('editor-save')]
    public function save(array $body, bool $isAutosave = false): void
    {
        Gate::authorize('update', $this->document);

        $documentService = app(DocumentService::class);

        // Optimistic locking: check if current_version_id still matches
        $this->document->refresh();
        if ($this->document->current_version_id !== $this->currentVersionId) {
            $this->dispatch('editor-conflict', [
                'server_version_id' => $this->document->current_version_id,
                'your_version_id' => $this->currentVersionId,
            ]);

            return;
        }

        $changeSummary = $isAutosave ? null : null; // Manual save summary handled by frontend

        $version = $documentService->createVersion(
            $this->document,
            $body,
            auth()->user(),
            $changeSummary,
        );

        if ($version === null) {
            // No changes detected
            $this->dispatch('editor-save-skipped');

            return;
        }

        $this->currentVersionId = $version->id;
        $this->currentVersionNumber = $version->version_number;
        $this->lastSavedAt = now()->toIso8601String();

        $this->dispatch('editor-saved', [
            'version_id' => $version->id,
            'version_number' => $version->version_number,
        ]);
    }

    public function forceSave(array $body): void
    {
        Gate::authorize('update', $this->document);

        $documentService = app(DocumentService::class);

        // Force save regardless of version conflict
        $this->document->refresh();

        $version = $documentService->createVersion(
            $this->document,
            $body,
            auth()->user(),
            __('documents.force_saved_after_conflict'),
        );

        if ($version) {
            $this->currentVersionId = $version->id;
            $this->currentVersionNumber = $version->version_number;
            $this->lastSavedAt = now()->toIso8601String();
        }

        $this->dispatch('editor-saved', [
            'version_id' => $version?->id ?? $this->currentVersionId,
            'version_number' => $version?->version_number ?? $this->currentVersionNumber,
        ]);
    }

    public function reloadLatest(): void
    {
        $this->document->refresh();
        $this->document->load('currentVersion');
        $this->currentVersionId = $this->document->current_version_id ?? '';
        $this->currentVersionNumber = $this->document->currentVersion?->version_number ?? 1;

        $this->dispatch('editor-load-content', [
            'body' => $this->document->currentVersion?->body ?? ['type' => 'doc', 'content' => [['type' => 'paragraph']]],
        ]);
    }

    public function updateTitle(string $title): void
    {
        Gate::authorize('update', $this->document);

        $this->document->update([
            'title' => $title,
            'updated_by_user_id' => auth()->id(),
        ]);
        $this->documentTitle = $title;
    }

    public function getEditorContentProperty(): array
    {
        return $this->document->currentVersion?->body ?? [
            'type' => 'doc',
            'content' => [['type' => 'paragraph']],
        ];
    }

    public function render()
    {
        return view('documents.editor')
            ->layout('layouts.editor');
    }
}
