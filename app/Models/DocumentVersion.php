<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\DocumentVersionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentVersion extends Model
{
    /** @use HasFactory<DocumentVersionFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'document_id',
        'version_number',
        'body',
        'body_hash',
        'change_summary',
        'created_by_user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'body' => 'array',
            'version_number' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function clauses(): HasMany
    {
        return $this->hasMany(DocumentClause::class)->orderBy('position');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
