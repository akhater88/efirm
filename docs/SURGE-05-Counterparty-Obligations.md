# SURGE-05 — Counterparty & Obligations

**Surge ID:** S-05
**Name:** Counterparty & Obligations
**Type:** BUILD Surge
**Estimated duration:** 6–8 days (with Claude Code); 2 weeks (manual)
**Depends on:** S-02 complete; S-03 complete (no dependency on S-04, can run parallel)
**Enables:** S-06 launch features

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — wedge-agnostic |
| Legal domain | `[PENDING-LEGAL-REVIEW]` — Counterparty data model + Obligation taxonomy + Governing-law list need advisor validation |
| Sign-off | PENDING — Founder + Legal Advisor + ≥ 1 pilot firm |

---

## Goal

Promote the Counterparty concept from a flag-on-Contact into a first-class commercial entity with the fields a transactional lawyer actually tracks: contract value, currency, effective date, term, renewal, governing law, jurisdiction. Add obligation tracking with date-driven reminders.

This Surge closes the Vol. 3 schema gap (HAQQ's Contact has no contract-native fields) and the Vol. 2 schema gap (no obligations, no renewal/expiry, no governing-law on the Matter level).

By the end of this Surge:
- A Matter has a richer Counterparty representation with contract economics + dates + governing law
- Each Document can carry contract-level metadata (effective date, expiry, renewal date)
- Obligations (specific dated commitments) are tracked per Document/Matter
- A lawyer receives email reminders for upcoming renewals + obligations
- A dashboard shows all upcoming deadlines across the workspace

---

## Flows

### F-05.1 — Counterparty position on Matter

**Goal:** Promote the `matter_counterparties` pivot (from S-02) into a richer relationship that carries contract economics and our position in the deal.

**Scope:**
- Modify `matter_counterparties` pivot table — add columns:
  - `counterparty_role` VARCHAR(100) (e.g., "buyer", "seller", "licensor", "licensee", "service_provider", "client")
  - `our_position` ENUM('we_represent', 'they_represent', 'no_counsel', 'mutual')
  - `notes` TEXT nullable
- New `Counterparty` Eloquent **wrapper model** around the pivot (extending `Illuminate\Database\Eloquent\Relations\Pivot`) with helper methods
- Migration: `php artisan make:migration add_position_to_matter_counterparties_table`
- `MatterPolicy` continues to govern access (no separate Counterparty policy needed since it's a pivot)

**Entities touched:** `matter_counterparties` (alter); `Matter` (relationship updates with `withPivot`).

**API surface:**
- `PATCH /api/v1/matters/{matter_id}/counterparties/{contact_id}` — update counterparty_role, our_position, notes
- The attach/detach endpoints from S-02 (`POST/DELETE /api/v1/matters/{id}/counterparties`) extend to accept the new fields

**UI surface:**
- Matter detail page → "Parties" tab now shows richer counterparty info
- Filament `MatterResource` form: counterparties section becomes a `Repeater` with role + position fields
- Bilingual role labels (use a translated lookup, not free text in Wave-Ready Package)

**Key decisions to make in Wave-Ready Package:**
- `[PENDING-LEGAL-REVIEW]` Final `counterparty_role` enum list — advisor input on Levant-typical commercial deal roles
- Role as enum vs free-text — enum is cleaner but inflexible; lean enum + "other" option
- `our_position` enum (already proposed); confirm with advisor

**Acceptance criteria:**
- Pest: attaching a Counterparty with role + position works
- Pest: updating counterparty fields persists
- Pest: cannot set `our_position='they_represent'` if no `opposing_counsel_contact_id` is provided (future field — defer to Year-2)
- Filament repeater renders correctly in AR (RTL)

**Dependencies:** S-02 complete.

---

### F-05.2 — Contract-economic fields on Document

**Goal:** A Document — when it represents a contract — carries contract-level metadata: value, currency, effective date, term, renewal, expiry, governing law.

**Scope:**
- New `contract_metadata` table (one-to-one with `documents` where `document_type='contract'`):
  - `id` UUID
  - `workspace_id` UUID FK
  - `document_id` UUID FK unique
  - `contract_value` DECIMAL(15,2) nullable
  - `contract_currency` CHAR(3) nullable (ISO 4217)
  - `effective_date` DATE nullable
  - `term_months` INT nullable (contract length in months)
  - `expiry_date` DATE nullable (computed from effective + term, or set manually)
  - `auto_renew` BOOLEAN default false
  - `renewal_notice_period_days` INT nullable (e.g., 60 — how many days before expiry the lawyer wants to be notified)
  - `governing_law` VARCHAR(100) nullable (e.g., "Jordan", "Lebanon", "England & Wales", "DIFC", "ADGM")
  - `jurisdiction_clause` VARCHAR(255) nullable (arbitration seat or court)
  - `signed_date` DATE nullable
  - `status` ENUM('draft', 'negotiating', 'awaiting_signature', 'signed', 'expired', 'terminated') — mirrors Document.status but with contract-specific values
  - audit timestamps + soft deletes

- `ContractMetadata` Eloquent model with `BelongsToWorkspace`
- `Document::contractMetadata()` hasOne relationship
- A helper `Document::asContract()` that returns the contract view (or null if document_type != 'contract')
- Auto-computation: when `effective_date` + `term_months` set but `expiry_date` not, compute it

**Entities touched:** `contract_metadata` (new).

**API surface:**
- `GET /api/v1/documents/{id}/contract` — get contract metadata
- `PUT /api/v1/documents/{id}/contract` — upsert contract metadata (uses `UpsertContractMetadataRequest`)

**UI surface:**
- Document detail page → "Contract" tab (visible when `document_type='contract'`)
- Filament `ContractMetadataResource` (read-only summary view); editing happens via Document edit form
- Currency picker: ISO 4217 list (truncated to top 30 relevant currencies + search)
- Governing law picker: curated list (`[PENDING-LEGAL-REVIEW]` for Levant + common international choices)

**Key decisions to make in Wave-Ready Package:**
- `[PENDING-LEGAL-REVIEW]` Governing law list — advisor curates
- Currency list (top 30 + search)
- Date format per locale (DD/MM/YYYY for AR users; DD/MM/YYYY for EN as default — confirm)
- Whether to extract contract metadata from imported .docx automatically using AI (lean: Year-2; manual entry at MVP)
- Hijri vs Gregorian date display (Gregorian only at MVP; flag for Year-2)

**Acceptance criteria:**
- Pest: contract metadata can be set, read, updated
- Pest: expiry_date auto-computed when effective + term given
- Pest: governing_law accepts only values from the curated list (FormRequest validation)
- Pest: workspace isolation
- Filament view renders correctly in AR

**Dependencies:** S-03 (Document) complete.

---

### F-05.3 — Obligations & milestones

**Goal:** A contract carries a set of dated obligations (e.g., "pay $50,000 by 2026-09-01", "deliver Q3 report by 2026-10-15"). Each obligation has a status and a responsible party.

**Scope:**
- `obligations` table:
  - `id` UUID
  - `workspace_id` UUID FK
  - `document_id` UUID FK (the contract this obligation belongs to)
  - `clause_id` UUID FK to `document_clauses` nullable (which clause this obligation is derived from)
  - `title` VARCHAR(255)
  - `description` TEXT nullable
  - `obligation_type` ENUM('payment', 'delivery', 'reporting', 'notification', 'consent', 'other')
  - `responsible_party` ENUM('us', 'counterparty', 'mutual', 'third_party')
  - `responsible_user_id` UUID FK to users nullable (when 'us')
  - `due_date` DATE
  - `reminder_days_before` INT default 7 (days before due_date to send reminder)
  - `status` ENUM('pending', 'in_progress', 'completed', 'overdue', 'waived') default 'pending'
  - `completed_at` TIMESTAMP nullable
  - `completed_by_id` UUID FK nullable
  - `monetary_amount` DECIMAL(15,2) nullable (for payment obligations)
  - `monetary_currency` CHAR(3) nullable
  - `notes` TEXT nullable
  - audit timestamps + soft deletes
  - composite index `(workspace_id, due_date)` for upcoming-deadlines query

- `Obligation` Eloquent model with `BelongsToWorkspace`
- `Document::obligations()` hasMany; `Matter::obligations()` hasManyThrough Document
- Scheduled task (`app/Console/Kernel.php` daily): mark obligations past `due_date` as `overdue`
- AI extension (optional, post-MVP): "Extract obligations from this contract" — uses AI to scan document clauses and propose obligations for user review. **Flag as `[REVISIT-AFTER-AI-TEST]`**

**Entities touched:** `obligations` (new).

**API surface:**
- Full CRUD: `GET/POST /api/v1/documents/{id}/obligations`, `GET/PATCH/DELETE /api/v1/obligations/{id}`
- `POST /api/v1/obligations/{id}/complete` — mark complete
- `POST /api/v1/documents/{id}/obligations/extract` — AI-extract (post-MVP)

**UI surface:**
- Document detail → "Obligations" tab — kanban or table view (default table for MVP)
- Filament `ObligationResource`
- Dashboard widget: "Upcoming Obligations (7 days)"
- Color-coded by status (overdue red, pending yellow, completed green)

**Key decisions to make in Wave-Ready Package:**
- `[PENDING-LEGAL-REVIEW]` Confirm obligation_type enum with advisor
- View default: table vs kanban (MVP: table; kanban Year-2)
- AI extraction: defer to Year-2 (out of S-05 scope; flag)
- Obligations on Matter without a Document? (No at MVP — every obligation belongs to a Document.)

**Acceptance criteria:**
- Pest: CRUD round-trip
- Pest: marking complete sets `completed_at` and `completed_by_id`
- Pest: scheduled task transitions pending → overdue when due_date < today
- Pest: dashboard widget query returns obligations within 7 days
- Filament resource bilingual + RTL-correct

**Dependencies:** S-03; F-05.2 (logical pairing).

---

### F-05.4 — Renewal reminders & deadline notifications

**Goal:** Lawyers receive email reminders for upcoming renewals (from `contract_metadata`) and obligations (from `obligations`). Reminders are bilingual and respect the user's preferred locale.

**Scope:**
- Scheduled task (daily 8:00 AM local-to-workspace, or UTC fallback): scan workspaces, compute reminders due today
- For each reminder:
  - Recipient: the Matter's lead_lawyer; CC the obligation's responsible_user; for contract renewals, lead_lawyer of the parent Matter
  - Content: bilingual Markdown mailable with the relevant details
- Reminder triggers:
  - Contract renewal: `expiry_date - renewal_notice_period_days = today`
  - Obligation: `due_date - reminder_days_before = today`
  - Overdue obligation: daily until completed or waived
- Email deduplication: don't send the same reminder twice on the same day
- User preferences: a `notification_preferences` JSON column on users for opt-out per category (defer detail UI to S-06; MVP: opt-out is admin-only via Filament)

**Entities touched:** `notification_log` table (new — for dedup) with `(user_id, source_type, source_id, sent_on_date)` unique index.

**API surface:**
- None at MVP (notifications are server-driven, not user-triggered)

**UI surface:**
- Email templates (Markdown mailables): `ContractRenewalReminderMail`, `ObligationDueReminderMail`, `ObligationOverdueMail`
- All advisor-reviewed for tone
- Dashboard widget: "Recent Notifications" (read-only audit)

**Key decisions to make in Wave-Ready Package:**
- Local timezone handling — workspaces in Levant get UTC+2/+3; default to workspace timezone (add `workspaces.timezone` column if not yet present)
- Email queue (uses Redis queue from S-01)
- Failure handling: retry 3x with exponential backoff
- Email content (AR + EN) — advisor-approved tone

**Acceptance criteria:**
- Pest: scheduled task identifies the right reminders for a fixture workspace
- Pest: emails are dispatched to the right recipients in the right locale
- Pest: dedup works — same reminder not sent twice
- Manual QA: emails render correctly in Gmail, Outlook, Apple Mail; both AR and EN

**Dependencies:** F-05.2, F-05.3.

---

### F-05.5 — Workspace dashboard rollup

**Goal:** The workspace dashboard (currently empty from S-01 F-01.6) now shows actionable summaries: upcoming obligations, upcoming renewals, recent activity.

**Scope:**
- Dashboard widgets:
  - **Upcoming Obligations** — next 14 days, grouped by week, link to obligation
  - **Upcoming Renewals** — next 60 days
  - **Recent Documents** — 5 most recently updated
  - **My Open Matters** — matters where current user is lead or team member, status=active
  - **AI Usage (Owner/Admin only)** — token spend this month (from S-04 `ai_interactions`)
- Each widget is a Livewire component with its own loading state + empty state
- Bilingual labels + dates
- Empty workspace shows onboarding hints instead of empty widgets

**Entities touched:** None (read-only).

**API surface:**
- Internal Livewire queries; no public API needed at MVP

**UI surface:**
- `resources/views/dashboard.blade.php` rewritten as a grid of Livewire components

**Key decisions to make in Wave-Ready Package:**
- Widget layout (Figma in S-00)
- Caching strategy (5-min cache per widget per user via Redis)
- Mobile layout (defer — desktop primary)

**Acceptance criteria:**
- Dashboard renders without errors on an empty workspace
- Dashboard renders correctly on a populated workspace
- Widgets respect workspace isolation
- All localized

**Dependencies:** F-05.2, F-05.3 (data sources).

---

## Surge acceptance criteria

- [ ] F-05.1: Counterparty enriched on Matter; role + position fields working
- [ ] F-05.2: Contract metadata works; governing law + dates + currency
- [ ] F-05.3: Obligations CRUD + status transitions + dashboard widget
- [ ] F-05.4: Reminder emails dispatch correctly in both locales
- [ ] F-05.5: Dashboard rollup renders correctly
- [ ] All Pest tests green
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated
- [ ] `[PENDING-LEGAL-REVIEW]` items signed off (counterparty roles, governing-law list, obligation taxonomy, email tone)
- [ ] Sign-off: Founder + Legal Advisor + ≥ 1 pilot firm

---

## Out of scope for this Surge

- AI-extraction of obligations from contract text (Year-2; flagged)
- E-signature integration (Year-2)
- Calendar export (.ics) of obligations (Year-2)
- SMS/WhatsApp reminders (out of MVP entirely)
- Slack/Teams integration (Year-2)
- Counterparty contact-of-contact graph (Year-2)
- Conflict-of-interest cross-counterparty checks (Year-2)
- Document-aware obligation suggestion ("this looks like a payment clause; want to track it as an obligation?") — Year-2

---

## What the Software Engineer agent should produce

Same template. Special focus:

1. The `notification_log` table is sensitive — schema must enforce dedup at DB level via unique index
2. Email templates need a separate sign-off line for the legal advisor before merging
3. Scheduled task tests must use Laravel's `Carbon::setTestNow()` to deterministically trigger reminder windows
4. Cache invalidation rules for dashboard widgets (Redis tags by workspace_id)
5. `[PENDING-LEGAL-REVIEW]` items in this Surge are EXPLICITLY blocked from merge until advisor signs off — CI step should refuse to deploy if a PR touches `config/governing_law.php`, `config/counterparty_roles.php`, or `config/obligation_types.php` without an attached signed advisor approval (manual check in PR review at MVP — automated gate Year-2)
