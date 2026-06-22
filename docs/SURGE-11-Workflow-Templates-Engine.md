# SURGE-11 — Workflow & Templates Engine

**Surge ID:** S-11
**Name:** Workflow & Templates Engine
**Type:** BUILD Surge (platform features, deferred from MVP)
**Estimated duration:** 10–14 days (Claude Code-accelerated)
**Depends on:** SURGE-10 complete
**Pivot reference:** `decisions/D-09_breadth_pivot.md`

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — pure platform features |
| Legal domain | `[ADVISOR-REVIEW-RECOMMENDED]` for any built-in legal-document templates included as seed data |
| Sign-off | PENDING |
| Complexity warning | **This Surge implements platform/runtime features (rule engine, expression evaluator, automation triggers). Scope is genuinely large. The agent may report "done" quickly, but real-world robustness will require iteration based on customer use. Treat the agent's "done" as v0.5, not v1.0.** |

---

## Goal

Add the two configurable-platform features HAQQ has and we previously excluded:

1. **Form Templates engine** — workspace admins can define custom forms for capturing structured data on entities (e.g., "New Client Onboarding" form, "Settlement Tracker" form, custom Matter intake)
2. **Automations engine** — workspace admins can define rules: "when X happens, do Y" (e.g., "when a Matter status changes to Closed-Won, create an Invoice from the time entries")

By the end of this Surge:

- A workspace admin can create a Form Template via a form-builder UI
- Forms can be attached to any tenant entity as additional structured data
- An admin can define automations with triggers (entity events) and actions (create entity, transition task stage, send email, generate document)
- A small library of seed automations covers common patterns

---

## Honest assessment up front

This Surge is the closest the project comes to building a low-code/no-code platform. The reason CLAUDE.md v1 originally excluded these was that:

- A real automation engine is a multi-year product (Zapier, n8n, HubSpot Workflows — these are entire companies)
- A real form-builder is also large (Typeform, JotForm)
- Their value is mostly to "power users" — small firms rarely configure their own workflows
- 80% of the user value comes from sensible hardcoded defaults

We're proceeding anyway because the founder has chosen the breadth strategy. But: **what we ship here will be v0.5 not v1.0.** Real production robustness — handling 1000+ rule evaluations per minute, complex conditional logic, error recovery, retry policies, observability — takes years of iteration. Expect bugs. Expect customer feedback to drive the next 3 Surges of refinement. Set founder expectations accordingly.

---

## Flows

### F-11.1 — Form Templates engine

**Goal:** A workspace admin can design a Form Template (a structured collection of fields), attach it to one or more entity types, and capture instances of that template per entity record.

**Scope:**

- `form_templates` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `name_ar` VARCHAR(200)
  - `name_en` VARCHAR(200)
  - `description` TEXT nullable
  - `applies_to_entity_type` VARCHAR(100) nullable (`matter`, `contact`, `document`, etc.; null = global)
  - `is_active` BOOLEAN default true
  - `version` INT default 1
  - audit timestamps + soft deletes + audit users

- `form_template_fields` table:
  - `id` ULID
  - `form_template_id` ULID FK (cascade)
  - `key` VARCHAR(100) (stable identifier within template)
  - `label_ar` VARCHAR(200)
  - `label_en` VARCHAR(200)
  - `field_type` ENUM('text','textarea','number','currency','date','datetime','boolean','select','multiselect','radio','checkbox','file','rich_text','contact_picker','matter_picker','user_picker')
  - `is_required` BOOLEAN default false
  - `default_value` JSON nullable
  - `options` JSON nullable (for select/radio/checkbox — list of options with AR/EN labels)
  - `validation_rules` JSON nullable (e.g., `{ "max_length": 255, "min_value": 0 }`)
  - `help_text_ar` TEXT nullable
  - `help_text_en` TEXT nullable
  - `sort_order` INT
  - `is_pii` BOOLEAN default false (flagged fields get extra audit logging)
  - composite unique `(form_template_id, key)`

- `form_submissions` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `form_template_id` ULID FK (which template version was used)
  - `template_version_at_submission` INT (snapshot — for traceability if template changes later)
  - `submittable_type` VARCHAR(100) (`matter`, `contact`, etc.)
  - `submittable_id` ULID
  - `submitted_by_user_id` ULID FK
  - `submitted_at` TIMESTAMP
  - `values` JSON (the actual data — keys map to `form_template_fields.key`)
  - audit timestamps + soft deletes + audit users
  - index `(submittable_type, submittable_id)`, `(form_template_id)`

- Models: `FormTemplate`, `FormTemplateField`, `FormSubmission`
- Service: `FormTemplateValidationService::validate($template, $values)` — applies the JSON validation rules to submitted values; returns Laravel ValidationException-compatible errors
- Filament `FormTemplateResource` (Owner/Admin only) — Repeater of fields with conditional config per field_type
- Filament Relation Manager on Matter, Contact, etc.: "Form Submissions" tab showing all submissions for entity instances of that type
- Custom Livewire component: render a form template as a live form, submit handler invokes validation service

**Tests:**
- Pest: form template CRUD
- Pest: form submission validation rejects values violating rules (required, min, max, type)
- Pest: form template version frozen at submission time
- Pest: workspace isolation
- Pest: PII-flagged fields generate audit log entries

**API:** Full CRUD on templates and submissions

**Acceptance:** Founder creates a "Quick KYC" form template via Filament, attaches it to Contacts, submits data for a contact, sees the submission in the Contact detail tab.

---

### F-11.2 — Automations engine — triggers, conditions, actions

**Goal:** A workspace admin defines automation rules: when a trigger event occurs and conditions are met, execute one or more actions.

**Scope:**

- `automations` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `name_ar` VARCHAR(200)
  - `name_en` VARCHAR(200)
  - `description` TEXT nullable
  - `trigger_event` VARCHAR(100) (e.g., `matter.created`, `matter.status_changed`, `obligation.due_in_7_days`, `invoice.paid`, `task.transitioned`)
  - `conditions` JSON (an expression tree — see Conditions section below)
  - `is_active` BOOLEAN default true
  - `run_count` INT default 0 (audit)
  - `last_run_at` TIMESTAMP nullable
  - audit timestamps + soft deletes + audit users

- `automation_actions` table:
  - `id` ULID
  - `automation_id` ULID FK (cascade)
  - `sort_order` INT
  - `action_type` VARCHAR(100) (`create_task`, `transition_task`, `create_obligation`, `send_email`, `generate_document`, `create_invoice_from_time_entries`, `assign_to_user`, `notify_user`, `set_field`)
  - `action_payload` JSON (action-specific parameters)
  - `stop_on_error` BOOLEAN default true (if true, fail the automation; if false, continue to next action)

- `automation_runs` table (append-only audit):
  - `id` ULID
  - `workspace_id` ULID FK
  - `automation_id` ULID FK
  - `trigger_payload` JSON (the event data that fired the run)
  - `conditions_evaluation` JSON (what conditions evaluated to)
  - `actions_executed` JSON (list of actions attempted + outcomes)
  - `status` ENUM('success','partial','failed')
  - `error_message` TEXT nullable
  - `duration_ms` INT
  - `created_at` TIMESTAMP — no updated_at (append-only)

- **Trigger event system:** Laravel events. Each significant model event raises a corresponding event (`MatterCreated`, `MatterStatusChanged`, `ObligationDueIn7Days`, etc.). An `AutomationEventListener` subscribes to all of these, queries active automations for matching `trigger_event`, evaluates conditions, executes actions.

- **Conditions** — a JSON expression tree supporting:
  - Comparison: `{ "op": "eq", "left": "matter.status", "right": "active" }`
  - Boolean: `{ "op": "and", "operands": [...] }`, `{ "op": "or", ...}`, `{ "op": "not", ...}`
  - Negation, comparison operators (`eq`, `neq`, `gt`, `lt`, `gte`, `lte`, `in`, `contains`, `is_null`, `is_not_null`)
  - References to trigger payload via `trigger.<path>` syntax (e.g., `trigger.matter.value`)
  - Service: `ConditionEvaluatorService::evaluate($condition, $context)` — recursive evaluator with type coercion and security boundaries (no arbitrary code execution)

- **Actions** — each `action_type` has a corresponding handler class in `app/Services/AutomationActions/`:
  - `CreateTaskAction`, `TransitionTaskAction`, `CreateObligationAction`, `SendEmailAction`, `GenerateDocumentAction` (uses SURGE-10 F-10.4), `CreateInvoiceFromTimeEntriesAction`, etc.
  - Each handler has its own validation, retry policy, and error reporting

- **Async execution:** Automations run in a dedicated queue (`automations`) — never synchronously on the request thread. Slow automations don't block UX.

- **Loop prevention:** An automation cannot directly or transitively trigger itself within the same trigger chain. Tracked via `automation_run_chain_id` token passed through events; depth limit of 5 transitive triggers.

- **Filament `AutomationResource`** (Owner/Admin only) — form builder for trigger event, conditions (JSON editor with schema validation), actions (Repeater with conditional fields per action_type)
- **Test mode:** simulate an automation's execution against a sample trigger payload without actually executing actions — useful for debugging

- **Seed automations** (workspace creation default — Owner/Admin can disable):
  - "Create renewal reminder Task when Contract Metadata's renewal_date is set" → action: CreateTask
  - "Send notification when Matter assigned to me" → action: NotifyUser
  - "Create Invoice from billable time when Matter status changes to Closed-Won" → action: CreateInvoiceFromTimeEntries

**Authorization:**
- Owner/Admin only can create/edit/delete automations
- Member can view active automations (for transparency)

**Tests:**
- Pest: condition evaluator handles all operators correctly
- Pest: trigger event fires automation correctly
- Pest: condition false → automation does not run actions
- Pest: failed action with `stop_on_error=true` halts subsequent actions and marks run as failed
- Pest: failed action with `stop_on_error=false` continues
- Pest: loop prevention catches self-triggering chain at depth 5
- Pest: automation_runs audit table records full execution detail
- Pest: workspace isolation
- Pest: test mode does not actually execute actions
- Pest: async execution via queue (not on request thread)

**API:**
- Full CRUD on automations
- `POST /api/v1/automations/{id}/test` → runs in test mode with provided sample payload, returns execution trace

**Acceptance:** Founder creates an automation "When Matter is created with practice_area=commercial_contracts, create a Task on it titled 'Send engagement letter'" via Filament, verifies it fires correctly on next Matter create.

---

### F-11.3 — Document Templates (extension to F-10.4)

**Goal:** Beyond the AI generation templates from SURGE-10 F-10.4, allow user-defined Document Templates that are NOT AI-generated but are skeleton documents users can clone. (HAQQ has this for static templates like "Standard NDA Form".)

**Scope:**

- `document_templates` table:
  - `id` ULID
  - `workspace_id` ULID FK nullable (null = system template; non-null = workspace-specific)
  - `name_ar` VARCHAR(200)
  - `name_en` VARCHAR(200)
  - `description` TEXT nullable
  - `document_type` ENUM matches Document's document_type
  - `language` ENUM('ar','en','bilingual')
  - `body` LONGTEXT (TipTap JSON — the template content with placeholder tokens like `{{client_name}}`)
  - `placeholder_schema` JSON (declares which placeholders this template uses + their types)
  - `is_active` BOOLEAN default true
  - audit timestamps + soft deletes + audit users

- Service: `DocumentTemplateService::createFromTemplate($template, $matter, $replacements)` — clones template, replaces placeholders with provided values, creates Document + Version 1 + extracts clauses
- Filament `DocumentTemplateResource` (Owner/Admin manage; Members use)
- Filament action on Matter: "New Document from Template" — picks template, fills placeholder form, creates Document
- Seeded system templates (founder-decided defaults — flag for advisor review):
  - "Standard Mutual NDA (Levant)" — bilingual
  - "Engagement Letter Template"
  - "Service Agreement Skeleton"
  - "Demand Letter Template"
  - "Settlement Agreement Skeleton"

**`[ADVISOR-REVIEW-RECOMMENDED]`** items:
- Each seeded template's content — these go in front of clients; they need to be lawyer-vetted before production use
- The placeholder schema for each (which fields are required)

**Tests:**
- Pest: template creation works
- Pest: cloning a template with placeholders produces a Document with all placeholders correctly replaced
- Pest: missing required placeholder rejected with 422
- Pest: workspace-specific template overrides system template for same name

**API:** Full CRUD on templates; `POST /api/v1/matters/{id}/documents/from-template`

**Acceptance:** Founder clones an NDA template, fills in two party names, creates Document with both party names substituted throughout.

---

### F-11.4 — Workflow Templates Library

**Goal:** A small library of pre-configured workflow patterns combining F-10.1 (task workflows), F-11.2 (automations), F-11.3 (document templates) for common firm operations.

**Scope:**

- Seeded workflow bundles (Owner can activate per workspace):
  - **"New Client Onboarding"**: KYC checklist (S-07) + Tasks workflow (drafting engagement letter, conflict check) + Document template (engagement letter) + automation (send welcome email)
  - **"Contract Drafting"**: Task workflow with stages (Drafting → Internal Review → Counterparty → Signed) + Document template (NDA or supply agreement) + automation (move to next stage when document marked as ready)
  - **"Litigation Matter Setup"**: Task workflow + hearing reminder automation + service-of-process tracking task

- Each bundle is described in a JSON manifest in `database/seeders/workflow_bundles/*.json`
- Filament Page: "Workflow Library" — Owner clicks "Activate" on a bundle → creates all the necessary task workflows, automations, document templates in the workspace
- Activation is idempotent (re-activating overwrites if user confirms; otherwise no-op)

**Tests:**
- Pest: activating a bundle creates all expected sub-entities
- Pest: re-activating without confirm is a no-op
- Pest: deactivating a bundle removes the sub-entities (or marks them inactive)
- Pest: workspace isolation

**Acceptance:** Founder activates "Contract Drafting" workflow bundle; verifies that task workflow, automation, and document template all appear correctly configured.

---

## Surge acceptance criteria

- [ ] F-11.1: Form templates work; submissions validated against rules
- [ ] F-11.2: Automations engine works; condition evaluator passes all test cases; loop prevention works
- [ ] F-11.3: Document templates work; placeholder substitution correct
- [ ] F-11.4: Workflow bundles activatable
- [ ] All Pest tests green
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated (~25 new endpoints)
- [ ] No regression in S-01 to S-10 tests
- [ ] All `[ADVISOR-REVIEW-RECOMMENDED]` items inventoried for future lawyer review

---

## Out of scope

- A visual workflow designer with drag-and-drop nodes (Year-2 if user demand)
- Cross-workspace template marketplace (Year-2)
- Scheduled/cron triggers for automations beyond hourly granularity (Year-2)
- Webhook actions (incoming webhooks as triggers; outgoing webhooks as actions) (Year-2)
- Conditional branching within an automation's action list (if/then within action sequence) — Year-2
- Loop constructs in automations (Year-2)
- Custom code/script actions — never (security)
- Multi-step user prompts during automation execution — Year-2
- A/B testing of automations — Year-2

---

## What the Software Engineer agent should produce

1. **For F-11.2 condition evaluator:** unit-test exhaustively. Every operator, every type coercion, every edge case (null operand, type mismatch, deeply nested expressions). This is the heart of the engine; bugs here corrupt every automation downstream.

2. **For F-11.2 action handlers:** each action handler is its own class with its own test file. They must be independently invocable (so test mode can simulate one action at a time).

3. **For F-11.4 workflow bundles:** the bundle JSON manifests are the contract. Each manifest must validate against a schema. Adding a new bundle = adding a JSON file + a manifest schema test.

4. **Critical performance constraint:** automations are evaluated on every domain event. For a workspace with 50 automations and a Matter create event, 50 evaluations run. Cache the per-workspace active automation list aggressively (Redis, TTL 5 min, invalidate on Automation save).

5. **Observability:** every automation run logs to `automation_runs`. The Filament `AutomationResource` shows a run-history tab per automation. This makes the system debuggable when it inevitably misbehaves.
