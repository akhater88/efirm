# SURGE-10 — Tasks Workflow + Board UI + AI Document Generation

**Surge ID:** S-10
**Name:** Tasks Workflow + Board + AI Document Generation
**Type:** BUILD Surge (feature expansion, post-SURGE-09)
**Estimated duration:** 8–12 days (Claude Code-accelerated)
**Depends on:** SURGE-01 through SURGE-09 reported complete
**Pivot reference:** `decisions/D-09_breadth_pivot.md`

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — extends existing surfaces |
| Legal domain | `[PENDING-LEGAL-REVIEW]` for new AI document-generation prompt templates (additional templates beyond the 5 already in `prompts/`) |
| Sign-off | PENDING |
| Verification dependency | **This Surge ships on top of SURGE-09. The verification work in SURGE-VERIFY is recommended before SURGE-10 to avoid building on hidden bugs. Founder has elected to proceed without verification.** |

---

## Goal

Add three specific feature extensions the founder requested:

1. **Tasks gain a workflow layer** — configurable stages, transitions with rules, approval gates on transitions
2. **Tasks gain a board view** — kanban with drag-and-drop, swim lanes, filters; replaces the basic Filament table for the primary task surface
3. **AI document generation** — a new AI capability beyond clause-level operations: generate a full contract from structured intent (parties, deal type, governing law, key terms), then drop the result into the editor

By the end of this Surge:

- A workspace can define custom Task workflows (e.g., "Contract Review Workflow" with stages Drafting → Internal Review → Counterparty Review → Approval → Closed)
- Different Task types can use different workflows
- A board view exists alongside the table view; users can drag tasks between stages
- A lawyer can click "Generate Contract" on a Matter, fill a structured form (deal type, parties, key terms, governing law), and receive an AI-drafted full contract as a new Document — bilingual where applicable

---

## Flows

### F-10.1 — Task Workflows (definition layer)

**Goal:** A workspace can define multiple Task Workflows. Each workflow is a directed graph of stages with allowed transitions.

**Scope:**

- `task_workflows` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `name` VARCHAR(100)
  - `description` TEXT nullable
  - `is_default` BOOLEAN default false (one default per workspace)
  - `applies_to_task_type` VARCHAR(100) nullable (when null, applies to all; when set, only Tasks with matching type)
  - audit timestamps + soft deletes + audit users

- `task_workflow_stages` table:
  - `id` ULID
  - `task_workflow_id` ULID FK (cascade on delete)
  - `name_ar` VARCHAR(100)
  - `name_en` VARCHAR(100)
  - `key` VARCHAR(50) (stable identifier, e.g., 'drafting', 'review', 'approval', 'closed')
  - `sort_order` INT
  - `is_initial` BOOLEAN default false (exactly one per workflow)
  - `is_terminal` BOOLEAN default false (at least one per workflow)
  - `color` VARCHAR(20) (Tailwind color name, e.g., 'gray', 'blue', 'green', 'red')
  - `requires_approval` BOOLEAN default false (transitions INTO this stage require approval)
  - composite unique `(task_workflow_id, key)`

- `task_workflow_transitions` table:
  - `id` ULID
  - `task_workflow_id` ULID FK
  - `from_stage_id` ULID FK
  - `to_stage_id` ULID FK
  - `requires_role` ENUM nullable — `owner|admin|member` — minimum role to perform transition
  - `requires_approval_by_user_id` ULID FK nullable (specific user must approve)
  - `auto_transition_after_hours` INT nullable (auto-advance after N hours in source stage)
  - composite unique `(task_workflow_id, from_stage_id, to_stage_id)`

- `task_workflow_approvals` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `task_id` ULID FK
  - `from_stage_id` ULID FK
  - `to_stage_id` ULID FK
  - `requested_by_user_id` ULID FK
  - `approver_user_id` ULID FK (who must approve)
  - `status` ENUM('pending','approved','rejected','cancelled')
  - `responded_at` TIMESTAMP nullable
  - `notes` TEXT nullable
  - audit timestamps

- Update `tasks` table (migration `add_workflow_fields_to_tasks_table`):
  - `task_workflow_id` ULID FK nullable (nullable so existing tasks aren't broken)
  - `current_stage_id` ULID FK nullable
  - DEPRECATE the existing `status` enum field — keep for backward compat but new tasks use stages
  - When `task_workflow_id` is null, fall back to legacy status enum behavior

- Models: `TaskWorkflow`, `TaskWorkflowStage`, `TaskWorkflowTransition`, `TaskWorkflowApproval`
- Service: `TaskTransitionService::transition($task, $toStage, $user)` — validates allowed transitions, role permissions, approval requirements; creates approval record if needed; raises `TaskStageTransitioned` event
- Service: `TaskApprovalService::approve($approvalId, $approver)` / `reject($approvalId, $approver, $reason)`
- Job: `AutoTransitionStaleTasksJob` (scheduled hourly) — moves tasks whose `auto_transition_after_hours` has elapsed
- Filament `TaskWorkflowResource` (Owner/Admin only) — Repeater for stages, Repeater for transitions, default seeding helpers
- Filament `TaskWorkflowApprovalResource` — shows pending approvals to approvers
- Default workflows seeded on workspace creation:
  - "Generic Workflow": To-Do → In Progress → Done
  - "Contract Review Workflow": Drafting → Internal Review (requires approval) → Counterparty Review → Approval (requires approval) → Closed
  - "Litigation Task Workflow": To-Do → In Progress → Blocked → Done

**Authorization rules:**
- Only Owner/Admin can create/edit Workflows
- Members can use existing Workflows
- Transitions respect `requires_role` per-transition

**Tests:**
- Pest: transition from valid stages persists
- Pest: transition to disallowed stage rejected (422)
- Pest: approval-required transition creates approval record + does not advance task
- Pest: approval grant advances task
- Pest: approval rejection leaves task at source stage
- Pest: auto-transition job runs correctly
- Pest: workspace isolation across all 4 new tables
- Pest: cannot delete a stage that has tasks currently in it (CHECK or business-rule violation)
- Pest: backward compat — existing tasks without `task_workflow_id` still work via legacy status

**API:** Full CRUD on workflows + endpoints:
- `POST /api/v1/tasks/{id}/transitions` → request a transition; returns task or pending-approval record
- `POST /api/v1/task-approvals/{id}/respond` → approve/reject

**Acceptance:** All tests green; founder can create a workflow with 4 stages and 1 approval gate via Filament; transitions work end-to-end.

**Dependencies:** SURGE-07 F-07.1 (Tasks) complete.

---

### F-10.2 — Task Board (kanban view)

**Goal:** A board view alongside the existing Filament table view. Users see Tasks as cards in columns (stages), drag-and-drop between columns to transition.

**Scope:**

- Filament Page: `app/Filament/Pages/TasksBoard.php`
- Livewire component handles drag-and-drop (Filament v5 supports custom Livewire components inside pages)
- Drag library: SortableJS via `livewire-sortable` package or Filament's built-in if available
- Board renders columns based on the **currently filtered Task Workflow's stages** (default workflow if no filter)
- Each card shows: title, assignee avatar, due date, priority badge, parent entity (e.g., "On Matter: Acme SPA")
- Card click opens a Filament-style edit modal for the Task
- Drag from column A to column B:
  - If transition allowed and no approval needed → invoke `TaskTransitionService` → task moves
  - If approval needed → show modal asking for note → create approval record → card stays in source column with "pending approval" badge
  - If transition not allowed → snap card back, show inline error
- Top toolbar: workflow picker (which workflow's stages to show), filter by assignee, filter by parent entity, filter by priority
- Swim lanes (optional toggle): group rows by assignee, OR by Matter, OR none
- Empty stage shows a small placeholder
- Bilingual: column headers in current locale; card content respects per-locale rendering; RTL flips column order

**Performance:**
- Server returns ≤ 200 cards per stage on initial load
- Pagination per column for stages with > 200 cards ("Show more" link)
- Drag-and-drop uses Livewire actions — no full page reload
- Optimistic UI: card moves immediately; rolls back on server reject

**Tests:**
- Pest browser test: drag card from "To Do" to "In Progress" via SortableJS simulation, verify backend updated
- Pest browser test: drag to disallowed stage, verify card returns to source
- Pest browser test: approval-gated drag opens modal, creates approval record
- Pest browser test: board renders correctly in AR (columns flow RTL)
- Pest browser test: filter by assignee shows only that user's tasks
- Pest browser test: swim lanes group correctly

**Acceptance:** Board works smoothly in both directions, drag-drop persists, approval gating respected, filters work.

**Dependencies:** F-10.1.

---

### F-10.3 — Task Board on Matter detail (embedded)

**Goal:** Inside a Matter, show a Tasks tab with the same kanban board scoped to that Matter only.

**Scope:**

- Add "Tasks" tab to Matter detail page (next to Documents, Parties, Obligations, Hearings if litigation, Time)
- Reuse the F-10.2 Livewire component, parameterized with `taskable_type='matter'`, `taskable_id={matter_id}`
- Workflow picker defaults to Matter's `practice_area`-matched workflow if one exists, otherwise default workflow

**Tests:**
- Pest browser test: open Matter detail, navigate to Tasks tab, see only that Matter's tasks
- Pest browser test: drag-drop on the embedded board updates only this Matter's tasks
- Pest: workspace isolation enforced on the embedded view

**Acceptance:** Tasks tab embedded successfully; doesn't conflict with global Tasks Board.

**Dependencies:** F-10.2.

---

### F-10.4 — AI Document Generation — intake form & service

**Goal:** A lawyer fills a structured form ("Generate Contract"), the AI produces a full contract draft, and it's saved as a new Document with Version 1 populated by the AI output.

**Scope:**

- New entity `ai_document_generations` table (audit + replay):
  - `id` ULID
  - `workspace_id` ULID FK
  - `matter_id` ULID FK (the matter this generation belongs to)
  - `user_id` ULID FK (who requested)
  - `template_key` VARCHAR(100) — which template was used (e.g., 'nda_levant', 'spa_jordan', 'supply_agreement')
  - `intent_payload` JSON — the structured intake (parties, deal terms, governing law, language, special clauses)
  - `prompt_used` TEXT — the actual prompt sent to LLM
  - `model_used` VARCHAR(100)
  - `input_tokens` INT
  - `output_tokens` INT
  - `cost_usd` DECIMAL(10,6)
  - `latency_ms` INT
  - `generated_document_id` ULID FK nullable (the Document created from this generation)
  - `status` ENUM('queued','generating','complete','failed','cancelled')
  - `error_message` TEXT nullable
  - audit timestamps + audit users (no soft delete — append-only audit)

- Service: `AiDocumentGenerationService::generate($intent, $matter, $user): AiDocumentGeneration`
  - Builds prompt from template + intent
  - Calls `LlmProvider` (existing abstraction from SURGE-04 F-04.2)
  - Receives full document content as structured editor JSON (NOT plain text — the prompt instructs the model to return TipTap-compatible JSON)
  - Creates Document + DocumentVersion in the target Matter
  - Runs ClauseExtractionService on the new content
  - Persists `ai_document_generations` audit row
- Templates live in `prompts/document_generation/` directory:
  - `nda_levant.md` — Jordanian/Lebanese/Palestinian/Iraqi NDA generation
  - `spa_jordan.md` — Jordan-law share purchase agreement
  - `supply_agreement_levant.md` — supply agreement
  - `services_agreement_levant.md` — professional services
  - `commercial_lease_levant.md` — commercial lease
  - Each template has the required `[LEGAL-REVIEW-PENDING: <date>]` header until lawyer signs off; until signed, generation refuses to run in production (CI gates this; in development a `force=true` flag bypasses for testing)
- All templates carry the same disclaimer footer baked into the prompt: instructions to the model to include "AI-generated draft. Review before use. Not legal advice." at the end of every generated document

- Intake form is a Filament Page with structured fields:
  - Template picker (dropdown of available templates)
  - Conditional fields per template (templates declare their parameters via a schema attribute on each template file)
  - Common fields: language (AR/EN/bilingual), governing law, parties, key terms
  - Generate button → invokes the service → shows progress → on completion, redirects to the new Document in editor
- Streaming response: while generating, show progressive output token-by-token via SSE or chunked transfer

**Authorization:**
- Any workspace member can use AI generation on a Matter they have access to
- Generation counts against the workspace's daily AI token cap (existing from SURGE-04)
- Generated documents follow normal Document policies for access

**Tests:**
- Pest: Mock LlmProvider returns deterministic output → generation creates Document with Version 1 containing that output
- Pest: Generation persists audit row with prompt, response, tokens, cost
- Pest: Generation respects token budget — denies if workspace over daily cap
- Pest: Failed generation persists `status=failed` with error message; does NOT create Document
- Pest: Concurrent generations on same Matter don't conflict
- Pest: Generated Document is workspace-scoped (cannot be created cross-workspace)
- Pest browser test: full flow — fill intake form, click Generate, see streaming, end on Document editor
- Pest: production-mode refuses to run if template header is `[LEGAL-REVIEW-PENDING]`

**`[PENDING-LEGAL-REVIEW]`** items (entire Flow blocked from production until each template signed):
- nda_levant prompt template
- spa_jordan prompt template
- supply_agreement_levant prompt template
- services_agreement_levant prompt template
- commercial_lease_levant prompt template
- The disclaimer footer text

**API:** `POST /api/v1/matters/{matter_id}/ai/generate-document` — body: `{ template_key, intent_payload }` → returns `AiDocumentGeneration` resource, streams generation, returns `Document` on completion.

**Acceptance:** All tests green; founder can generate an NDA from intake form in development; production deployment refuses until templates signed.

**Dependencies:** SURGE-03 (Document, Editor), SURGE-04 (LlmProvider, AI infrastructure).

---

### F-10.5 — AI Document Generation — template management

**Goal:** Owner/Admin can view, edit, and version the document generation templates inside the product. Templates are not just source code — they're product configuration.

**Scope:**

- New entity `ai_generation_templates` table (in-DB shadow of `prompts/document_generation/`):
  - `id` ULID
  - `workspace_id` ULID FK nullable (NULL = system template, available to all workspaces; non-null = workspace-specific override)
  - `key` VARCHAR(100)
  - `name_ar` VARCHAR(200)
  - `name_en` VARCHAR(200)
  - `description_ar` TEXT nullable
  - `description_en` TEXT nullable
  - `prompt_template` TEXT (the actual prompt with `{{placeholder}}` syntax for intent values)
  - `intent_schema` JSON (declares which intent fields this template requires)
  - `version` INT default 1
  - `legal_review_status` ENUM('pending','approved','revoked')
  - `legal_review_approver_name` VARCHAR(200) nullable
  - `legal_review_approver_date` DATE nullable
  - `is_active` BOOLEAN default true
  - audit timestamps + soft deletes + audit users

- Seed system templates from the `prompts/document_generation/` directory on app boot or via seeder
- Filament `AiGenerationTemplateResource` (Owner/Admin only):
  - List system templates (read-only for non-admins; admins can clone to workspace-specific)
  - Create workspace-specific templates (clone-then-edit pattern)
  - View prompt source; edit prompt for workspace overrides
  - Set `legal_review_status` (Owner/Admin can flag as pending review; only specific role/user can flag as approved — `[PENDING-LEGAL-REVIEW]` for who has authority to mark approved; default: only Owner with attestation)
- The `AiDocumentGenerationService` reads from DB first (workspace-specific override), falls back to system template
- Production refuses to use any template where `legal_review_status != 'approved'`

**`[PENDING-LEGAL-REVIEW]`** items:
- Who has authority to set `legal_review_status='approved'`? Default: Owner role with a written attestation field. Real answer requires lawyer advisor input.

**Tests:**
- Pest: workspace-specific template overrides system template for same key
- Pest: production refuses non-approved templates
- Pest: editing a workspace template doesn't affect system templates or other workspaces
- Pest: version increment on edit

**API:** Full CRUD on templates (Admin/Owner only)

**Acceptance:** Founder can clone a system template, edit it, mark it approved (with attestation), and use it.

**Dependencies:** F-10.4.

---

### F-10.6 — Document Generation Library / History

**Goal:** Lawyers can see history of all AI generations on a Matter — what was generated, what was kept, what was discarded.

**Scope:**

- Filament Relation Manager on Matter: "AI Generations" tab
  - Lists all `ai_document_generations` for the Matter
  - Columns: template used, requested by, status, token cost, generated document link
  - Action: "Regenerate" — re-runs the same intent with the latest template (creates new generation; original audit row preserved)
- Workspace-level "AI Usage" Filament Page (Owner/Admin only): cost dashboard showing all generations + clause-level operations (from S-04 audit table), grouped by month, by user, by template
- Cost guardrail: daily and monthly cost caps per workspace (configurable in workspace settings)
- Email alert to Owner when workspace hits 80% of monthly cap

**Tests:**
- Pest: generation history displays correctly per Matter
- Pest: regenerate creates new audit row + new Document; original preserved
- Pest: cost dashboard aggregates correctly
- Pest: cost cap email dispatches at 80% threshold

**Acceptance:** Lawyer can audit AI usage and costs; founder gets warnings before bill blows up.

**Dependencies:** F-10.4, F-10.5.

---

## Surge acceptance criteria

- [ ] F-10.1: Task workflows definable; transitions enforce rules; approvals work
- [ ] F-10.2: Task Board (kanban) usable; drag-drop persists; bilingual RTL works
- [ ] F-10.3: Embedded board on Matter detail works
- [ ] F-10.4: AI document generation works end-to-end in development; tests cover happy path + 3 sad paths; production refuses non-approved templates
- [ ] F-10.5: Template management UI works; legal_review_status enforced
- [ ] F-10.6: Generation history + cost dashboard works
- [ ] All Pest tests green
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated (~15 new endpoints)
- [ ] No regression in S-01 to S-09 tests
- [ ] All `[PENDING-LEGAL-REVIEW]` items inventoried in `validation/10_ai_generation_lawyer_signoff_pending.md`

---

## Hard stops for production deployment

- All AI document generation templates (`prompts/document_generation/*.md`) require `[LEGAL-REVIEW-APPROVED]` header before production
- The disclaimer text in generated documents requires lawyer signoff
- Template management UI's "approval authority" rule requires lawyer guidance

Build, demo, test internally — all OK without lawyer. Production deployment to real users requires the same hard-stop pattern as SURGE-04, SURGE-06, SURGE-08.

---

## Out of scope for this Surge

- Multi-document workflows (e.g., generate NDA + supply agreement together) — Year-2
- Template marketplace / sharing across workspaces — Year-2
- Fine-tuning custom models on workspace data — Year-2
- AI-extracted obligations from generated documents (still manual entry per S-05) — Year-2
- Voice input to generation intake — out
- AI-driven workflow automation (e.g., "auto-generate NDA when matter is created") — Year-2
- Mobile UI for board view — out (web-only)

---

## What the Software Engineer agent should produce

1. **For F-10.1 workflows**: produce explicit "before/after" test cases showing that existing tasks (created in S-07) continue to work without modification — backward compatibility is critical
2. **For F-10.2 board**: include a performance test ensuring < 500ms render time for a workspace with 500 tasks across 5 stages
3. **For F-10.4 AI generation**: every template file in `prompts/document_generation/` must include the standard legal-review header. The seeder for `ai_generation_templates` table must respect the header status and refuse to mark anything as 'approved' automatically
4. **For F-10.6 cost dashboard**: aggregate query must use cached daily/monthly rollups, not on-the-fly SUM across `ai_interactions` table (which will grow to millions of rows over time)
