<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\TimeEntryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeEntry extends Model
{
    /** @use HasFactory<TimeEntryFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'matter_id',
        'document_id',
        'task_id',
        'description',
        'duration_minutes',
        'started_at',
        'ended_at',
        'is_billable',
        'billing_rate_per_hour',
        'currency',
        'started_via_context',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_billable' => 'boolean',
            'billing_rate_per_hour' => 'decimal:2',
            'duration_minutes' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // --- Helpers ---

    public function billableAmount(): ?string
    {
        if (! $this->is_billable || ! $this->billing_rate_per_hour) {
            return null;
        }

        return bcmul(
            bcdiv((string) $this->duration_minutes, '60', 4),
            (string) $this->billing_rate_per_hour,
            2,
        );
    }

    // --- Scopes ---

    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeInPeriod($query, $start, $end)
    {
        return $query->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end);
    }
}
