<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class WorkspaceSwitcher extends Component
{
    public bool $showCreateModal = false;

    public string $newWorkspaceName = '';

    public string $newWorkspaceLocale = 'ar';

    protected function rules(): array
    {
        return [
            'newWorkspaceName' => 'required|string|max:255',
            'newWorkspaceLocale' => 'required|string|in:ar,en',
        ];
    }

    public function switchTo(string $workspaceId): void
    {
        $workspace = Workspace::withoutGlobalScopes()->findOrFail($workspaceId);

        if (! auth()->user()->belongsToWorkspace($workspace)) {
            return;
        }

        auth()->user()->switchWorkspace($workspace);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function createWorkspace(): void
    {
        $this->validate();

        DB::transaction(function () {
            $workspace = Workspace::create([
                'name' => $this->newWorkspaceName,
                'default_locale' => $this->newWorkspaceLocale,
                'created_by_user_id' => auth()->id(),
                'updated_by_user_id' => auth()->id(),
            ]);

            WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id' => auth()->id(),
                'role' => Role::Owner,
                'joined_at' => now(),
                'created_by_user_id' => auth()->id(),
            ]);

            auth()->user()->switchWorkspace($workspace);
        });

        $this->reset(['showCreateModal', 'newWorkspaceName', 'newWorkspaceLocale']);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.workspace-switcher', [
            'workspaces' => auth()->user()->workspaces()->get(),
            'currentWorkspace' => auth()->user()->currentWorkspace(),
        ]);
    }
}
