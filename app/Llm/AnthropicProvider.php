<?php

namespace App\Llm;

use Anthropic;
use Anthropic\Client;

class AnthropicProvider implements LlmProvider
{
    private Client $client;

    public function __construct()
    {
        $apiKey = config('llm.anthropic.api_key');

        if (! $apiKey) {
            throw new \RuntimeException('ANTHROPIC_API_KEY is not configured.');
        }

        $this->client = Anthropic::client($apiKey);
    }

    public function complete(string $prompt, LlmRequestOptions $options): LlmResponse
    {
        $startTime = hrtime(true);

        $params = [
            'model' => $options->model,
            'max_tokens' => $options->maxTokens,
            'temperature' => $options->temperature,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        if ($options->systemPrompt) {
            $params['system'] = $options->systemPrompt;
        }

        $response = $this->client->messages()->create($params);

        $latencyMs = (int) ((hrtime(true) - $startTime) / 1_000_000);

        $content = '';
        foreach ($response->content as $block) {
            if ($block->type === 'text') {
                $content .= $block->text;
            }
        }

        $inputTokens = $response->usage->inputTokens;
        $outputTokens = $response->usage->outputTokens;
        $costUsd = $this->calculateCost($options->model, $inputTokens, $outputTokens);

        return new LlmResponse(
            content: $content,
            model: $response->model,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            costUsd: $costUsd,
            latencyMs: $latencyMs,
        );
    }

    public function name(): string
    {
        return 'anthropic';
    }

    /**
     * Calculate cost based on Anthropic's published pricing.
     */
    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        // Pricing per 1M tokens (as of 2025)
        $pricing = config('llm.anthropic.pricing', []);

        $modelKey = match (true) {
            str_contains($model, 'opus') => 'opus',
            str_contains($model, 'sonnet') => 'sonnet',
            str_contains($model, 'haiku') => 'haiku',
            default => 'sonnet',
        };

        $inputRate = $pricing[$modelKey]['input'] ?? 3.0;   // $/1M tokens
        $outputRate = $pricing[$modelKey]['output'] ?? 15.0; // $/1M tokens

        return ($inputTokens * $inputRate / 1_000_000) + ($outputTokens * $outputRate / 1_000_000);
    }
}
