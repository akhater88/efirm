<?php

namespace App\Models;

use App\Enums\SubscriptionEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'subscription_id',
        'event_type',
        'from_state',
        'to_state',
        'payload',
        'triggered_by_admin_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => SubscriptionEventType::class,
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Subscription events are append-only and cannot be updated.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Subscription events are append-only and cannot be deleted.');
        });
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function triggeredByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'triggered_by_admin_id');
    }
}
