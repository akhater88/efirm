# SURGE-07 — Practice Management Breadth

**Surge ID:** S-07
**Name:** Practice Management Breadth
**Type:** BUILD Surge (post-MVP, breadth-pivot)
**Estimated duration:** 7–10 days (Claude Code-accelerated)
**Depends on:** SURGE-01 through SURGE-06 complete (✓)
**Enables:** SURGE-08, SURGE-09
**Pivot reference:** `decisions/D-09_breadth_pivot.md`

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — breadth additions, depth wedge preserved |
| Legal domain | `[ADVISOR-REVIEW-RECOMMENDED]` for KYC checklist content; other modules lower-risk |
| Sign-off | PENDING — Founder + Legal Advisor (when secured) |
| Hard stops | None at Surge level. KYC checklist content recommended for advisor review but not gated |

---

## Goal

Extend the product from a contract-workspace to a practice-management platform. Add the cross-entity scaffolding (Tasks linking to anything, time entries, smart lists) that subsequent Surges (Litigation in S-08, Financial+CRM in S-09) will rely on.

By the end of this Surge:

- A lawyer can create Tasks attached to any entity (Matter, Contact, Document, Counterparty, Obligation) with a workflow status, assignee, due date
- A lawyer can log time entries against a Matter or Document
- A workspace can run KYC for any Contact: a document checklist with status flags and expiry tracking
- A workspace owner can configure Teams, assign users to teams, and view KPI rollups per team
- All users can save filter combinations as Smart Lists on any entity list

---

## Flows

### F-07.1 — Tasks (cross-entity)

**Goal:** A generic Task entity that can attach to ANY tenant-scoped entity (Matter, Contact, Document, Counterparty, Obligation, Hearing-future, Invoice-future).

**Scope:**

- `tasks` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `title` VARCHAR(255)
  - `description` TEXT nullable
  - `taskable_type` VARCHAR(100) (Eloquent polymorphic — `App\Models\Matter`, `App\Models\Contact`, etc.)
  - `taskable_id` ULID
  - `assigned_to_user_id` ULID FK to `users` nullable
  - `due_date` DATE nullable
  - `priority` ENUM('low','normal','high','urgent') default 'normal'
  - `status` ENUM('todo','in_progress','blocked','done','cancelled') default 'todo'
  - `completed_at` TIMESTAMP nullable
  - `completed_by_user_id` ULID FK nullable
  - `tags` JSON
  - audit timestamps + soft deletes + audit users
  - composite index `(workspace_id, status, due_date)`

- `Task` Eloquent model with `BelongsToWorkspace`, polymorphic `taskable()` relation
- All entity models (Matter, Contact, Document, Counterparty, Obligation) get a `tasks()` morphMany relation
- `TaskPolicy`: members can view/create/update assigned tasks; admins/owners can manage all
- Filament `TaskResource` with kanban view (Filament v5 supports kanban natively)
- Filament Relation Manager on Matter, Contact, Document showing their tasks

**API:** Full CRUD + complete action (`POST /api/v1/tasks/{id}/complete`)

**Acceptance:**
- Pest: tasks attach to all 5 entity types; polymorphic resolution works
- Pest: workspace isolation
- Pest: completing a task sets `completed_at` and `completed_by_user_id`
- Filament kanban renders correctly in AR (RTL — columns flow right-to-left)

---

### F-07.2 — Time tracking

**Goal:** Lawyers log billable time against Matters and Documents. Daily/weekly summaries. No timer UI in this Surge — manual entry first; timer in Year-2 if customers ask.

**Scope:**

- `time_entries` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `user_id` ULID FK
  - `matter_id` ULID FK nullable
  - `document_id` ULID FK nullable
  - `task_id` ULID FK nullable (link to F-07.1 Task)
  - `description` TEXT
  - `duration_minutes` INT
  - `started_at` DATETIME
  - `ended_at` DATETIME
  - `is_billable` BOOLEAN default true
  - `billing_rate_per_hour` DECIMAL(8,2) nullable (snapshot at entry time)
  - `currency` CHAR(3) nullable
  - audit timestamps + soft deletes + audit users
  - At least one of `matter_id`, `document_id`, `task_id` must be set (CHECK constraint)
  - index `(workspace_id, user_id, started_at)`

- `TimeEntry` model with `BelongsToWorkspace`, `BelongsTo` to all parent entities
- `TimeEntryPolicy`: users can manage their own entries; admins can view/edit any in workspace
- Filament `TimeEntryResource` with table grouped by week
- Filament Widget on Matter/Document detail showing total billable time + breakdown by user
- Helper service: `TimeEntryService::summarize($scope, $start, $end)` returns aggregations

**API:** Full CRUD + `GET /api/v1/time-entries/summary?...` aggregation endpoint

**Acceptance:**
- Pest: time entries persist with correct durations
- Pest: cannot save without at least one parent entity reference
- Pest: aggregation endpoint returns correct rollups by user/matter/week
- Filament table sortable + filterable by date range

---

### F-07.3 — KYC workflow `[ADVISOR-REVIEW-RECOMMENDED]`

**Goal:** A document checklist per Contact (Person or Organization), with status tracking and expiry reminders.

**Scope:**

- `kyc_checklists` table (per Contact, optional):
  - `id` ULID
  - `workspace_id` ULID FK
  - `contact_id` ULID FK
  - `status` ENUM('not_started','in_progress','complete','expired','blocked') default 'not_started'
  - `started_at` DATE nullable
  - `completed_at` DATE nullable
  - `next_review_date` DATE nullable
  - `notes` TEXT nullable
  - audit timestamps + soft deletes + audit users

- `kyc_items` table (the line items):
  - `id` ULID
  - `kyc_checklist_id` ULID FK
  - `item_type` ENUM — varies by Contact type (Person vs Organization)
    - Person: `national_id`, `passport`, `address_proof`, `tax_id`, `sanctions_check`, `pep_check`, `source_of_funds_declaration`
    - Organization: `commercial_registration`, `articles_of_association`, `beneficial_owner_declaration`, `bank_certificate`, `authorized_signatories_list`, `sanctions_check`
  - `status` ENUM('not_requested','requested','received','verified','rejected','expired')
  - `expiry_date` DATE nullable
  - `document_id` ULID FK nullable (linked to a Document if uploaded)
  - `notes` TEXT nullable
  - audit timestamps

- `KycChecklist` and `KycItem` models with `BelongsToWorkspace`
- Filament `KycChecklistResource` accessible from Contact detail tab "KYC"
- Filament Widget: KYC status badge on Contact list
- Scheduled task: daily check for items with `expiry_date` ≤ today + 30 days; email reminders to owner

**`[ADVISOR-REVIEW-RECOMMENDED]`** items:
- The item_type enum (do these match Levant regulatory expectations for legal-services KYC?)
- The reminder text content (AR + EN)
- Whether sanctions_check and pep_check should be feature-flagged (these may have data-protection implications)

**API:** Full CRUD on checklists and items; bulk-attach via `POST /api/v1/contacts/{id}/kyc/start`

**Acceptance:**
- Pest: starting a KYC checklist seeds the appropriate items based on contact type
- Pest: completing all items transitions checklist status to `complete`
- Pest: reminder scheduled task identifies the right items
- Filament: status badge renders correctly

---

### F-07.4 — KPI & Targets

**Goal:** Per-user, per-team KPI rollups computed at read-time (no materialized KPI tables). Time-based metrics: billable hours, matter throughput, win rate (where applicable).

**Scope:**

- `kpi_targets` table (the targets, by user OR by team):
  - `id` ULID
  - `workspace_id` ULID FK
  - `targetable_type` VARCHAR(100) (`App\Models\User` or `App\Models\Team`)
  - `targetable_id` ULID
  - `metric` ENUM('billable_hours_monthly','matters_opened_monthly','matters_closed_monthly','revenue_monthly','win_rate')
  - `target_value` DECIMAL(12,2)
  - `period` ENUM('monthly','quarterly','annual')
  - `effective_from` DATE
  - `effective_to` DATE nullable
  - audit timestamps + soft deletes + audit users

- `KpiService`:
  - `getActualValue($user|Team, $metric, $period)` — runs aggregation query against time_entries / matters
  - `getProgressVsTarget($user|Team, $metric, $period)` — returns ratio
  - Cached for 1 hour per (target, period) tuple
- Filament `KpiTargetResource` for managing targets (Owner/Admin only)
- Filament Widget: KPI dashboard widget showing current user's targets + progress
- Filament Page: workspace KPI overview (Owner/Admin only — see all users + teams)

**API:** `GET /api/v1/kpi/my-progress`, `GET /api/v1/kpi/team/{team_id}/progress`, `GET /api/v1/kpi/workspace/overview`

**Acceptance:**
- Pest: setting a target and logging matching time entries produces correct progress
- Pest: KPI queries scoped to workspace (no cross-workspace aggregation possible)
- Pest: cache invalidates on time_entry changes
- Filament widget renders correctly in AR

---

### F-07.5 — Teams Hierarchy

**Goal:** Workspace can have Teams (e.g., "Corporate Practice", "Litigation Practice"); a user can belong to multiple teams. KPIs and tasks can be scoped by team.

**Scope:**

- `teams` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `name` VARCHAR(100)
  - `description` TEXT nullable
  - `lead_user_id` ULID FK nullable (team lead)
  - `parent_team_id` ULID FK self-referential nullable (sub-team support)
  - audit timestamps + soft deletes + audit users

- `team_user` pivot:
  - `team_id` ULID
  - `user_id` ULID
  - composite unique
  - `role_in_team` VARCHAR(50) nullable (e.g., "lead", "senior_associate", "associate")

- `Team` model with `BelongsToWorkspace`, `members()` belongsToMany, `parentTeam()` self-relation
- `TeamPolicy`: Owner/Admin manage; Members view their own teams
- Filament `TeamResource` with Relation Manager for members
- Matter gets optional `responsible_team_id` field (additive migration)

**API:** Full CRUD on teams; attach/detach members

**Acceptance:**
- Pest: nested teams work (Corporate Practice > M&A Team > Cross-border Subgroup)
- Pest: a user in two teams shows up in both team rollups (no dedup loss)
- Pest: workspace isolation

---

### F-07.6 — Smart Lists / saved filters

**Goal:** Any user can save a filter combination on any entity list (Contacts, Matters, Tasks, Time Entries, etc.) as a named Smart List. Shareable to workspace.

**Scope:**

- `smart_lists` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `user_id` ULID FK (creator)
  - `entity_type` VARCHAR(100) (`App\Models\Matter`, etc.)
  - `name` VARCHAR(100)
  - `filters` JSON (the serialized filter state from Filament)
  - `sort_order` JSON nullable
  - `is_shared_to_workspace` BOOLEAN default false
  - `is_pinned` BOOLEAN default false (user-level)
  - audit timestamps + soft deletes + audit users

- `SmartList` model with `BelongsToWorkspace`, scoped by entity_type
- Filament Custom Table Filter component — "Save as Smart List" / "Load Smart List" actions
- User menu shows pinned Smart Lists for quick access

**API:** CRUD on smart lists; toggle pin

**Acceptance:**
- Pest: saving a Smart List preserves filter state
- Pest: loading restores exact filter state
- Pest: shared Smart Lists visible to all workspace members; private only to creator
- Filament: pinned Smart Lists appear in sidebar nav

---

## Surge acceptance criteria

- [ ] F-07.1: Tasks attach to all 5 existing entity types; cross-entity Relation Managers work
- [ ] F-07.2: Time entries work; aggregation correct
- [ ] F-07.3: KYC checklists work; advisor-review markers logged for `[ADVISOR-REVIEW-RECOMMENDED]` items
- [ ] F-07.4: KPI targets work; progress computed correctly
- [ ] F-07.5: Teams work; Matters link to teams
- [ ] F-07.6: Smart Lists work across all entity types
- [ ] All Pest tests green
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated (~25 new endpoints)
- [ ] No regression in SURGE-01 to SURGE-06 tests (367 baseline)
- [ ] Founder sign-off recorded

---

## Out of scope

- Real-time timer UI (Year-2; manual time entry only)
- Conflict-of-interest checking on KYC (Year-2)
- Sanctions/PEP screening via external API (Year-2 — checklist line items exist but actual API calls deferred)
- Time entry approval workflow (Year-2)
- Team-based permission overrides (Year-2 — three roles still control access)
- Materialized KPI tables (Year-2 if perf demands)
- Auto-complete tasks based on document signing / matter closure (Year-2)

---

## What the Software Engineer agent should produce

Standard TTP structure (11 sections per Flow), but specifically:

1. **F-07.1 polymorphic patterns** must be reused exactly in F-07.4 (KPI targetable) and the future S-08 (litigation extensions) and S-09 (financial entries). Establish the polymorphic morph-map in `app/Providers/AppServiceProvider.php` and reference it consistently.
2. **F-07.3 KYC content** ships with founder-drafted item descriptions and reminder text in AR/EN. Each is tagged `[ADVISOR-REVIEW-RECOMMENDED]` in the code comment. The advisor's first review task (when secured) is to walk these checklists and either approve or revise.
3. **F-07.4 KPI calculations** must be deterministic and pure-function — same inputs always produce same outputs. Heavy use of dedicated service classes + thorough unit tests.
4. **F-07.6 Smart Lists** must serialize Filament filter state, not raw SQL. This means the saved JSON describes filter intent (e.g., `{ "status": ["active"], "practice_area": ["commercial_contracts"], "created_after": "2026-01-01" }`), not implementation. Filament restores this state by passing it back into the filter component.
