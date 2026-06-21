<?php

namespace App\Llm;

class LlmRequestOptions
{
    public function __construct(
        public string $model = 'claude-sonnet-4-20250514',
        public int $maxTokens = 4096,
        public float $temperature = 0.3,
        public ?string $systemPrompt = null,
    ) {}

    public static function forDraft(): self
    {
        return new self(
            model: config('llm.models.draft', 'claude-sonnet-4-20250514'),
            maxTokens: 4096,
            temperature: 0.4,
        );
    }

    public static function forReview(): self
    {
        return new self(
            model: config('llm.models.review', 'claude-sonnet-4-20250514'),
            maxTokens: 4096,
            temperature: 0.2,
        );
    }

    public static function forSuggest(): self
    {
        return new self(
            model: config('llm.models.suggest', 'claude-opus-4-20250514'),
            maxTokens: 4096,
            temperature: 0.3,
        );
    }

    public static function forTranslate(): self
    {
        return new self(
            model: config('llm.models.translate', 'claude-sonnet-4-20250514'),
            maxTokens: 4096,
            temperature: 0.1,
        );
    }

    public static function forExplain(): self
    {
        return new self(
            model: config('llm.models.explain', 'claude-haiku-4-20250514'),
            maxTokens: 2048,
            temperature: 0.2,
        );
    }
}
