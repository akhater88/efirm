# SURGE-04 — Clause Library & AI Inline

**Surge ID:** S-04
**Name:** Clause Library & AI Inline
**Type:** BUILD Surge
**Estimated duration:** 8–12 days (with Claude Code); 2.5–3 weeks (manual)
**Depends on:** S-03 complete; F-00.1 AI test result; D-03 (LLM provider) decided
**Enables:** S-05 (no direct dependency); S-06 launch (the wedge demo)

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | `[REVISIT-AFTER-AI-TEST]` — the depth of Arabic-legal capability and the LLM provider choice depend on the F-00.1 result |
| Legal domain | `[PENDING-LEGAL-REVIEW]` — every AI prompt that produces legal output must be reviewed by the legal advisor; AI disclaimer copy must be advisor-approved |
| Sign-off | PENDING — Founder + Legal Advisor + ≥ 2 pilot firms |

---

## Goal

Make the AI live INSIDE the document, not in a separate chat app. Make clauses reusable. This is the surface where HAQQ's Vol. 14 architectural seam (eFirm-Vue vs chat.haqq.ai split) becomes our integrated advantage made visible to a buyer.

By the end of this Surge:
- A Clause Library exists at the workspace level
- A lawyer can save any clause from a document into the library
- A lawyer can insert any library clause into a document
- A lawyer can ask the AI (selected text in mind) to draft / review / suggest / redline a clause
- AI suggestions appear inline at the clause level — accepted/rejected like Word's track changes
- All AI interactions are logged for audit and for future fine-tuning data
- Bilingual AR/EN clause pairing: a clause can store both AR and EN content as paired versions
- Risk flags on clauses (favourable / balanced / adverse) — the playbook concept

---

## Pre-Surge: AI test result branching `[REVISIT-AFTER-AI-TEST]`

The depth of this Surge's Arabic-specific features depends on F-00.1.

### If Wedge A wins (Arabic depth is the wedge — HAQQ AR is weak)
- LLM provider candidates: Claude (strong AR), Cohere Aya, multi-provider router. **Prefer Anthropic Claude** for both AR fluency and developer ergonomics.
- Invest in a curated Levant-clause-library seed (Jordan/Lebanon contract templates) baked into the product on day one
- Build Arabic-specific prompt templates and a small RAG layer over Levant statute references (in S-04 or deferred to Year-2 — flag for re-decision)
- Marketing/positioning: lead with Arabic-legal output quality

### If Wedge B wins (Integrated single surface is the wedge — HAQQ AR is competent)
- LLM provider: pick on overall quality + cost + integration ergonomics. Anthropic Claude or OpenAI GPT-4 acceptable
- Skip Levant-statute RAG at MVP; defer Year-2
- No Levant clause seed; library starts empty per workspace
- Marketing/positioning: lead with UX — "your contract, your AI, your matter, one screen"

### Both Flows below are wedge-agnostic in structure
What changes is: prompt engineering depth, seed library content, provider choice. The data model + UI + AI orchestration code is the same.

---

## Flows

### F-04.1 — Clause Library (workspace-scoped)

**Goal:** A workspace-level library of reusable clauses. Lawyers save clauses from documents into the library; insert library clauses into documents.

**Scope:**
- `library_clauses` table:
  - `id` UUID
  - `workspace_id` UUID FK
  - `title` VARCHAR(255)
  - `clause_type` VARCHAR(100) (e.g., "limitation_of_liability", "governing_law", "indemnification", "termination")
  - `practice_area` matches Matter's enum
  - `language` ENUM('ar', 'en', 'bilingual')
  - `body_ar` LONGTEXT nullable (editor JSON fragment)
  - `body_en` LONGTEXT nullable (editor JSON fragment)
  - `risk_position` ENUM('favourable', 'balanced', 'adverse') nullable
  - `is_fallback_of_id` UUID FK self-referential (a clause can be marked as a fallback of another clause — the playbook concept)
  - `tags` JSON
  - `source_document_id` UUID FK nullable (which document this was first saved from)
  - `usage_count` INT default 0 (incremented on insert into a document)
  - `last_used_at` TIMESTAMP nullable
  - audit timestamps + soft deletes
  - composite index `(workspace_id, clause_type, practice_area)`

- Eloquent: `LibraryClause` with `BelongsToWorkspace`, `fallbacks()` self-relation
- Service: `LibraryService::saveFromDocument(DocumentClause $sourceClause, array $attrs): LibraryClause`
- Service: `LibraryService::insertIntoDocument(LibraryClause $libClause, DocumentVersion $targetVersion, int $position): DocumentVersion` — inserts as new content, creates a new Version, increments `usage_count`

**Entities touched:** `library_clauses` (new); `document_clauses` (read for save-from-document); `document_versions` (write on insert-into-document).

**API surface:**
- Full CRUD: `GET /api/v1/library/clauses`, `POST /api/v1/library/clauses`, `GET/PATCH/DELETE /api/v1/library/clauses/{id}`
- `POST /api/v1/library/clauses/from-document-clause/{document_clause_id}` — save a document clause to library
- `POST /api/v1/documents/{id}/insert-library-clause` — insert library clause into a document at a given position

**UI surface:**
- Library page: `/library` — filterable list, search, grouped by clause_type
- Filament `LibraryClauseResource`
- In-editor sidebar: "Insert from Library" — search + filter + click-to-insert
- "Save to Library" action on any clause in the editor (context menu)
- A clause type taxonomy seeded at install time:
  - **If Wedge A:** seed with 30–50 Levant commercial clauses (curated by lawyer advisor)
  - **If Wedge B:** seed empty; user populates

**Key decisions to make in Wave-Ready Package:**
- Initial taxonomy of clause_type values (decide with lawyer advisor)
- AR/EN pairing UX: side-by-side editor for paired clauses? Or two separate fields?
- "Fallback of" relationship: visualize as a tree (Year-2) or a flat list with badges (MVP)?
- Search: by title + body? AR + EN content search?
- `[REVISIT-AFTER-AI-TEST]` decision on seed library inclusion

**Acceptance criteria:**
- Pest: full CRUD on LibraryClause
- Pest: save-from-document creates LibraryClause with source link
- Pest: insert-into-document creates a new Version with the clause content present; usage_count + last_used_at updated
- Pest: workspace isolation
- Pest: bilingual clause stores and retrieves both bodies correctly

**Dependencies:** S-03 complete.

---

### F-04.2 — LLM provider abstraction & AI infrastructure

**Goal:** A clean adapter pattern for LLM providers so the choice of provider can be revisited without rewriting the AI features. The chosen provider is configured here; usage is metered for cost tracking.

**Scope:**
- `LlmProvider` interface:
  - `complete(prompt: string, options: LlmRequestOptions): LlmResponse`
  - `complete_stream(prompt: string, options): iterable<LlmStreamChunk>` (streaming)
- Concrete implementations (only the chosen one shipped at MVP; others scaffolded for Year-2):
  - `AnthropicProvider` (recommended default)
  - `OpenAiProvider`
  - `MockProvider` (used in tests; deterministic responses)
- `LlmRequestOptions`: model name, max tokens, temperature, system prompt, response format
- Service: `AiOrchestrationService` — high-level operations (draft, review, suggest, translate, redline), each composing a prompt + provider call + post-processing
- Configuration via `config/llm.php` + `.env` (API keys)

- `ai_interactions` table (audit + usage):
  - `id` UUID
  - `workspace_id` UUID FK
  - `user_id` UUID FK
  - `document_id` UUID FK nullable
  - `document_clause_id` UUID FK nullable
  - `interaction_type` ENUM('draft', 'review', 'suggest', 'translate', 'redline', 'explain')
  - `prompt` TEXT (the full prompt sent to LLM — for audit)
  - `response` TEXT (the response — for audit)
  - `model` VARCHAR(100) (which model was used)
  - `input_tokens` INT
  - `output_tokens` INT
  - `cost_usd` DECIMAL(10,6) (computed from provider pricing)
  - `latency_ms` INT
  - `was_accepted` BOOLEAN nullable (set when user accepts/rejects the suggestion)
  - `created_at` TIMESTAMP

**Entities touched:** `ai_interactions` (new).

**API surface:**
- Internal only at this Flow — exercised by F-04.3 onward

**UI surface:** None at this Flow.

**Key decisions to make in Wave-Ready Package:**
- D-03: confirm LLM provider choice based on F-00.1 result
- Anthropic model: Claude Opus vs Sonnet (cost vs quality tradeoff; recommend Sonnet for most operations, Opus for redline)
- Streaming vs blocking responses (default: streaming for UI responsiveness)
- Rate limit per workspace (default: 100 interactions/day; soft cap with admin override)
- Per-user vs per-workspace cost attribution

**Acceptance criteria:**
- Pest: `MockProvider` returns deterministic responses for unit tests
- Pest: `AnthropicProvider` integration test (against a live test API key in CI secrets) round-trips a "hello, are you working" prompt
- Pest: an interaction is persisted with full audit detail on every AI call
- Pest: cost calculation accurate for known input/output token counts
- Latency budget: streaming first token < 1.5s p95

**Dependencies:** F-04.1; D-03 decided.

---

### F-04.3 — AI inline: draft / review / suggest

**Goal:** A lawyer selects text (or a clause) in the editor, opens an AI panel, picks an operation (draft, review, suggest), and sees the result inline — accept/reject like Word track changes.

**Scope:**
- Editor extension: a side panel ("AI Assistant") with operation buttons
- Operations:
  - **Draft**: cursor position; user types intent; AI inserts a draft clause
  - **Review**: selected clause; AI returns a critique (risks, ambiguities, missing terms) shown as comments/suggestions
  - **Suggest revision**: selected clause; AI returns an alternative version shown as a redline suggestion
  - **Translate**: selected clause; AI returns the AR or EN counterpart (pair-aware if a paired library clause exists)
  - **Explain**: selected clause; AI returns a plain-language explanation in the user's locale
- Prompt templates per operation, parameterized by:
  - User's preferred locale (AR / EN)
  - Practice area of the parent Matter
  - Governing law of the parent contract (when set in S-05)
  - Counterparty position (when set in S-05) — affects "review" tone
- Result handling:
  - Draft: insert at cursor as a new (un-confirmed) block; user accepts or undoes
  - Review: render as inline comments anchored to the clause path
  - Suggest: render as a diff between original and suggested text, with Accept / Reject buttons
  - Translate: insert paired AR/EN block, or replace if user chooses
  - Explain: ephemeral popover (not persisted in document body)
- Every operation creates an `AiInteraction` audit row; on Accept, `was_accepted=true`
- Per-prompt disclaimer footer (legal advisor-approved): "AI-generated text. Review before use."

**Entities touched:** `ai_interactions` (insert); `document_versions` (insert on accept of Draft or Suggest).

**API surface:**
- `POST /api/v1/documents/{id}/ai/draft` — body: `{ "intent": "...", "cursor_position": int, "language": "ar|en" }`
- `POST /api/v1/documents/{id}/ai/review` — body: `{ "clause_id": "uuid" }`
- `POST /api/v1/documents/{id}/ai/suggest` — body: `{ "clause_id": "uuid", "instruction": "make it more favourable to buyer" }`
- `POST /api/v1/documents/{id}/ai/translate` — body: `{ "clause_id": "uuid", "target_language": "ar|en" }`
- `POST /api/v1/documents/{id}/ai/explain` — body: `{ "clause_id": "uuid" }`
- All five endpoints stream responses (Server-Sent Events or chunked transfer)
- `POST /api/v1/ai-interactions/{id}/accept` — marks accepted; if Draft/Suggest, applies to document, creating a new Version

**UI surface:**
- Editor right panel: collapsible AI Assistant
- Inline diff renderer for Suggest
- Inline comment renderer for Review
- Accept/Reject buttons with localized labels
- Disclaimer text per operation (advisor-approved AR + EN)

**Key decisions to make in Wave-Ready Package:**
- `[PENDING-LEGAL-REVIEW]` All five prompt templates (AR + EN) must be reviewed by lawyer advisor before shipping
- Disclaimer copy (AR + EN) — advisor-approved
- Streaming UX: token-by-token typing, or chunked-paragraph reveal?
- Cost guardrails: per-user daily token budget?
- Default model per operation (e.g., Sonnet for Translate/Explain; Opus for Suggest where quality matters most)

**Acceptance criteria:**
- Pest: each of 5 operations completes end-to-end with `MockProvider`
- Pest: a "Draft" accept creates a new Document Version with the drafted text inserted
- Pest: a "Suggest" accept replaces the original clause with the suggested text, creates new Version
- Pest: AI interaction audit row created with prompt + response + cost + latency
- Pest browser test: AI panel renders correctly in AR (RTL — panel on the LEFT) and EN (LTR — panel on the right)
- Manual QA with lawyer advisor: outputs in Arabic are legally idiomatic, not literal translations from English
- Manual QA: when the Counterparty position is "buyer" (set in S-05 or stub at MVP), "review" output frames risk from the buyer's perspective

**Dependencies:** F-04.1, F-04.2.

---

### F-04.4 — Clause risk flags & playbook fallbacks

**Goal:** Clauses (in documents and library) can be flagged with a risk position. Library clauses can have explicit fallback positions (the playbook). When a lawyer is negotiating, they can see "if this clause is rejected, here are 3 fallback positions."

**Scope:**
- `document_clauses` gets `risk_position` ENUM nullable (mirrors `library_clauses.risk_position`)
- AI extension: a new operation `flag_risk` — AI suggests a risk_position for a clause; user accepts or overrides
- Library UI: when viewing a clause, show its fallback chain ("If rejected, try: [Clause B] → [Clause C]")
- Editor UI: clauses with risk flags get a colored marginal indicator (green = favourable, gray = balanced, red = adverse)
- Filament: LibraryClause table can be filtered by risk_position
- "Suggest fallback" AI operation: given a clause, suggests a more favourable / more balanced / more adverse alternative

**Entities touched:** `document_clauses.risk_position`, `library_clauses.is_fallback_of_id` (already in F-04.1).

**API surface:**
- `PATCH /api/v1/document-clauses/{id}/risk` — `{ "risk_position": "favourable|balanced|adverse|null" }`
- `POST /api/v1/documents/{id}/ai/flag-risk` — AI-suggested flag

**UI surface:**
- Clause marginal indicators (color + tooltip with localized label)
- Library: tree/list visualization of fallback chains
- "Fallback" AI panel button

**Key decisions to make in Wave-Ready Package:**
- `[PENDING-LEGAL-REVIEW]` Definition of favourable/balanced/adverse from whose perspective by default — buyer? seller? our client?
- Visualization style for fallback chains
- Whether risk flag is per-clause-instance or per-clause-type (lean per-instance; clauses get scoped to their document)

**Acceptance criteria:**
- Pest: setting a risk flag persists; reading returns the flag
- Pest: fallback chain traversal works (LibraryClause::fallbacks()->fallbacks() recursion)
- Pest: AI flag_risk returns one of the 3 enum values (post-processed)
- UI smoke test: marginal indicator renders correctly in both LTR and RTL

**Dependencies:** F-04.1, F-04.3.

---

## Surge acceptance criteria

- [ ] F-04.1: Clause Library works; save-from-doc, insert-into-doc, bilingual support
- [ ] F-04.2: LLM abstraction works; AnthropicProvider tested against live API; AI interactions audited
- [ ] F-04.3: All 5 AI operations work inline; lawyer advisor signs off on AR output quality
- [ ] F-04.4: Risk flags + fallback chains work
- [ ] All Pest tests green
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated
- [ ] **The wedge demo passes:** lawyer advisor + 2 pilot firms shown a live demo — produces "this is different from HAQQ" response
- [ ] All `[PENDING-LEGAL-REVIEW]` prompt templates + disclaimers signed off
- [ ] Sign-off: Founder + Legal Advisor + ≥ 2 pilot firms

---

## Out of scope for this Surge

- Fine-tuning a custom model (Year-2 once we have proprietary interaction data)
- RAG over external statute corpus (Year-2; flagged for re-decision in Wedge A path)
- Multi-step agents / chain-of-thought visible to user (Year-2)
- AI-generated full contracts from scratch with structured intake (e.g., "draft me an NDA between X and Y") — Year-2 onboarding flow
- Comparison of clauses across multiple matters (Year-2)
- Compliance / regulatory scanning (Year-2)
- Voice input for AI prompts (out of MVP entirely)
- Per-user prompt customization (Year-2)

---

## What the Software Engineer agent should produce

Same template plus:

1. A `prompts/` directory committed to repo with one `.md` per prompt template, **each requiring `[LEGAL-REVIEW]` tag and sign-off line filled before merge**
2. A cost dashboard view in Filament (read-only): `AiUsageResource` showing per-workspace token spend over time
3. **Hard rule:** No prompt template ships to production without the legal advisor signing off in the file's header
4. A separate cost-guardrail middleware that rejects requests if workspace daily token cap is exceeded
5. **Critical test:** a Pest browser test that exercises the FULL wedge demo flow — import a real bilingual contract, ask AI to review a clause in Arabic, accept the suggestion, export to .docx. This test gates Surge sign-off.
