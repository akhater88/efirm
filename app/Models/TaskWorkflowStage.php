<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskWorkflowStage extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'task_workflow_id',
        'name_ar',
        'name_en',
        'key',
        'sort_order',
        'is_initial',
        'is_terminal',
        'color',
        'requires_approval',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_initial' => 'boolean',
            'is_terminal' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    // --- Relationships ---

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(TaskWorkflow::class, 'task_workflow_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'current_stage_id');
    }

    public function transitionsFrom(): HasMany
    {
        return $this->hasMany(TaskWorkflowTransition::class, 'from_stage_id');
    }

    public function transitionsTo(): HasMany
    {
        return $this->hasMany(TaskWorkflowTransition::class, 'to_stage_id');
    }

    // --- Helpers ---

    /**
     * Return the localized name based on the current app locale.
     */
    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }
}
