<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmartList extends Model
{
    use BelongsToWorkspace, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'entity_type',
        'name',
        'filters',
        'sort_order',
        'is_shared_to_workspace',
        'is_pinned',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'sort_order' => 'array',
            'is_shared_to_workspace' => 'boolean',
            'is_pinned' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // --- Scopes ---

    public function scopeForEntity($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('is_shared_to_workspace', true);
        });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}
