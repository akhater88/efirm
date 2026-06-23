<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\HearingActionItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HearingActionItem extends Model
{
    /** @use HasFactory<HearingActionItemFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'hearing_id',
        'description_ar',
        'description_en',
        'due_date',
        'responsible_user_id',
        'status',
        'obligation_id',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function hearing(): BelongsTo
    {
        return $this->belongsTo(Hearing::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function obligation(): BelongsTo
    {
        return $this->belongsTo(Obligation::class);
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
