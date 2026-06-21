<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\ClauseLanguage;
use App\Enums\PracticeArea;
use App\Enums\RiskPosition;
use Database\Factories\LibraryClauseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryClause extends Model
{
    /** @use HasFactory<LibraryClauseFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'title',
        'clause_type',
        'practice_area',
        'language',
        'body_ar',
        'body_en',
        'risk_position',
        'is_fallback_of_id',
        'tags',
        'source_document_id',
        'usage_count',
        'last_used_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'language' => ClauseLanguage::class,
            'practice_area' => PracticeArea::class,
            'risk_position' => RiskPosition::class,
            'body_ar' => 'array',
            'body_en' => 'array',
            'tags' => 'array',
            'usage_count' => 'integer',
            'last_used_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function fallbackOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'is_fallback_of_id');
    }

    public function fallbacks(): HasMany
    {
        return $this->hasMany(self::class, 'is_fallback_of_id');
    }

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'source_document_id');
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

    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}
