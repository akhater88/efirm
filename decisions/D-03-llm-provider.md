# D-03 — LLM Provider Choice

**Status:** DECIDED
**Date:** 2026-06-21
**Decided by:** Founder (Abdullah) — defaulting to Wedge A (Arabic depth)
**Supersedes:** None (first decision)

---

## Context

SURGE-04 requires an LLM provider for 5 AI operations (draft, review, suggest, translate, explain) on bilingual Arabic/English legal contracts. The choice depends on:
- Arabic legal fluency (Wedge A priority)
- API ergonomics (streaming, structured output)
- Cost predictability
- Developer experience

## Decision

**Anthropic Claude** as the default LLM provider.

### Model selection per operation

| Operation | Model | Rationale |
|---|---|---|
| Draft | Claude Sonnet | Good quality, fast, cost-efficient for generation |
| Review | Claude Sonnet | Analysis doesn't need Opus-level reasoning |
| Suggest (redline) | Claude Opus | Highest quality for legal clause revision |
| Translate | Claude Sonnet | Translation is well-handled by Sonnet |
| Explain | Claude Haiku | Simple explanation, fastest + cheapest |

### Why Anthropic Claude

1. **Arabic fluency:** Claude demonstrates strong Arabic legal text generation — critical for Wedge A positioning against HAQQ.
2. **API quality:** Clean SDK, streaming support, structured output, tool use for future agent capabilities.
3. **Developer ergonomics:** `anthropic` PHP/JS SDK is well-maintained. Laravel integration via HTTP client is straightforward.
4. **Safety:** Constitutional AI alignment reduces risk of harmful legal output — important for a legal product.

### `[REVISIT-AFTER-AI-TEST]`

This decision defaults to Wedge A without F-00.1 validation. If F-00.1 reveals that HAQQ's Arabic is competent (Wedge B wins), the provider choice may shift to cost-optimize rather than quality-optimize. The abstraction layer (F-04.2 `LlmProvider` interface) makes switching providers a config change, not a rewrite.

## Consequences

- `.env` variable: `ANTHROPIC_API_KEY` (already present)
- Config: `config/llm.php` with provider + model + cost settings
- `AnthropicProvider` implements `LlmProvider` interface
- `MockProvider` for tests (deterministic responses)
- Cost tracking per interaction in `ai_interactions` table

## Review

- [ ] Founder sign-off
- [ ] `[REVISIT-AFTER-AI-TEST]` — Re-evaluate after F-00.1 completes
