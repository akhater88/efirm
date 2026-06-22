<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\JudgeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Judge extends Model
{
    /** @use HasFactory<JudgeFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'court_id',
        'name_ar',
        'name_en',
        'title',
        'notes',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(Hearing::class);
    }

    public function matters(): HasMany
    {
        return $this->hasMany(Matter::class);
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

    public function localizedName(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            return $this->name_ar;
        }

        return $this->name_en ?? $this->name_ar;
    }
}
