<?php

use App\Enums\Role;
use App\Livewire\Dashboard\Widget\TasksWidget;
use App\Models\Task;
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

test('tasks widget renders empty state when no tasks', function () {
    Livewire::test(TasksWidget::class)
        ->assertSee(__('dashboard.no_recent_tasks'));
});

test('tasks widget shows tasks assigned to current user', function () {
    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'assigned_to_user_id' => $this->user->id,
        'title' => 'Review contract clause 4',
        'priority' => 'high',
    ]);

    Livewire::test(TasksWidget::class)
        ->assertSee('Review contract clause 4');
});

test('tasks widget does not show tasks assigned to others', function () {
    $other = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $other->id,
        'workspace_id' => $this->workspace->id,
    ]);

    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'assigned_to_user_id' => $other->id,
        'title' => 'Other User Task',
    ]);

    Livewire::test(TasksWidget::class)
        ->assertDontSee('Other User Task');
});

test('tasks widget shows footer links', function () {
    Task::factory()->create([
        'workspace_id' => $this->workspace->id,
        'assigned_to_user_id' => $this->user->id,
        'title' => 'Some task',
    ]);

    Livewire::test(TasksWidget::class)
        ->assertSee(__('common.view_all'))
        ->assertSee(__('shell.new_task'));
});
