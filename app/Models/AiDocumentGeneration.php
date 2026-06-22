<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\AiDocGenerationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDocumentGeneration extends Model
{
    use BelongsToWorkspace, HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'matter_id',
        'user_id',
        'template_key',
        'intent_payload',
        'prompt_used',
        'model_used',
        'input_tokens',
        'output_tokens',
        'cost_usd',
        'latency_ms',
        'generated_document_id',
        'status',
        'error_message',
        'created_by_user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'intent_payload' => 'array',
            'status' => AiDocGenerationStatus::class,
            'cost_usd' => 'decimal:6',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'latency_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    // APPEND-ONLY enforcement
    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Append-only: ai_document_generations cannot be updated.'));
        static::deleting(fn () => throw new \LogicException('Append-only: ai_document_generations cannot be deleted.'));
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generatedDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'generated_document_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
