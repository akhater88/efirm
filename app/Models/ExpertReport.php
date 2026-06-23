<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\ExpertReportPosition;
use App\Enums\ExpertReportType;
use Database\Factories\ExpertReportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Expert Report entity — tracks court-appointed expert reports with 8-day objection deadline.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 (expert report entity) and #19 (day after receipt start).
 */
class ExpertReport extends Model
{
    /** @use HasFactory<ExpertReportFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $attributes = [
        'our_position' => 'not_yet_reviewed',
        'objection_filed' => false,
    ];

    protected $fillable = [
        'workspace_id',
        'matter_id',
        'expert_name_ar',
        'expert_name_en',
        'report_type',
        'received_date',
        'objection_deadline_date',
        'objection_filed',
        'objection_filed_date',
        'our_position',
        'summary_ar',
        'summary_en',
        'document_id',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'report_type' => ExpertReportType::class,
            'our_position' => ExpertReportPosition::class,
            'received_date' => 'date',
            'objection_deadline_date' => 'date',
            'objection_filed' => 'boolean',
            'objection_filed_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Auto-compute objection_deadline_date = received_date + 8 days (day after receipt per Decision #19)
        static::creating(function (self $report) {
            if ($report->received_date && ! $report->objection_deadline_date) {
                $report->objection_deadline_date = $report->received_date->copy()->addDays(8);
            }
        });
    }

    // --- Relationships ---

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
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
