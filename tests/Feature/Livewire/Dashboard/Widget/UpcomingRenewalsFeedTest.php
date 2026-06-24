<?php

use App\Enums\Role;
use App\Livewire\Dashboard\Widget\UpcomingRenewalsFeed;
use App\Models\Contact;
use App\Models\ContractMetadata;
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

test('renewals feed renders empty state', function () {
    Livewire::test(UpcomingRenewalsFeed::class)
        ->assertSee(__('dashboard.no_upcoming_renewals'));
});

test('renewals feed shows contracts expiring soon', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);
    $document = Document::factory()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'title' => 'Service Agreement',
    ]);

    ContractMetadata::create([
        'document_id' => $document->id,
        'workspace_id' => $this->workspace->id,
        'expiry_date' => now()->addDays(30),
    ]);

    Livewire::test(UpcomingRenewalsFeed::class)
        ->assertSee('Service Agreement');
});

test('renewals feed excludes expired contracts', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);
    $document = Document::factory()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'title' => 'Expired Contract',
    ]);

    ContractMetadata::create([
        'document_id' => $document->id,
        'workspace_id' => $this->workspace->id,
        'expiry_date' => now()->subDays(5),
    ]);

    Livewire::test(UpcomingRenewalsFeed::class)
        ->assertDontSee('Expired Contract');
});
