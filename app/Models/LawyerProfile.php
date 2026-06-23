<?php

namespace App\Models;

use App\Enums\LawyerProfileStatus;
use Database\Factories\LawyerProfileFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LawyerProfile extends Model
{
    /** @use HasFactory<LawyerProfileFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'bar_admission_number',
        'bar_admission_country',
        'bar_admission_date',
        'jurisdictions',
        'practice_areas',
        'languages_spoken',
        'default_hourly_rate',
        'default_currency',
        'position_title_ar',
        'position_title_en',
        'bio_ar',
        'bio_en',
        'status',
        'joined_firm_date',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'jurisdictions' => 'array',
            'practice_areas' => 'array',
            'languages_spoken' => 'array',
            'default_hourly_rate' => 'decimal:2',
            'bar_admission_date' => 'date',
            'joined_firm_date' => 'date',
            'status' => LawyerProfileStatus::class,
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function localizedPositionTitle(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'ar'
            ? ($this->position_title_ar ?? $this->position_title_en)
            : ($this->position_title_en ?? $this->position_title_ar);
    }
}
