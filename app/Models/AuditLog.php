<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids;

    /**
     * Append-only: no updated_at column.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'changes',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * APPEND-ONLY enforcement: block update and delete operations.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('AuditLog records are append-only and cannot be updated.');
        });

        static::deleting(function () {
            throw new \RuntimeException('AuditLog records are append-only and cannot be deleted.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
