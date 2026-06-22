<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\DecisionOutcome;
use App\Enums\DecisionType;
use Database\Factories\CourtReviewFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourtReview extends Model
{
    /** @use HasFactory<CourtReviewFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'matter_id',
        'hearing_id',
        'decision_date',
        'decision_type',
        'outcome',
        'summary_ar',
        'summary_en',
        'decision_document_id',
        'appealable',
        'appeal_deadline_date',
        'appeal_filed',
        'next_steps',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'decision_type' => DecisionType::class,
            'outcome' => DecisionOutcome::class,
            'decision_date' => 'date',
            'appealable' => 'boolean',
            'appeal_deadline_date' => 'date',
            'appeal_filed' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function hearing(): BelongsTo
    {
        return $this->belongsTo(Hearing::class);
    }

    public function decisionDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'decision_document_id');
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
