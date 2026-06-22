<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\FormSubmissionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmission extends Model
{
    /** @use HasFactory<FormSubmissionFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'form_template_id',
        'template_version_at_submission',
        'submittable_type',
        'submittable_id',
        'submitted_by_user_id',
        'submitted_at',
        'values',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'template_version_at_submission' => 'integer',
            'submitted_at' => 'datetime',
            'values' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function submittable(): MorphTo
    {
        return $this->morphTo();
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
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
