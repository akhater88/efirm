<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\JournalEntryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    /** @use HasFactory<JournalEntryFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'entry_number',
        'entry_date',
        'description',
        'reference',
        'is_posted',
        'posted_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (JournalEntry $entry) {
            if ($entry->is_posted) {
                throw new \LogicException(__('financial.journal_entry_already_posted'));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'is_posted' => 'boolean',
            'posted_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
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
