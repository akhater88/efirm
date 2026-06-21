<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\ObligationStatus;
use App\Enums\ObligationType;
use App\Enums\ResponsibleParty;
use Database\Factories\ObligationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Obligation extends Model
{
    /** @use HasFactory<ObligationFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'document_id',
        'clause_id',
        'title',
        'description',
        'obligation_type',
        'responsible_party',
        'responsible_user_id',
        'due_date',
        'reminder_days_before',
        'status',
        'completed_at',
        'completed_by_id',
        'monetary_amount',
        'monetary_currency',
        'notes',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'obligation_type' => ObligationType::class,
            'responsible_party' => ResponsibleParty::class,
            'status' => ObligationStatus::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'monetary_amount' => 'decimal:2',
            'reminder_days_before' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // --- Helpers ---

    public function markComplete(User $actor): void
    {
        $this->update([
            'status' => ObligationStatus::Completed,
            'completed_at' => now(),
            'completed_by_id' => $actor->id,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast()
            && ! in_array($this->status, [ObligationStatus::Completed, ObligationStatus::Waived]);
    }

    public function reminderDate(): ?Carbon
    {
        return $this->due_date->subDays($this->reminder_days_before);
    }

    // --- Scopes ---

    public function scopeUpcoming($query, int $days = 14)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
            ->where('due_date', '>=', now())
            ->whereNotIn('status', [ObligationStatus::Completed, ObligationStatus::Waived]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [ObligationStatus::Completed, ObligationStatus::Waived]);
    }
}
