<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\TrustAccountFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrustAccount extends Model
{
    /** @use HasFactory<TrustAccountFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'contact_id',
        'matter_id',
        'name',
        'bank_name',
        'bank_account_number',
        'currency',
        'balance',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(TrustLedgerEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
