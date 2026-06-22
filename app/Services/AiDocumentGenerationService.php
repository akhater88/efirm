<?php

namespace App\Services;

use App\Enums\AiDocGenerationStatus;
use App\Enums\DocumentLanguage;
use App\Enums\DocumentType;
use App\Llm\LlmProvider;
use App\Llm\LlmRequestOptions;
use App\Models\AiDocumentGeneration;
use App\Models\Matter;
use App\Models\User;

class AiDocumentGenerationService
{
    public function __construct(
        private LlmProvider $provider,
        private DocumentService $documentService,
    ) {}

    /**
     * Generate a full document from a template + structured intent.
     * Creates the audit row as a single insert (append-only).
     */
    public function generate(string $templateKey, array $intentPayload, Matter $matter, User $actor): AiDocumentGeneration
    {
        $prompt = $this->buildPrompt($templateKey, $intentPayload);
        $options = LlmRequestOptions::forDraft();
        $options->systemPrompt = $this->systemPrompt();

        try {
            $response = $this->provider->complete($prompt, $options);

            // Parse AI response into TipTap JSON
            $body = $this->parseToTiptapJson($response->content);

            // Create the document
            $title = $intentPayload['title'] ?? $this->generateTitle($templateKey, $intentPayload);
            $language = $intentPayload['language'] ?? 'bilingual';

            $document = $this->documentService->createDocument($matter, $title, $body, $actor, [
                'document_type' => DocumentType::Contract,
                'language_primary' => DocumentLanguage::tryFrom($language) ?? DocumentLanguage::Bilingual,
                'change_summary' => __('ai.generated_via_ai', ['template' => $templateKey]),
            ]);

            // Single append-only insert with all fields
            return AiDocumentGeneration::create([
                'workspace_id' => $matter->workspace_id,
                'matter_id' => $matter->id,
                'user_id' => $actor->id,
                'template_key' => $templateKey,
                'intent_payload' => $intentPayload,
                'prompt_used' => $prompt,
                'model_used' => $response->model,
                'input_tokens' => $response->inputTokens,
                'output_tokens' => $response->outputTokens,
                'cost_usd' => $response->costUsd,
                'latency_ms' => $response->latencyMs,
                'generated_document_id' => $document->id,
                'status' => AiDocGenerationStatus::Complete,
                'created_by_user_id' => $actor->id,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Record failure as append-only audit row
            return AiDocumentGeneration::create([
                'workspace_id' => $matter->workspace_id,
                'matter_id' => $matter->id,
                'user_id' => $actor->id,
                'template_key' => $templateKey,
                'intent_payload' => $intentPayload,
                'prompt_used' => $prompt,
                'status' => AiDocGenerationStatus::Failed,
                'error_message' => $e->getMessage(),
                'created_by_user_id' => $actor->id,
                'created_at' => now(),
            ]);
        }
    }

    private function buildPrompt(string $templateKey, array $intentPayload): string
    {
        $templatePath = base_path("prompts/document_generation/{$templateKey}.md");

        $template = file_exists($templatePath)
            ? file_get_contents($templatePath)
            : $this->defaultPromptTemplate();

        // Inject intent values
        $prompt = $template;
        foreach ($intentPayload as $key => $value) {
            if (is_string($value)) {
                $prompt .= "\n{$key}: {$value}";
            } elseif (is_array($value)) {
                $prompt .= "\n{$key}: ".implode(', ', $value);
            }
        }

        return $prompt;
    }

    private function defaultPromptTemplate(): string
    {
        return <<<'PROMPT'
You are a legal document drafting AI specializing in commercial contracts for Levant law firms.
Generate a complete, professionally formatted legal document based on the following parameters.
Use formal legal language. If bilingual, provide Arabic followed by English for each section.
Include standard clause sections: definitions, obligations, representations, liability, governing law, signatures.

[LEGAL-REVIEW-PENDING]

Parameters:
PROMPT;
    }

    private function systemPrompt(): string
    {
        // [LEGAL-REVIEW-PENDING] — founder-drafted
        return 'You are a legal document drafting AI. Generate complete, professionally formatted contracts in the requested language. Structure output as clear sections with headings. Always include the disclaimer: "AI-generated draft. Review before use. Not legal advice."';
    }

    private function parseToTiptapJson(string $content): array
    {
        $lines = explode("\n", trim($content));
        $nodes = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Detect headings
            if (str_starts_with($line, '# ')) {
                $nodes[] = ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => ltrim($line, '# ')]]];
            } elseif (str_starts_with($line, '## ')) {
                $nodes[] = ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => ltrim($line, '# ')]]];
            } elseif (str_starts_with($line, '### ')) {
                $nodes[] = ['type' => 'heading', 'attrs' => ['level' => 3], 'content' => [['type' => 'text', 'text' => ltrim($line, '# ')]]];
            } else {
                $nodes[] = ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $line]]];
            }
        }

        if (empty($nodes)) {
            $nodes[] = ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $content]]];
        }

        return ['type' => 'doc', 'content' => $nodes];
    }

    private function generateTitle(string $templateKey, array $intentPayload): string
    {
        $titles = [
            'nda_levant' => 'اتفاقية عدم إفصاح',
            'spa_jordan' => 'اتفاقية شراء أسهم',
            'supply_agreement_levant' => 'اتفاقية توريد',
            'services_agreement_levant' => 'اتفاقية خدمات',
            'commercial_lease_levant' => 'عقد إيجار تجاري',
        ];

        $base = $titles[$templateKey] ?? 'مستند مولّد بالذكاء الاصطناعي';

        if (isset($intentPayload['parties'])) {
            $parties = is_array($intentPayload['parties']) ? implode(' و ', $intentPayload['parties']) : $intentPayload['parties'];
            $base .= ' — '.$parties;
        }

        return $base;
    }
}
