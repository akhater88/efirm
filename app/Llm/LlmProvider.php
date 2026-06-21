<?php

namespace App\Llm;

interface LlmProvider
{
    /**
     * Send a completion request to the LLM.
     */
    public function complete(string $prompt, LlmRequestOptions $options): LlmResponse;

    /**
     * Get the provider name (for audit logging).
     */
    public function name(): string;
}
