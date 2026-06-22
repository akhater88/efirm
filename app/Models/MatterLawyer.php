<?php

namespace App\Models;

use App\Enums\MatterLawyerRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatterLawyer extends Model
{
    protected $table = 'matter_lawyers';

    protected $fillable = [
        'matter_id',
        'user_id',
        'role',
        'assigned_at',
        'assigned_by_user_id',
        'unassigned_at',
        'unassigned_by_user_id',
        'notes',
        'backfilled_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => MatterLawyerRole::class,
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
            'backfilled_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function unassignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unassigned_by_user_id');
    }

    // --- Scopes ---

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('unassigned_at');
    }

    public function scopeLead(Builder $query): Builder
    {
        return $query->where('role', MatterLawyerRole::Lead)->whereNull('unassigned_at');
    }
}
