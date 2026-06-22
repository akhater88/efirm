<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskWorkflowApproval extends Model
{
    use BelongsToWorkspace, HasUlids;

    protected $fillable = [
        'workspace_id',
        'task_id',
        'from_stage_id',
        'to_stage_id',
        'requested_by_user_id',
        'approver_user_id',
        'status',
        'responded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(TaskWorkflowStage::class, 'from_stage_id');
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(TaskWorkflowStage::class, 'to_stage_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
