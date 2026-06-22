<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\LitigationStatus;
use App\Enums\MatterLawyerRole;
use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use App\Enums\RepresentationRole;
use Database\Factories\MatterFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matter extends Model
{
    /** @use HasFactory<MatterFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $attributes = [
        'is_litigation' => false,
    ];

    protected $fillable = [
        'workspace_id',
        'title',
        'client_id',
        'practice_area',
        'status',
        'stage',
        'description',
        'internal_reference',
        'lead_lawyer_id',
        'opened_at',
        'closed_at',
        'tags',
        'is_litigation',
        'court_id',
        'judge_id',
        'court_case_number',
        'case_number_internal',
        'litigation_status',
        'filed_date',
        'next_hearing_date',
        'representation_role',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'practice_area' => PracticeArea::class,
            'status' => MatterStatus::class,
            'is_litigation' => 'boolean',
            'litigation_status' => LitigationStatus::class,
            'representation_role' => RepresentationRole::class,
            'filed_date' => 'date',
            'next_hearing_date' => 'date',
            'opened_at' => 'date',
            'closed_at' => 'date',
            'tags' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function client(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'client_id');
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(Judge::class);
    }

    public function counterparties(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'matter_counterparties')
            ->withPivot('representing', 'counterparty_role', 'our_position', 'notes', 'opposing_counsel_contact_id')
            ->withTimestamps();
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(Hearing::class);
    }

    public function courtReviews(): HasMany
    {
        return $this->hasMany(CourtReview::class);
    }

    public function serviceLogEntries(): HasMany
    {
        return $this->hasMany(ServiceLogEntry::class);
    }

    public function responsibleTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'responsible_team_id');
    }

    public function leadLawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_lawyer_id');
    }

    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'matter_lawyers')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function matterLawyers(): HasMany
    {
        return $this->hasMany(MatterLawyer::class)->active();
    }

    public function activeLead(): HasOne
    {
        return $this->hasOne(MatterLawyer::class)
            ->where('role', MatterLawyerRole::Lead)
            ->whereNull('unassigned_at');
    }

    public function activeSupportingLawyers(): HasMany
    {
        return $this->hasMany(MatterLawyer::class)
            ->where('role', MatterLawyerRole::Supporting)
            ->whereNull('unassigned_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function aiDocumentGenerations(): HasMany
    {
        return $this->hasMany(AiDocumentGeneration::class);
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

    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('status', MatterStatus::Active);
    }

    public function scopeByPracticeArea($query, PracticeArea $area)
    {
        return $query->where('practice_area', $area);
    }

    public function scopeLitigation($query)
    {
        return $query->where('is_litigation', true);
    }

    public function scopeCommercial($query)
    {
        return $query->where('is_litigation', false);
    }
}
