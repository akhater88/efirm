<?php

use App\Enums\Role;
use App\Enums\TaskStatus;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Obligation;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentService;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    Sanctum::actingAs($this->user);

    $this->client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
    ]);
});

it('creates a task attached to a matter', function () {
    $response = $this->postJson('/api/v1/tasks', [
        'title' => 'Review contract draft',
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
        'priority' => 'high',
        'due_date' => now()->addDays(3)->toDateString(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Review contract draft')
        ->assertJsonPath('data.taskable_type', 'matter');
});

it('creates a task attached to a contact', function () {
    $response = $this->postJson('/api/v1/tasks', [
        'title' => 'Follow up with client',
        'taskable_type' => 'contact',
        'taskable_id' => $this->client->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.taskable_type', 'contact');
});

it('creates a task attached to a document', function () {
    $body = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Test']]]]];
    $document = app(DocumentService::class)->createDocument($this->matter, 'Doc', $body, $this->user);

    $response = $this->postJson('/api/v1/tasks', [
        'title' => 'Finalize document',
        'taskable_type' => 'document',
        'taskable_id' => $document->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.taskable_type', 'document');
});

it('creates a task attached to an obligation', function () {
    $body = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Test']]]]];
    $document = app(DocumentService::class)->createDocument($this->matter, 'Doc', $body, $this->user);

    $obligation = Obligation::factory()->create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $document->id,
    ]);

    $response = $this->postJson('/api/v1/tasks', [
        'title' => 'Prepare payment',
        'taskable_type' => 'obligation',
        'taskable_id' => $obligation->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.taskable_type', 'obligation');
});

it('lists tasks', function () {
    Task::factory()->count(3)->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
    ]);

    $response = $this->getJson('/api/v1/tasks');

    $response->assertOk()
        ->assertJsonCount(3, 'data.data');
});

it('completes a task', function () {
    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
    ]);

    $response = $this->postJson("/api/v1/tasks/{$task->id}/complete");

    $response->assertOk()
        ->assertJsonPath('data.status', 'done');

    $task->refresh();
    expect($task->status)->toBe(TaskStatus::Done)
        ->and($task->completed_at)->not->toBeNull()
        ->and($task->completed_by_user_id)->toBe($this->user->id);
});

it('polymorphic resolution works via morphTo', function () {
    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
    ]);

    expect($task->taskable)->toBeInstanceOf(Matter::class)
        ->and($task->taskable->id)->toBe($this->matter->id);
});

it('morphMany works from parent entity', function () {
    Task::factory()->count(2)->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
    ]);

    expect($this->matter->tasks)->toHaveCount(2);
});

it('workspace isolation prevents cross-workspace access', function () {
    $otherWorkspace = Workspace::factory()->create();
    $otherMatter = Matter::factory()->create(['workspace_id' => $otherWorkspace->id]);

    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
    ]);
    Task::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $otherMatter->id,
    ]);

    $response = $this->getJson('/api/v1/tasks');

    $response->assertOk()
        ->assertJsonCount(1, 'data.data');
});

it('deletes a task (soft delete)', function () {
    $task = Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'taskable_type' => 'matter',
        'taskable_id' => $this->matter->id,
    ]);

    $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

    $response->assertNoContent();
    expect(Task::find($task->id))->toBeNull();
    expect(Task::withTrashed()->find($task->id))->not->toBeNull();
});
