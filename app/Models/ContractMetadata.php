<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class ContractMetadata extends Model
{
    use BelongsToWorkspace, HasUlids, SoftDeletes;

    protected $table = 'contract_metadata';

    protected $fillable = [
        'workspace_id',
        'document_id',
        'contract_value',
        'contract_currency',
        'effective_date',
        'term_months',
        'expiry_date',
        'auto_renew',
        'renewal_notice_period_days',
        'governing_law',
        'jurisdiction_clause',
        'signed_date',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'contract_value' => 'decimal:2',
            'effective_date' => 'date',
            'expiry_date' => 'date',
            'signed_date' => 'date',
            'auto_renew' => 'boolean',
            'term_months' => 'integer',
            'renewal_notice_period_days' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            // Auto-compute expiry_date from effective_date + term_months
            if ($model->effective_date && $model->term_months && ! $model->isDirty('expiry_date')) {
                $model->expiry_date = $model->effective_date->addMonths($model->term_months);
            }
        });
    }

    // --- Relationships ---

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    // --- Helpers ---

    public function isExpiringSoon(int $withinDays = 60): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBetween(now(), now()->addDays($withinDays));
    }

    public function renewalReminderDate(): ?Carbon
    {
        if (! $this->expiry_date || ! $this->renewal_notice_period_days) {
            return null;
        }

        return $this->expiry_date->subDays($this->renewal_notice_period_days);
    }
}
