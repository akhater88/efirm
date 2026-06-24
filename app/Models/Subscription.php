<?php

namespace App\Models;

use App\Enums\SubscriptionState;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'plan_id',
        'state',
        'seat_count',
        'stripe_customer_id',
        'stripe_subscription_id',
        'trial_ends_at',
        'grace_period_ends_at',
        'cancelled_at',
        'data_retention_expires_at',
        'data_purged',
        'data_purged_at',
    ];

    protected function casts(): array
    {
        return [
            'state' => SubscriptionState::class,
            'seat_count' => 'integer',
            'trial_ends_at' => 'datetime',
            'grace_period_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'data_retention_expires_at' => 'datetime',
            'data_purged' => 'boolean',
            'data_purged_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(SubscriptionEvent::class);
    }

    public function isActive(): bool
    {
        return $this->state === SubscriptionState::Active;
    }

    public function isTrial(): bool
    {
        return $this->state === SubscriptionState::Trial;
    }

    public function isSuspended(): bool
    {
        return $this->state === SubscriptionState::Suspended;
    }

    public function isCancelled(): bool
    {
        return $this->state === SubscriptionState::Cancelled;
    }

    public function trialExpired(): bool
    {
        return $this->isTrial() && $this->trial_ends_at?->isPast();
    }
}
