<?php

namespace App\Livewire\Documents;

use App\Models\AiInteraction;
use App\Models\Document;
use App\Models\DocumentClause;
use App\Models\DocumentShare;
use App\Models\DocumentVersion;
use App\Models\Matter;
use App\Services\AiOrchestrationService;
use App\Services\DocumentService;
use App\Services\VersionDiffService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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

    public bool $showVersionHistory = false;

    public array $versionList = [];

    public ?string $viewingVersionId = null;

    public bool $showDiff = false;

    public ?string $diffOldVersionId = null;

    public ?string $diffNewVersionId = null;

    public array $diffBlocks = [];

    public array $diffStats = [];

    public bool $showShareModal = false;

    public array $shareList = [];

    public string $shareRecipientEmail = '';

    public string $shareFormat = 'docx';

    public string $shareExpiry = '';

    public string $lastCreatedShareUrl = '';

    // AI Panel
    public bool $showAiPanel = false;

    public string $aiPrompt = '';

    public ?string $aiLastResponse = null;

    public ?string $aiLastInteractionId = null;

    public ?string $aiLastType = null;

    public bool $aiLoading = false;

    public array $aiHistory = [];

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

    // ─── Version History ───────────────────────────────────────────────────

    public function toggleVersionHistory(): void
    {
        $this->showVersionHistory = ! $this->showVersionHistory;
        $this->showDiff = false;

        if ($this->showVersionHistory) {
            $this->loadVersionList();
        }
    }

    public function loadVersionList(): void
    {
        $this->versionList = $this->document->versions()
            ->with('createdBy')
            ->orderByDesc('version_number')
            ->get()
            ->map(fn (DocumentVersion $v) => [
                'id' => $v->id,
                'version_number' => $v->version_number,
                'change_summary' => $v->change_summary,
                'created_by' => $v->createdBy?->name ?? __('common.unknown'),
                'created_at' => $v->created_at?->format('d/m/Y H:i'),
                'is_current' => $v->id === $this->currentVersionId,
            ])
            ->toArray();
    }

    public function viewVersion(string $versionId): void
    {
        $version = DocumentVersion::where('document_id', $this->document->id)
            ->where('id', $versionId)
            ->firstOrFail();

        $this->viewingVersionId = $versionId;

        $this->dispatch('editor-load-content', [
            'body' => $version->body,
            'readOnly' => $version->id !== $this->currentVersionId,
        ]);
    }

    public function viewCurrentVersion(): void
    {
        $this->viewingVersionId = null;
        $this->reloadLatest();
    }

    public function restoreVersion(string $versionId): void
    {
        Gate::authorize('update', $this->document);

        $version = DocumentVersion::where('document_id', $this->document->id)
            ->where('id', $versionId)
            ->firstOrFail();

        $documentService = app(DocumentService::class);
        $restoredVersion = $documentService->createVersion(
            $this->document,
            $version->body,
            auth()->user(),
            __('documents.restored_from_version', ['version' => $version->version_number]),
        );

        if ($restoredVersion) {
            $this->currentVersionId = $restoredVersion->id;
            $this->currentVersionNumber = $restoredVersion->version_number;
            $this->viewingVersionId = null;
            $this->showDiff = false;

            $this->dispatch('editor-load-content', ['body' => $restoredVersion->body]);
            $this->dispatch('editor-saved', [
                'version_id' => $restoredVersion->id,
                'version_number' => $restoredVersion->version_number,
            ]);

            $this->loadVersionList();
        }
    }

    // ─── Diff ─────────────────────────────────────────────────────────────────

    public function showDiffBetween(string $oldVersionId, string $newVersionId): void
    {
        $oldVersion = DocumentVersion::where('document_id', $this->document->id)
            ->where('id', $oldVersionId)
            ->firstOrFail();
        $newVersion = DocumentVersion::where('document_id', $this->document->id)
            ->where('id', $newVersionId)
            ->firstOrFail();

        $diffService = app(VersionDiffService::class);
        $result = $diffService->diff($oldVersion, $newVersion);

        $this->diffOldVersionId = $oldVersionId;
        $this->diffNewVersionId = $newVersionId;
        $this->diffBlocks = $result['blocks'];
        $this->diffStats = $result['stats'];
        $this->showDiff = true;
    }

    public function closeDiff(): void
    {
        $this->showDiff = false;
        $this->diffBlocks = [];
        $this->diffStats = [];
        $this->diffOldVersionId = null;
        $this->diffNewVersionId = null;
    }

    // ─── Sharing ───────────────────────────────────────────────────────────

    public function toggleShareModal(): void
    {
        $this->showShareModal = ! $this->showShareModal;
        $this->lastCreatedShareUrl = '';

        if ($this->showShareModal) {
            $this->loadShareList();
        }
    }

    public function loadShareList(): void
    {
        $this->shareList = $this->document->shares()
            ->with('createdBy')
            ->latest()
            ->get()
            ->map(fn (DocumentShare $s) => [
                'id' => $s->id,
                'url' => $s->getPublicUrl(),
                'recipient_email' => $s->recipient_email,
                'format' => $s->format,
                'download_count' => $s->download_count,
                'last_accessed_at' => $s->last_accessed_at?->format('d/m/Y H:i'),
                'expires_at' => $s->expires_at?->format('d/m/Y'),
                'is_active' => $s->isActive(),
                'created_by' => $s->createdBy?->name ?? __('common.unknown'),
                'created_at' => $s->created_at?->format('d/m/Y H:i'),
            ])
            ->toArray();
    }

    public function createShare(): void
    {
        Gate::authorize('update', $this->document);

        $share = DocumentShare::create([
            'workspace_id' => $this->document->workspace_id,
            'document_id' => $this->document->id,
            'version_id' => $this->currentVersionId,
            'token' => Str::random(64),
            'recipient_email' => $this->shareRecipientEmail ?: null,
            'format' => $this->shareFormat,
            'expires_at' => $this->shareExpiry ? now()->addDays((int) $this->shareExpiry) : null,
            'created_by_user_id' => auth()->id(),
        ]);

        $this->lastCreatedShareUrl = $share->getPublicUrl();
        $this->shareRecipientEmail = '';
        $this->shareExpiry = '';
        $this->loadShareList();
    }

    public function revokeShare(string $shareId): void
    {
        $share = DocumentShare::where('document_id', $this->document->id)
            ->where('id', $shareId)
            ->firstOrFail();

        Gate::authorize('delete', $share);

        $share->delete();
        $this->loadShareList();
    }

    // ─── AI Panel ──────────────────────────────────────────────────────────

    public function toggleAiPanel(): void
    {
        $this->showAiPanel = ! $this->showAiPanel;
    }

    public function aiDraft(string $intent, string $language = 'ar'): void
    {
        $this->aiLoading = true;
        $aiService = app(AiOrchestrationService::class);

        $interaction = $aiService->draft($this->document, $intent, $language, auth()->user());

        $this->handleAiResponse($interaction, 'draft');
    }

    public function aiReview(string $clauseId): void
    {
        $this->aiLoading = true;
        $clause = DocumentClause::where('id', $clauseId)->firstOrFail();
        $aiService = app(AiOrchestrationService::class);

        $interaction = $aiService->review($clause, auth()->user());

        $this->handleAiResponse($interaction, 'review');
    }

    public function aiSuggest(string $clauseId, string $instruction): void
    {
        $this->aiLoading = true;
        $clause = DocumentClause::where('id', $clauseId)->firstOrFail();
        $aiService = app(AiOrchestrationService::class);

        $interaction = $aiService->suggest($clause, $instruction, auth()->user());

        $this->handleAiResponse($interaction, 'suggest');
    }

    public function aiTranslate(string $clauseId, string $targetLanguage): void
    {
        $this->aiLoading = true;
        $clause = DocumentClause::where('id', $clauseId)->firstOrFail();
        $aiService = app(AiOrchestrationService::class);

        $interaction = $aiService->translate($clause, $targetLanguage, auth()->user());

        $this->handleAiResponse($interaction, 'translate');
    }

    public function aiExplain(string $clauseId): void
    {
        $this->aiLoading = true;
        $clause = DocumentClause::where('id', $clauseId)->firstOrFail();
        $aiService = app(AiOrchestrationService::class);

        $interaction = $aiService->explain($clause, auth()->user());

        $this->handleAiResponse($interaction, 'explain');
    }

    public function aiSendPrompt(): void
    {
        if (empty(trim($this->aiPrompt))) {
            return;
        }

        $this->aiDraft($this->aiPrompt, auth()->user()->preferred_locale ?? 'ar');
        $this->aiPrompt = '';
    }

    public function aiAccept(string $interactionId): void
    {
        $interaction = AiInteraction::findOrFail($interactionId);
        $aiService = app(AiOrchestrationService::class);
        $aiService->markAccepted($interaction, true);

        // Update the history entry
        $this->aiHistory = collect($this->aiHistory)->map(function ($entry) use ($interactionId) {
            if ($entry['id'] === $interactionId) {
                $entry['was_accepted'] = true;
            }

            return $entry;
        })->toArray();
    }

    public function aiReject(string $interactionId): void
    {
        $interaction = AiInteraction::findOrFail($interactionId);
        $aiService = app(AiOrchestrationService::class);
        $aiService->markAccepted($interaction, false);

        $this->aiHistory = collect($this->aiHistory)->map(function ($entry) use ($interactionId) {
            if ($entry['id'] === $interactionId) {
                $entry['was_accepted'] = false;
            }

            return $entry;
        })->toArray();
    }

    private function handleAiResponse(AiInteraction $interaction, string $type): void
    {
        $this->aiLoading = false;
        $this->aiLastResponse = $interaction->response;
        $this->aiLastInteractionId = $interaction->id;
        $this->aiLastType = $type;

        // Prepend to history
        array_unshift($this->aiHistory, [
            'id' => $interaction->id,
            'type' => $type,
            'response' => $interaction->response,
            'model' => $interaction->model,
            'tokens' => $interaction->input_tokens + $interaction->output_tokens,
            'latency_ms' => $interaction->latency_ms,
            'was_accepted' => null,
        ]);

        // Keep last 10 in history
        $this->aiHistory = array_slice($this->aiHistory, 0, 10);
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
