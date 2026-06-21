<?php

use App\Enums\Role;
use App\Models\SmartList;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
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
});

it('creates a smart list', function () {
    $response = $this->postJson('/api/v1/smart-lists', [
        'entity_type' => 'matter',
        'name' => 'Active Commercial Matters',
        'filters' => ['status' => ['active'], 'practice_area' => ['commercial_contracts']],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Active Commercial Matters')
        ->assertJsonPath('data.entity_type', 'matter');
});

it('lists smart lists visible to user (own + shared)', function () {
    SmartList::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'entity_type' => 'matter',
        'name' => 'My List',
        'filters' => ['status' => ['active']],
    ]);

    $otherUser = User::factory()->create();
    SmartList::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $otherUser->id,
        'entity_type' => 'matter',
        'name' => 'Shared List',
        'filters' => ['status' => ['closed']],
        'is_shared_to_workspace' => true,
    ]);

    SmartList::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $otherUser->id,
        'entity_type' => 'matter',
        'name' => 'Private Other List',
        'filters' => ['status' => ['on_hold']],
        'is_shared_to_workspace' => false,
    ]);

    $response = $this->getJson('/api/v1/smart-lists');

    $response->assertOk()
        ->assertJsonCount(2, 'data'); // own + shared, not private-other
});

it('preserves filter state in JSON', function () {
    $filters = [
        'status' => ['active', 'on_hold'],
        'practice_area' => ['commercial_contracts'],
        'created_after' => '2026-01-01',
    ];

    $this->postJson('/api/v1/smart-lists', [
        'entity_type' => 'matter',
        'name' => 'Complex Filter',
        'filters' => $filters,
    ]);

    $smartList = SmartList::first();
    expect($smartList->filters)->toBe($filters);
});

it('toggles pin status', function () {
    $smartList = SmartList::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'entity_type' => 'matter',
        'name' => 'Test',
        'filters' => [],
        'is_pinned' => false,
    ]);

    $response = $this->putJson("/api/v1/smart-lists/{$smartList->id}", [
        'is_pinned' => true,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.is_pinned', true);
});

it('deletes a smart list', function () {
    $smartList = SmartList::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'entity_type' => 'matter',
        'name' => 'To Delete',
        'filters' => [],
    ]);

    $response = $this->deleteJson("/api/v1/smart-lists/{$smartList->id}");

    $response->assertNoContent();
});
