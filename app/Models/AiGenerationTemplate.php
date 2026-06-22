<?php

namespace App\Models;

use App\Enums\LegalReviewStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiGenerationTemplate extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'key',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'prompt_template',
        'intent_schema',
        'version',
        'legal_review_status',
        'legal_review_approver_name',
        'legal_review_approver_date',
        'is_active',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'intent_schema' => 'array',
            'legal_review_status' => LegalReviewStatus::class,
            'legal_review_approver_date' => 'date',
            'is_active' => 'boolean',
            'version' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // --- Helpers ---

    public function isSystemTemplate(): bool
    {
        return $this->workspace_id === null;
    }

    public function isApproved(): bool
    {
        return $this->legal_review_status === LegalReviewStatus::Approved;
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    // --- Scopes ---

    public function scopeSystem($query)
    {
        return $query->whereNull('workspace_id');
    }

    public function scopeForWorkspace($query, string $workspaceId)
    {
        return $query->where(function ($q) use ($workspaceId) {
            $q->where('workspace_id', $workspaceId)
                ->orWhereNull('workspace_id');
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Resolve the effective template for a key + workspace.
     * Workspace-specific overrides system.
     */
    public static function resolveForKey(string $key, ?string $workspaceId = null): ?self
    {
        if ($workspaceId) {
            $workspaceTemplate = static::where('key', $key)
                ->where('workspace_id', $workspaceId)
                ->where('is_active', true)
                ->first();

            if ($workspaceTemplate) {
                return $workspaceTemplate;
            }
        }

        return static::where('key', $key)
            ->whereNull('workspace_id')
            ->where('is_active', true)
            ->first();
    }
}
