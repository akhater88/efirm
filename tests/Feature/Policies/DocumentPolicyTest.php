<?php

use App\Enums\Role;
use App\Models\Document;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();

    $this->owner = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->owner->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);

    $this->admin = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->admin->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Admin,
    ]);

    $this->member = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->member->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Member,
    ]);

    $this->document = Document::factory()->create(['workspace_id' => $this->workspace->id]);
});

it('allows any member to view documents', function () {
    $this->actingAs($this->member);
    $this->member->switchWorkspace($this->workspace);

    expect($this->member->can('viewAny', Document::class))->toBeTrue()
        ->and($this->member->can('view', $this->document))->toBeTrue();
});

it('allows any member to create documents', function () {
    $this->actingAs($this->member);
    $this->member->switchWorkspace($this->workspace);

    expect($this->member->can('create', Document::class))->toBeTrue();
});

it('allows same-workspace member to update', function () {
    $this->actingAs($this->member);
    $this->member->switchWorkspace($this->workspace);

    expect($this->member->can('update', $this->document))->toBeTrue();
});

it('denies cross-workspace update', function () {
    $otherWorkspace = Workspace::factory()->create();
    $otherUser = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $otherUser->id,
        'workspace_id' => $otherWorkspace->id,
        'role' => Role::Admin,
    ]);

    $this->actingAs($otherUser);
    $otherUser->switchWorkspace($otherWorkspace);

    expect($otherUser->can('update', $this->document))->toBeFalse();
});

it('allows owner and admin to delete', function () {
    $this->actingAs($this->owner);
    $this->owner->switchWorkspace($this->workspace);
    expect($this->owner->can('delete', $this->document))->toBeTrue();

    $this->actingAs($this->admin);
    $this->admin->switchWorkspace($this->workspace);
    expect($this->admin->can('delete', $this->document))->toBeTrue();
});

it('denies member from deleting', function () {
    $this->actingAs($this->member);
    $this->member->switchWorkspace($this->workspace);

    expect($this->member->can('delete', $this->document))->toBeFalse();
});
