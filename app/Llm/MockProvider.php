<?php

namespace App\Llm;

/**
 * Deterministic mock LLM provider for tests.
 */
class MockProvider implements LlmProvider
{
    private ?string $fixedResponse = null;

    private int $fixedInputTokens = 100;

    private int $fixedOutputTokens = 50;

    public function withResponse(string $response): self
    {
        $this->fixedResponse = $response;

        return $this;
    }

    public function withTokens(int $input, int $output): self
    {
        $this->fixedInputTokens = $input;
        $this->fixedOutputTokens = $output;

        return $this;
    }

    public function complete(string $prompt, LlmRequestOptions $options): LlmResponse
    {
        $content = $this->fixedResponse ?? $this->generateDeterministicResponse($prompt);

        return new LlmResponse(
            content: $content,
            model: 'mock-model',
            inputTokens: $this->fixedInputTokens,
            outputTokens: $this->fixedOutputTokens,
            costUsd: 0.0001,
            latencyMs: 50,
        );
    }

    public function name(): string
    {
        return 'mock';
    }

    private function generateDeterministicResponse(string $prompt): string
    {
        $lower = mb_strtolower($prompt);

        // Return different responses based on keywords in the prompt
        if (str_contains($lower, 'draft') || str_contains($prompt, 'صياغة')) {
            return 'يلتزم الطرف الأول بتقديم الخدمات المتفق عليها وفقاً للشروط والأحكام المنصوص عليها في هذه الاتفاقية. The First Party agrees to provide the agreed-upon services in accordance with the terms and conditions set forth in this Agreement.';
        }

        if (str_contains($lower, 'review') || str_contains($prompt, 'مراجعة')) {
            return 'This clause contains a potential risk: the liability cap of 10% is below market standard for similar transactions in Jordan. Consider raising to 25% to better protect the buyer. كذلك، ننصح بإضافة بند يحدد آلية حل النزاعات بشكل أوضح.';
        }

        if (str_contains($lower, 'suggest') || str_contains($prompt, 'اقتراح')) {
            return 'تقتصر مسؤولية البائع الإجمالية بموجب جميع الضمانات على 25% من ثمن الشراء، وتنتهي خلال 12 شهراً من تاريخ الإغلاق.';
        }

        if (str_contains($lower, 'translate') || str_contains($prompt, 'ترجم')) {
            return 'The Seller\'s aggregate liability under all warranties shall be limited to 25% of the purchase price and shall expire within 12 months from the Closing Date.';
        }

        if (str_contains($lower, 'explain') || str_contains($prompt, 'اشرح')) {
            return 'This clause limits how much the seller can be held liable for. It sets a cap at a percentage of the total deal value and includes a time limit after which claims cannot be made.';
        }

        return 'AI response for the given prompt.';
    }
}
