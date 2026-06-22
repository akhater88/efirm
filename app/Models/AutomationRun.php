<?php

namespace App\Models;

use Database\Factories\AutomationRunFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * APPEND-ONLY model — update and delete are blocked in boot().
 * No updated_at, no deleted_at.
 */
class AutomationRun extends Model
{
    /** @use HasFactory<AutomationRunFactory> */
    use HasFactory, HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'automation_id',
        'trigger_payload',
        'conditions_evaluation',
        'actions_executed',
        'status',
        'error_message',
        'duration_ms',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'trigger_payload' => 'array',
            'conditions_evaluation' => 'array',
            'actions_executed' => 'array',
            'duration_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('AutomationRun is append-only. Updates are not allowed.');
        });

        static::deleting(function () {
            throw new \RuntimeException('AutomationRun is append-only. Deletes are not allowed.');
        });
    }

    // --- Relationships ---

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
