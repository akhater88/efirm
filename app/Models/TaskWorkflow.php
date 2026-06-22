<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskWorkflow extends Model
{
    use BelongsToWorkspace, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'is_default',
        'applies_to_task_type',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function stages(): HasMany
    {
        return $this->hasMany(TaskWorkflowStage::class)->orderBy('sort_order');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(TaskWorkflowTransition::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_workflow_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    // --- Helpers ---

    public function initialStage(): ?TaskWorkflowStage
    {
        return $this->stages()->where('is_initial', true)->first();
    }
}
