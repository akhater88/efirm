<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\ClauseLanguage;
use App\Enums\RiskPosition;
use Database\Factories\DocumentClauseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentClause extends Model
{
    /** @use HasFactory<DocumentClauseFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids;

    protected $fillable = [
        'workspace_id',
        'document_version_id',
        'position',
        'clause_path',
        'title',
        'body',
        'language',
        'clause_type',
        'risk_position',
    ];

    protected function casts(): array
    {
        return [
            'body' => 'array',
            'language' => ClauseLanguage::class,
            'risk_position' => RiskPosition::class,
            'position' => 'integer',
        ];
    }

    // --- Relationships ---

    public function version(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'document_version_id');
    }
}
