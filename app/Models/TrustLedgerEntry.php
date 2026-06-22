<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\TrustLedgerEntryType;
use Database\Factories\TrustLedgerEntryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustLedgerEntry extends Model
{
    /** @use HasFactory<TrustLedgerEntryFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids;

    /**
     * APPEND-ONLY: no updated_at column.
     */
    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'trust_account_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'reference',
        'created_by_user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TrustLedgerEntryType::class,
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    /**
     * APPEND-ONLY enforcement: block update and delete at the model level.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException(__('financial.trust_ledger_entry_immutable'));
        });

        static::deleting(function () {
            throw new \LogicException(__('financial.trust_ledger_entry_immutable'));
        });
    }

    // --- Relationships ---

    public function trustAccount(): BelongsTo
    {
        return $this->belongsTo(TrustAccount::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
