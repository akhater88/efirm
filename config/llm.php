<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default LLM Provider
    |--------------------------------------------------------------------------
    |
    | The default provider to use for AI operations. Supported: "anthropic", "mock"
    | Use "mock" for testing (deterministic responses).
    |
    */
    'provider' => env('LLM_PROVIDER', 'anthropic'),

    /*
    |--------------------------------------------------------------------------
    | Model Selection per Operation
    |--------------------------------------------------------------------------
    |
    | Per D-03: Sonnet for most operations, Opus for suggest/redline,
    | Haiku for explain (simple + fast).
    |
    */
    'models' => [
        'draft' => env('LLM_MODEL_DRAFT', 'claude-sonnet-4-20250514'),
        'review' => env('LLM_MODEL_REVIEW', 'claude-sonnet-4-20250514'),
        'suggest' => env('LLM_MODEL_SUGGEST', 'claude-opus-4-20250514'),
        'translate' => env('LLM_MODEL_TRANSLATE', 'claude-sonnet-4-20250514'),
        'explain' => env('LLM_MODEL_EXPLAIN', 'claude-haiku-4-20250514'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic Configuration
    |--------------------------------------------------------------------------
    */
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),

        // Pricing per 1M tokens (USD) — update when Anthropic changes pricing
        'pricing' => [
            'opus' => ['input' => 15.0, 'output' => 75.0],
            'sonnet' => ['input' => 3.0, 'output' => 15.0],
            'haiku' => ['input' => 0.25, 'output' => 1.25],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    |
    | Per-workspace daily interaction limits (soft cap).
    |
    */
    'rate_limits' => [
        'daily_interactions' => env('LLM_DAILY_LIMIT', 100),
    ],
];
