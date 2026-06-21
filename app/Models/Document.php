<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\DocumentLanguage;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'matter_id',
        'title',
        'document_type',
        'language_primary',
        'status',
        'current_version_id',
        'original_file_url',
        'metadata',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'language_primary' => DocumentLanguage::class,
            'status' => DocumentStatus::class,
            'metadata' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'current_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version_number');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(DocumentShare::class);
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

    public function scopeDraft($query)
    {
        return $query->where('status', DocumentStatus::Draft);
    }

    public function scopeByType($query, DocumentType $type)
    {
        return $query->where('document_type', $type);
    }
}
