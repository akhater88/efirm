<?php

namespace App\Models;

use App\Enums\AdminActivityEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AdminActivityLog extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $table = 'admin_activity_log';

    /** @var list<string> */
    protected $fillable = [
        'admin_user_id',
        'attempted_email',
        'event_type',
        'target_type',
        'target_id',
        'payload',
        'ip_address',
        'user_agent',
        'occurred_at',
        'created_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'event_type' => AdminActivityEventType::class,
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Admin activity log entries are append-only and cannot be updated.');
        });

        static::deleting(function (): never {
            throw new LogicException('Admin activity log entries are append-only and cannot be deleted.');
        });
    }

    // --- Relationships ---

    /** @return BelongsTo<AdminUser, $this> */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }
}
