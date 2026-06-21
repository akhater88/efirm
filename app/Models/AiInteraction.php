<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\AiInteractionType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInteraction extends Model
{
    use BelongsToWorkspace, HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'document_id',
        'document_clause_id',
        'interaction_type',
        'prompt',
        'response',
        'model',
        'input_tokens',
        'output_tokens',
        'cost_usd',
        'latency_ms',
        'was_accepted',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'interaction_type' => AiInteractionType::class,
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'cost_usd' => 'float',
            'latency_ms' => 'integer',
            'was_accepted' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
