<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminImpersonationSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_user_id',
        'impersonated_user_id',
        'workspace_id',
        'purpose',
        'ip_address',
        'started_at',
        'ended_at',
        'termination_reason',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            // Allow updating ended_at and termination_reason only (for session end)
        });

        static::deleting(function () {
            throw new \RuntimeException('Admin impersonation sessions are append-only and cannot be deleted.');
        });
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function impersonatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonated_user_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }
}
