<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'title',
        'description',
        'taskable_type',
        'taskable_id',
        'assigned_to_user_id',
        'due_date',
        'priority',
        'status',
        'completed_at',
        'completed_by_user_id',
        'tags',
        'task_workflow_id',
        'current_stage_id',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'tags' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(TaskWorkflow::class, 'task_workflow_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(TaskWorkflowStage::class, 'current_stage_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TaskWorkflowApproval::class);
    }

    // --- Helpers ---

    public function markComplete(User $actor): void
    {
        $this->update([
            'status' => TaskStatus::Done,
            'completed_at' => now(),
            'completed_by_user_id' => $actor->id,
        ]);
    }

    // --- Scopes ---

    public function scopeUpcoming($query, int $days = 14)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays($days))
            ->where('due_date', '>=', now())
            ->whereNotIn('status', [TaskStatus::Done, TaskStatus::Cancelled]);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', [TaskStatus::Done, TaskStatus::Cancelled]);
    }

    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assigned_to_user_id', $user->id);
    }
}
