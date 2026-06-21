<?php

namespace App\Services;

use App\Enums\AiInteractionType;
use App\Llm\LlmProvider;
use App\Llm\LlmRequestOptions;
use App\Models\AiInteraction;
use App\Models\Document;
use App\Models\DocumentClause;
use App\Models\User;

class AiOrchestrationService
{
    public function __construct(
        private LlmProvider $provider,
    ) {}

    /**
     * Draft a new clause based on user intent.
     */
    public function draft(Document $document, string $intent, string $language, User $actor): AiInteraction
    {
        $systemPrompt = $this->loadPromptTemplate('draft', $language);
        $prompt = "Document title: {$document->title}\nPractice area: {$document->matter?->practice_area?->value}\n\nUser intent: {$intent}\n\nDraft a clause in {$language}.";

        return $this->executeAndLog(
            type: AiInteractionType::Draft,
            prompt: $prompt,
            systemPrompt: $systemPrompt,
            options: LlmRequestOptions::forDraft(),
            actor: $actor,
            document: $document,
        );
    }

    /**
     * Review a clause for risks and issues.
     */
    public function review(DocumentClause $clause, User $actor): AiInteraction
    {
        $document = $clause->version->document;
        $clauseText = $this->extractClauseText($clause);
        $language = $actor->preferred_locale ?? 'ar';

        $systemPrompt = $this->loadPromptTemplate('review', $language);
        $prompt = "Document: {$document->title}\nClause: {$clause->title}\nPractice area: {$document->matter?->practice_area?->value}\n\nClause text:\n{$clauseText}\n\nReview this clause for risks, ambiguities, and missing terms. Respond in {$language}.";

        return $this->executeAndLog(
            type: AiInteractionType::Review,
            prompt: $prompt,
            systemPrompt: $systemPrompt,
            options: LlmRequestOptions::forReview(),
            actor: $actor,
            document: $document,
            clause: $clause,
        );
    }

    /**
     * Suggest a revision for a clause.
     */
    public function suggest(DocumentClause $clause, string $instruction, User $actor): AiInteraction
    {
        $document = $clause->version->document;
        $clauseText = $this->extractClauseText($clause);
        $language = $actor->preferred_locale ?? 'ar';

        $systemPrompt = $this->loadPromptTemplate('suggest', $language);
        $prompt = "Document: {$document->title}\nClause: {$clause->title}\n\nOriginal clause:\n{$clauseText}\n\nInstruction: {$instruction}\n\nProvide a revised version of this clause. Respond in the same language as the original.";

        return $this->executeAndLog(
            type: AiInteractionType::Suggest,
            prompt: $prompt,
            systemPrompt: $systemPrompt,
            options: LlmRequestOptions::forSuggest(),
            actor: $actor,
            document: $document,
            clause: $clause,
        );
    }

    /**
     * Translate a clause to the target language.
     */
    public function translate(DocumentClause $clause, string $targetLanguage, User $actor): AiInteraction
    {
        $document = $clause->version->document;
        $clauseText = $this->extractClauseText($clause);

        $systemPrompt = $this->loadPromptTemplate('translate', $targetLanguage);
        $prompt = "Translate the following legal clause to {$targetLanguage}. Maintain legal terminology and formality.\n\nOriginal:\n{$clauseText}";

        return $this->executeAndLog(
            type: AiInteractionType::Translate,
            prompt: $prompt,
            systemPrompt: $systemPrompt,
            options: LlmRequestOptions::forTranslate(),
            actor: $actor,
            document: $document,
            clause: $clause,
        );
    }

    /**
     * Explain a clause in plain language.
     */
    public function explain(DocumentClause $clause, User $actor): AiInteraction
    {
        $document = $clause->version->document;
        $clauseText = $this->extractClauseText($clause);
        $language = $actor->preferred_locale ?? 'ar';

        $systemPrompt = $this->loadPromptTemplate('explain', $language);
        $prompt = "Explain the following legal clause in plain, simple language that a non-lawyer business person can understand. Respond in {$language}.\n\nClause:\n{$clauseText}";

        return $this->executeAndLog(
            type: AiInteractionType::Explain,
            prompt: $prompt,
            systemPrompt: $systemPrompt,
            options: LlmRequestOptions::forExplain(),
            actor: $actor,
            document: $document,
            clause: $clause,
        );
    }

    /**
     * Mark an AI interaction as accepted or rejected.
     */
    public function markAccepted(AiInteraction $interaction, bool $accepted): void
    {
        $interaction->update(['was_accepted' => $accepted]);
    }

    /**
     * Execute the LLM call and persist the audit row.
     */
    private function executeAndLog(
        AiInteractionType $type,
        string $prompt,
        string $systemPrompt,
        LlmRequestOptions $options,
        User $actor,
        Document $document,
        ?DocumentClause $clause = null,
    ): AiInteraction {
        $options->systemPrompt = $systemPrompt;

        $response = $this->provider->complete($prompt, $options);

        return AiInteraction::create([
            'workspace_id' => $document->workspace_id,
            'user_id' => $actor->id,
            'document_id' => $document->id,
            'document_clause_id' => $clause?->id,
            'interaction_type' => $type,
            'prompt' => $prompt,
            'response' => $response->content,
            'model' => $response->model,
            'input_tokens' => $response->inputTokens,
            'output_tokens' => $response->outputTokens,
            'cost_usd' => $response->costUsd,
            'latency_ms' => $response->latencyMs,
            'created_at' => now(),
        ]);
    }

    /**
     * Load a prompt template system prompt.
     * [LEGAL-REVIEW-PENDING] — all templates must be advisor-approved before real users.
     */
    private function loadPromptTemplate(string $operation, string $language): string
    {
        // [LEGAL-REVIEW-PENDING] Founder-drafted placeholder prompts
        $templates = [
            'draft' => 'You are a legal AI assistant specializing in commercial contracts for Levant law firms (Jordan, Lebanon, Palestine, Iraq). Draft clauses that are legally precise, formally worded, and appropriate for the jurisdiction. When drafting in Arabic, use formal legal Arabic (فصحى قانونية). Never provide legal advice — only draft text for lawyer review.',
            'review' => 'You are a legal AI assistant reviewing commercial contract clauses. Identify: (1) potential risks to our client, (2) ambiguous language, (3) missing standard protections, (4) terms that deviate from market standard. Be specific and actionable. Format as numbered findings.',
            'suggest' => 'You are a legal AI assistant suggesting clause revisions. Provide the complete revised clause text — not a description of changes. Maintain the formal legal tone and terminology of the original. Preserve any bilingual structure.',
            'translate' => 'You are a legal translator specializing in Arabic-English commercial contract translation. Translate maintaining: (1) legal terminology accuracy, (2) formal register, (3) jurisdiction-specific terms (Levant legal vocabulary). Do not simplify or paraphrase — translate precisely.',
            'explain' => 'You are a legal assistant explaining contract clauses in plain language. Explain what the clause means practically — what obligations it creates, what risks it carries, and what happens if it is breached. Use simple language a business owner would understand.',
        ];

        return $templates[$operation] ?? $templates['review'];
    }

    /**
     * Extract plain text from a DocumentClause's body JSON.
     */
    private function extractClauseText(DocumentClause $clause): string
    {
        return $this->walkText($clause->body);
    }

    private function walkText(array $node): string
    {
        if (($node['type'] ?? '') === 'text') {
            return $node['text'] ?? '';
        }

        $text = '';
        foreach ($node['content'] ?? [] as $child) {
            $text .= $this->walkText($child);
        }

        return $text;
    }
}
