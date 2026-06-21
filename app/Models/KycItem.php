<?php

namespace App\Models;

use App\Enums\KycItemStatus;
use App\Enums\KycItemType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycItem extends Model
{
    use HasUlids;

    protected $fillable = [
        'kyc_checklist_id',
        'item_type',
        'status',
        'expiry_date',
        'document_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'item_type' => KycItemType::class,
            'status' => KycItemStatus::class,
            'expiry_date' => 'date',
        ];
    }

    // --- Relationships ---

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(KycChecklist::class, 'kyc_checklist_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
