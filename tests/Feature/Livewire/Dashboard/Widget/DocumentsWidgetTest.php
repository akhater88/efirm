<?php

use App\Enums\Role;
use App\Livewire\Dashboard\Widget\DocumentsWidget;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Livewire\Livewire;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);
});

test('documents widget renders empty state when no documents', function () {
    Livewire::test(DocumentsWidget::class)
        ->assertSee(__('dashboard.no_recent_documents'));
});

test('documents widget shows recent documents with matter name', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'title' => 'Lease Agreement Matter',
    ]);
    Document::factory()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'title' => 'Draft Lease v2',
    ]);

    Livewire::test(DocumentsWidget::class)
        ->assertSee('Draft Lease v2')
        ->assertSee('Lease Agreement Matter');
});

test('documents widget does not show cross-workspace documents', function () {
    $otherWorkspace = Workspace::factory()->create();
    $client = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $otherWorkspace->id, 'client_id' => $client->id]);
    Document::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'matter_id' => $matter->id,
        'title' => 'Secret Document',
    ]);

    Livewire::test(DocumentsWidget::class)
        ->assertDontSee('Secret Document');
});
