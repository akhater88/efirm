<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskWorkflowTransition extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'task_workflow_id',
        'from_stage_id',
        'to_stage_id',
        'requires_role',
        'requires_approval_by_user_id',
        'auto_transition_after_hours',
    ];

    protected function casts(): array
    {
        return [
            'auto_transition_after_hours' => 'integer',
        ];
    }

    // --- Relationships ---

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(TaskWorkflow::class, 'task_workflow_id');
    }

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(TaskWorkflowStage::class, 'from_stage_id');
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(TaskWorkflowStage::class, 'to_stage_id');
    }

    public function requiredApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requires_approval_by_user_id');
    }
}
