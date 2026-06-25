<?php

namespace App\Livewire\Pages;

use App\Enums\MatterLawyerRole;
use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Services\MatterLawyerService;
use Livewire\Component;

class MatterDetail extends Component
{
    public Matter $matter;

    public string $activeTab = 'overview';

    // Overview editing
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

    // Team management
    public bool $showAddMemberModal = false;

    public ?string $addMemberUserId = null;

    public string $addMemberRole = 'supporting';

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

    // --- Overview editing ---

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

    // --- Team management (lead lawyer or workspace owner/admin) ---

    public function canManageTeam(): bool
    {
        $user = auth()->user();

        // Workspace Owner or Admin can always manage
        $role = $user->currentRole();
        if ($role && in_array($role->value, ['owner', 'admin'])) {
            return true;
        }

        // Lead lawyer on this matter can manage
        return $this->matter->matterLawyers
            ->where('role', MatterLawyerRole::Lead)
            ->where('user_id', $user->id)
            ->isNotEmpty();
    }

    public function openAddMember(): void
    {
        if (! $this->canManageTeam()) {
            return;
        }

        $this->addMemberUserId = null;
        $this->addMemberRole = 'supporting';
        $this->showAddMemberModal = true;
    }

    public function addMember(): void
    {
        if (! $this->canManageTeam()) {
            return;
        }

        $this->validate([
            'addMemberUserId' => 'required|exists:users,id',
            'addMemberRole' => 'required|in:lead,supporting',
        ]);

        $user = User::findOrFail($this->addMemberUserId);
        $role = MatterLawyerRole::from($this->addMemberRole);

        app(MatterLawyerService::class)->assignLawyer(
            $this->matter,
            $user,
            $role,
            auth()->user()
        );

        $this->matter->load('matterLawyers.user');
        $this->showAddMemberModal = false;
    }

    public function removeMember(string $userId): void
    {
        if (! $this->canManageTeam()) {
            return;
        }

        $user = User::findOrFail($userId);

        app(MatterLawyerService::class)->unassignLawyer(
            $this->matter,
            $user,
            auth()->user()
        );

        $this->matter->load('matterLawyers.user');
    }

    public function promoteLead(string $userId): void
    {
        if (! $this->canManageTeam()) {
            return;
        }

        $user = User::findOrFail($userId);

        app(MatterLawyerService::class)->changeLeadLawyer(
            $this->matter,
            $user,
            auth()->user()
        );

        $this->matter->load('matterLawyers.user');
    }

    public function render()
    {
        $clients = $this->editing
            ? Contact::where('is_client', true)->get(['id', 'display_name'])
            : collect();

        $workspace = auth()->user()->currentWorkspace();
        $workspaceMembers = ($this->showAddMemberModal && $workspace)
            ? User::whereHas('workspaces', fn ($q) => $q->where('workspaces.id', $workspace->id))
                ->whereNotIn('id', $this->matter->matterLawyers->pluck('user_id')->toArray())
                ->get(['id', 'name', 'email'])
            : collect();

        return view('livewire.pages.matter-detail', [
            'clients' => $clients,
            'statuses' => MatterStatus::cases(),
            'practiceAreas' => PracticeArea::cases(),
            'workspaceMembers' => $workspaceMembers,
            'isLead' => $this->canManageTeam(),
        ])->layout('components.layouts.dashboard');
    }
}
