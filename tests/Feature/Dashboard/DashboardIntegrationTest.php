<?php

use App\Enums\MatterStatus;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Matter;
use App\Models\Obligation;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create(['name' => 'Khalid']);
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);
});

test('dashboard renders complete shell in English locale', function () {
    app()->setLocale('en');

    $response = $this->get('/dashboard');

    $response->assertStatus(200);
    // Top chrome
    $response->assertSee($this->workspace->name);
    // Hero
    $response->assertSee('Khalid');
    // Widget titles
    $response->assertSee(__('dashboard.widget_matters'));
    $response->assertSee(__('dashboard.widget_calendar'));
    $response->assertSee(__('dashboard.widget_documents'));
    $response->assertSee(__('dashboard.widget_tasks'));
    // Feed titles
    $response->assertSee(__('dashboard.widget_obligations'));
    $response->assertSee(__('dashboard.widget_renewals'));
    // Navigation
    $response->assertSee(__('shell.nav_matters'));
    $response->assertSee(__('shell.nav_contacts'));
    // Quick links
    $response->assertSee(__('shell.quick_links'));
});

test('dashboard renders complete shell in Arabic locale', function () {
    app()->setLocale('ar');

    $response = $this->get('/dashboard');

    $response->assertStatus(200);
    // RTL direction
    $response->assertSee('dir="rtl"', false);
    $response->assertSee('lang="ar"', false);
    // Arabic widget titles
    $response->assertSee('القضايا القانونية');
    $response->assertSee('المستندات');
    $response->assertSee('المهام');
    // Arabic nav
    $response->assertSee('القضايا');
    $response->assertSee('جهات الاتصال');
    // Arabic quick links
    $response->assertSee('روابط سريعة');
});

test('dashboard with populated data shows widget content', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);

    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
        'title' => 'Integration Test Matter',
        'status' => MatterStatus::Active,
    ]);

    $document = Document::factory()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'title' => 'Integration Test Document',
    ]);

    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'assigned_to_user_id' => $this->user->id,
        'title' => 'Integration Test Task',
    ]);

    Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $document->id,
        'title' => 'Integration Test Obligation',
        'due_date' => now()->addDays(5),
        'status' => 'pending',
    ]);

    $response = $this->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Integration Test Matter');
    $response->assertSee('Integration Test Document');
    $response->assertSee('Integration Test Task');
    $response->assertSee('Integration Test Obligation');
});

test('dashboard requires authentication', function () {
    auth()->logout();

    $this->get('/dashboard')
        ->assertRedirect('/login');
});

test('dashboard uses brand tokens in layout', function () {
    $response = $this->get('/dashboard');
    $html = $response->getContent();

    // Theme color
    expect($html)->toContain('#072E17');
    // Font preloads
    expect($html)->toContain('source-sans-pro');
    expect($html)->toContain('ibm-plex-sans-arabic');
    // Favicon
    expect($html)->toContain('efirm-favicon.svg');
    // Brand meta
    expect($html)->toContain('og:site_name');
});
