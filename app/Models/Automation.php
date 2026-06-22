<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\AutomationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Automation extends Model
{
    /** @use HasFactory<AutomationFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'name_ar',
        'name_en',
        'description',
        'trigger_event',
        'conditions',
        'is_active',
        'run_count',
        'last_run_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_active' => 'boolean',
            'run_count' => 'integer',
            'last_run_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function actions(): HasMany
    {
        return $this->hasMany(AutomationAction::class)->orderBy('sort_order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRun::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
