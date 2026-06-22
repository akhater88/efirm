<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\OpportunityStatus;
use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'contact_id',
        'pipeline_id',
        'lead_id',
        'title',
        'status',
        'current_stage',
        'estimated_value',
        'currency',
        'expected_close_date',
        'converted_to_matter_id',
        'notes',
        'assigned_to_user_id',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => OpportunityStatus::class,
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function convertedToMatter(): BelongsTo
    {
        return $this->belongsTo(Matter::class, 'converted_to_matter_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
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
