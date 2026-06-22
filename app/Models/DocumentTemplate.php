<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\DocumentTemplateFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    /** @use HasFactory<DocumentTemplateFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'name_ar',
        'name_en',
        'description',
        'document_type',
        'language',
        'body',
        'placeholder_schema',
        'is_active',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'body' => 'array',
            'placeholder_schema' => 'array',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
