<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\KycChecklistStatus;
use App\Enums\KycItemStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KycChecklist extends Model
{
    use BelongsToWorkspace, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'contact_id',
        'status',
        'started_at',
        'completed_at',
        'next_review_date',
        'notes',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => KycChecklistStatus::class,
            'started_at' => 'date',
            'completed_at' => 'date',
            'next_review_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(KycItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // --- Helpers ---

    public function recalculateStatus(): void
    {
        $items = $this->items;

        if ($items->isEmpty()) {
            return;
        }

        $allVerified = $items->every(fn (KycItem $item) => $item->status === KycItemStatus::Verified);
        $anyRejected = $items->contains(fn (KycItem $item) => $item->status === KycItemStatus::Rejected);
        $anyExpired = $items->contains(fn (KycItem $item) => $item->status === KycItemStatus::Expired);

        if ($allVerified) {
            $this->update([
                'status' => KycChecklistStatus::Complete,
                'completed_at' => now(),
            ]);
        } elseif ($anyRejected) {
            $this->update(['status' => KycChecklistStatus::Blocked]);
        } elseif ($anyExpired) {
            $this->update(['status' => KycChecklistStatus::Expired]);
        } else {
            $this->update(['status' => KycChecklistStatus::InProgress]);
        }
    }
}
