<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\TaskTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskType extends Model
{
    /** @use HasFactory<TaskTypeFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'name_en',
        'name_ar',
        'slug',
        'icon',
        'color',
        'default_workflow_id',
        'custom_fields',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function defaultWorkflow(): BelongsTo
    {
        return $this->belongsTo(TaskWorkflow::class, 'default_workflow_id');
    }

    // --- Helpers ---

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    // --- Scopes ---

    /**
     * @param  Builder<TaskType>  $query
     * @return Builder<TaskType>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
