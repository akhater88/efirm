<?php

use App\Enums\Role;
use App\Livewire\Dashboard\Widget\CalendarWidget;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Matter;
use App\Models\Obligation;
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

test('calendar widget renders empty state when no events', function () {
    Livewire::test(CalendarWidget::class)
        ->assertSee(__('dashboard.no_upcoming_events'));
});

test('calendar widget shows upcoming obligations', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id, 'matter_id' => $matter->id]);

    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $document->id,
        'title' => 'Payment Due',
        'due_date' => now()->addDays(5),
        'status' => 'pending',
    ]);

    Livewire::test(CalendarWidget::class)
        ->assertSee('Payment Due');
});

test('calendar widget excludes past obligations', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id, 'matter_id' => $matter->id]);

    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $document->id,
        'title' => 'Past Obligation',
        'due_date' => now()->subDays(2),
        'status' => 'pending',
    ]);

    Livewire::test(CalendarWidget::class)
        ->assertDontSee('Past Obligation');
});
