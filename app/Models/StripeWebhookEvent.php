<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stripe_event_id',
        'event_type',
        'signature_valid',
        'processing_result',
        'payload',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Stripe webhook events are append-only and cannot be updated.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Stripe webhook events are append-only and cannot be deleted.');
        });
    }
}
