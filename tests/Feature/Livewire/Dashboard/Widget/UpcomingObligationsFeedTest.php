<?php

use App\Enums\Role;
use App\Livewire\Dashboard\Widget\UpcomingObligationsFeed;
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

test('obligations feed renders empty state', function () {
    Livewire::test(UpcomingObligationsFeed::class)
        ->assertSee(__('dashboard.no_upcoming_obligations'));
});

test('obligations feed shows upcoming obligations', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id, 'matter_id' => $matter->id]);

    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $document->id,
        'title' => 'Deliver signed copy',
        'due_date' => now()->addDays(5),
        'status' => 'pending',
    ]);

    Livewire::test(UpcomingObligationsFeed::class)
        ->assertSee('Deliver signed copy');
});

test('obligations feed filters by search', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id, 'matter_id' => $matter->id]);

    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $document->id,
        'title' => 'Payment milestone',
        'due_date' => now()->addDays(3),
        'status' => 'pending',
    ]);
    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $document->id,
        'title' => 'Final delivery',
        'due_date' => now()->addDays(7),
        'status' => 'pending',
    ]);

    Livewire::test(UpcomingObligationsFeed::class)
        ->set('search', 'Payment')
        ->assertSee('Payment milestone')
        ->assertDontSee('Final delivery');
});

test('obligations feed respects days ahead filter', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);
    $document = Document::factory()->create(['workspace_id' => $this->workspace->id, 'matter_id' => $matter->id]);

    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $document->id,
        'title' => 'Soon obligation',
        'due_date' => now()->addDays(5),
        'status' => 'pending',
    ]);
    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $document->id,
        'title' => 'Far obligation',
        'due_date' => now()->addDays(20),
        'status' => 'pending',
    ]);

    Livewire::test(UpcomingObligationsFeed::class)
        ->set('daysAhead', 7)
        ->assertSee('Soon obligation')
        ->assertDontSee('Far obligation');
});
