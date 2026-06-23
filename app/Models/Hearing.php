<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\HearingStatus;
use App\Enums\HearingType;
use Database\Factories\HearingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Hearing extends Model
{
    /** @use HasFactory<HearingFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'matter_id',
        'hearing_date',
        'court_id',
        'judge_id',
        'hearing_type',
        'status',
        'held_at',
        'outcome',
        'judge_statement_ar',
        'judge_statement_en',
        'outcome_summary_ar',
        'outcome_summary_en',
        'our_submissions_made',
        'opposing_submissions_made',
        'next_session_required_actions_ar',
        'next_session_required_actions_en',
        'session_attended_by',
        'next_action_required',
        'postponed_to_hearing_id',
        'postponement_reason_ar',
        'postponement_reason_en',
        'postponement_initiated_by',
        'our_attendee_user_id',
        'assigned_lawyer_user_id',
        'lawyer_assigned_at',
        'lawyer_assigned_by_user_id',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'hearing_type' => HearingType::class,
            'status' => HearingStatus::class,
            'hearing_date' => 'datetime',
            'held_at' => 'datetime',
            'lawyer_assigned_at' => 'datetime',
            'session_attended_by' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Auto-default assigned_lawyer to Matter's Lead Lawyer on creation
        static::creating(function (self $hearing) {
            if (empty($hearing->assigned_lawyer_user_id) && $hearing->matter_id) {
                $lead = MatterLawyer::where('matter_id', $hearing->matter_id)
                    ->active()
                    ->lead()
                    ->first();

                if ($lead) {
                    $hearing->assigned_lawyer_user_id = $lead->user_id;
                    $hearing->lawyer_assigned_at = now();
                }
            }
        });
    }

    // --- Relationships ---

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(Judge::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'our_attendee_user_id');
    }

    public function assignedLawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_lawyer_user_id');
    }

    public function lawyerAssignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lawyer_assigned_by_user_id');
    }

    public function postponedTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'postponed_to_hearing_id');
    }

    public function postponedFrom(): HasMany
    {
        return $this->hasMany(self::class, 'postponed_to_hearing_id');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function courtReviews(): HasMany
    {
        return $this->hasMany(CourtReview::class);
    }

    public function actionItems(): HasMany
    {
        return $this->hasMany(HearingActionItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    // --- Accessors ---

    public function isHeld(): bool
    {
        return $this->status === HearingStatus::Held;
    }

    /**
     * Whether this hearing has been postponed to another hearing.
     */
    public function getIsPostponementAttribute(): bool
    {
        return $this->postponed_to_hearing_id !== null;
    }

    /**
     * Build the full postponement chain for this hearing, traversing
     * both backwards (via postponedFrom) and forwards (via postponedTo)
     * to collect every linked hearing in chronological order.
     *
     * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #30.
     */
    public function getPostponementChain(): Collection
    {
        $chain = collect();
        $visited = [];

        // Walk backwards to find the root
        $root = $this;
        while (true) {
            $visited[$root->id] = true;
            $parent = self::withoutGlobalScopes()
                ->where('postponed_to_hearing_id', $root->id)
                ->first();

            if (! $parent || isset($visited[$parent->id])) {
                break;
            }

            $root = $parent;
        }

        // Walk forwards from root
        $current = $root;
        $visited = [];
        while ($current) {
            if (isset($visited[$current->id])) {
                break;
            }
            $visited[$current->id] = true;
            $chain->push($current);

            if (! $current->postponed_to_hearing_id) {
                break;
            }

            $current = self::withoutGlobalScopes()->find($current->postponed_to_hearing_id);
        }

        return $chain->sortBy('hearing_date')->values();
    }

    /**
     * Check whether adding a link to the given target would create a circular reference.
     */
    public static function wouldCreateCircularReference(string $sourceId, string $targetId): bool
    {
        $current = self::withoutGlobalScopes()->find($targetId);
        $visited = [$sourceId => true];

        while ($current) {
            if (isset($visited[$current->id])) {
                return true;
            }
            $visited[$current->id] = true;

            if (! $current->postponed_to_hearing_id) {
                break;
            }

            $current = self::withoutGlobalScopes()->find($current->postponed_to_hearing_id);
        }

        return false;
    }

    // --- Scopes ---

    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->where('hearing_date', '>=', now())
            ->where('hearing_date', '<=', now()->addDays($days))
            ->where('status', HearingStatus::Scheduled);
    }

    public function scopeByStatus($query, HearingStatus $status)
    {
        return $query->where('status', $status);
    }
}
