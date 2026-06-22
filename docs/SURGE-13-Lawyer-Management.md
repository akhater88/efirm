# SURGE-13 — Lawyer Management & Matter Assignment

**Surge ID:** S-13
**Name:** Lawyer Management & Matter Assignment
**Type:** BUILD Surge — fills a gap missed in S-02 and S-07 planning
**Estimated duration:** 5–7 days (Claude Code-accelerated)
**Depends on:** SURGE-01 (Auth/Workspace/Members), SURGE-02 (Matters), SURGE-07 (Tasks, Time Entries, Teams), SURGE-08 (Hearings)
**Pivot reference:** Follow-up to D-09; gap identified during 2026-06-22 founder review

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — fills a foundational practice-management gap |
| Legal domain | None — internal user management only |
| Sign-off | PENDING |
| Backward compatibility | **CRITICAL** — existing Matters, Hearings, Tasks, Time Entries, KPI calculations must continue to function during and after migration |
| Founder rationale | Matter assignment to specific lawyers was never explicitly added through S-01 to S-12 planning despite being a foundational primitive. The Team assignment in S-07 F-07.5 is too coarse; lead-lawyer-on-Matter is the missing link. |

---

## Goal

Add lawyer-as-user-profile + multi-lawyer-on-Matter assignment + per-Hearing lawyer assignment, with a Lead/Supporting role distinction. Internal practice management only — no public directory.

By the end of this Surge:

- A workspace Owner/Admin can mark workspace members as "lawyers" by filling a Lawyer Profile on their User record (bar number, jurisdictions, practice areas, languages, default hourly rate, status active/inactive)
- A Matter has one Lead Lawyer + zero-to-many Supporting Lawyers
- A Hearing has its own `assigned_lawyer_user_id` (defaults to Matter's Lead Lawyer on Hearing creation, overridable)
- The Matter list filter shows Matters assigned to current user, all Matters (Admin/Owner), or by specific lawyer
- KPI rollups (S-07 F-07.4) extend to per-lawyer: matters opened, matters closed, billable hours, etc.
- Tasks can still be assigned to anyone, but the picker preferentially surfaces Matter's assigned lawyers first
- Time entries roll up correctly: a Lead Lawyer sees their own time, a Supervisor sees lead+supporting time per Matter

---

## Flows

### F-13.1 — Lawyer Profile (User extension)

**Goal:** Extend the User model with optional Lawyer Profile fields. A User with a non-null `lawyer_profile_id` (or `is_lawyer = true`) is treated as a lawyer in lawyer-pickers throughout the app.

**Scope:**

- Migration `create_lawyer_profiles_table`:
  - `id` ULID
  - `user_id` ULID FK (unique — one profile per user; cascade on user delete)
  - `bar_admission_number` VARCHAR(100) nullable (the lawyer's bar registration number)
  - `bar_admission_country` CHAR(2) nullable (ISO 3166-1 alpha-2)
  - `bar_admission_date` DATE nullable
  - `jurisdictions` JSON nullable (array of ISO country codes + optional governorate, e.g., `[{"country":"JO","governorate":"Amman"},{"country":"LB"}]`)
  - `practice_areas` JSON nullable (array of practice area enum values matching `matters.practice_area`)
  - `languages_spoken` JSON nullable (array of ISO 639-1 codes; default `['ar','en']`)
  - `default_hourly_rate` DECIMAL(8,2) nullable
  - `default_currency` CHAR(3) nullable
  - `position_title_ar` VARCHAR(150) nullable (e.g., "محامي شريك", "محامي أول", "محامي مساعد")
  - `position_title_en` VARCHAR(150) nullable (e.g., "Partner", "Senior Associate", "Associate", "Paralegal")
  - `bio_ar` TEXT nullable
  - `bio_en` TEXT nullable
  - `status` ENUM('active','inactive','on_leave') default 'active'
  - `joined_firm_date` DATE nullable
  - audit timestamps + soft deletes + audit users

- Eloquent model `LawyerProfile` with `BelongsTo` to User; User gets `hasOne(LawyerProfile)` and helper `User::isLawyer(): bool` returning whether a non-soft-deleted active profile exists
- Policy `LawyerProfilePolicy`:
  - `view`: any workspace member can see another member's profile within their workspace
  - `update`: Owner/Admin only (Admin can manage all profiles; Owner can manage Admin + Owner profiles too)
  - `update-own`: a Member can update their own profile (limited fields — bio, position_title, languages_spoken; cannot self-edit bar number or rate)
  - `delete`: Owner only
- Filament `LawyerProfileResource` accessible from User detail page (Relation Manager) AND as a standalone Filament resource "Lawyers" in the navigation:
  - List view: Active lawyers in workspace, with avatar + name + position + practice areas + status badge
  - Form: standard fields
  - Filters: by practice area, by jurisdiction, by status
  - Bulk actions: change status (Admin/Owner only)
- Add `is_lawyer` Filament column to User list (computed, not a DB column)

**`[PROVISIONAL-FOUNDER-DECIDED]`** items:
- Position title enum values (Partner/Senior Associate/Associate/Paralegal vs. local Levant titles like المحامي الشريك / المحامي الأول / المحامي المساعد / المساعد القانوني) — flagged for lawyer-advisor review when secured
- Whether bar_admission_number should be validated against jurisdiction-specific formats — deferred until advisor review

**Tests:**
- Pest: Lawyer Profile CRUD; workspace isolation enforced
- Pest: User::isLawyer() returns correct boolean
- Pest: cannot create two profiles for same User
- Pest: Member can update own bio + position; cannot update own bar number or rate (returns 403)
- Pest: Owner/Admin can update any profile
- Pest: deleting a User soft-deletes the LawyerProfile via cascade

**Acceptance:** Founder can navigate to "Lawyers" in Filament; create profiles for self + 2 other invited Members; verify list filtering by practice area works; verify status changes; verify Member self-edit restrictions.

**API:** Full CRUD on lawyer profiles (`GET /api/v1/lawyer-profiles`, etc.)

---

### F-13.2 — Matter Lawyer Assignment (Lead + Supporting)

**Goal:** A Matter has one Lead Lawyer (a User with active LawyerProfile) and zero-to-many Supporting Lawyers. Migration is additive — existing Matters remain valid (Lead Lawyer nullable initially; auto-populated by data migration).

**Scope:**

- Migration `create_matter_lawyers_table`:
  - `id` ULID
  - `matter_id` ULID FK (cascade on Matter delete)
  - `user_id` ULID FK
  - `role` ENUM('lead','supporting')
  - `assigned_at` TIMESTAMP
  - `assigned_by_user_id` ULID FK
  - `unassigned_at` TIMESTAMP nullable (when removed — kept for audit trail)
  - `unassigned_by_user_id` ULID FK nullable
  - `notes` TEXT nullable
  - composite unique `(matter_id, user_id)` where `unassigned_at IS NULL` (active assignment uniqueness)
  - index `(matter_id, role)` for fast Lead lookup

- Data migration `backfill_matter_lawyers_from_created_by`:
  - For each existing Matter with no lawyer assignments and a `created_by_user_id` that has a LawyerProfile, create a `matter_lawyers` row with `role='lead'`, `assigned_at = matter.created_at`, `assigned_by_user_id = matter.created_by_user_id`
  - For Matters without a LawyerProfile-holding creator, leave unassigned (Owner/Admin will see these in the "Unassigned Matters" filter and remediate)
  - **Rollback strategy:** the `down()` migration removes only rows created by this backfill (matched by `assigned_at = matter.created_at` heuristic; or use a dedicated `seeded_by_migration` flag on the row)

- Eloquent on `Matter`:
  - `lawyers()` HasMany matter_lawyers WHERE unassigned_at IS NULL
  - `leadLawyer()` HasOne matter_lawyers WHERE role='lead' AND unassigned_at IS NULL
  - `supportingLawyers()` HasMany matter_lawyers WHERE role='supporting' AND unassigned_at IS NULL
  - Helper: `Matter::assignLawyer(User $user, MatterLawyerRole $role, User $by)`, `Matter::unassignLawyer(User $user, User $by)`, `Matter::changeLeadLawyer(User $newLead, User $by)`
  - **Constraint enforced in MatterLawyerService:** a Matter must have at most one active Lead at any time; changing lead atomically marks previous lead as `unassigned_at = now()` and creates a new row with `role='lead'`. (Append-style audit pattern — never UPDATE the role column on an existing row.)

- Policy `MatterLawyerPolicy`:
  - `assign`: Owner/Admin can assign anyone; current Lead Lawyer of the Matter can assign Supporting lawyers; Members cannot assign anyone
  - `unassign`: Same rules as assign
  - `change_lead`: Owner/Admin only

- Filament `MatterResource` updates:
  - Form section "Assigned Lawyers": Lead Lawyer picker (required for new Matters — single select from active LawyerProfile users); Supporting Lawyers multiselect
  - List view: new column "Lead Lawyer" with avatar+name; sortable, filterable
  - List filter chip: "Assigned to me" (current user is lead OR supporting on matter); "Unassigned" (no active lead); "Lead by..." (filter by specific lawyer)
  - Relation Manager "Assigned Lawyers" tab on Matter detail: shows current + historical assignments with timestamps

- Notification: when a Matter is assigned to a lawyer (Lead or Supporting), the lawyer receives an in-app notification + email (bilingual based on user's locale).

- Notification: when a Matter's Lead is changed, both old lead and new lead receive notifications.

**`[PROVISIONAL-FOUNDER-DECIDED]`** items:
- Whether Matter creation should hard-require a Lead Lawyer (currently: required by Filament form, nullable in DB for backfill compatibility) — flag for lawyer-advisor review
- Whether a lawyer can be Lead on more than N matters concurrently (workload cap) — currently no cap; flag for future iteration

**Tests:**
- Pest: assign lead lawyer; verify single active lead constraint
- Pest: change lead lawyer; verify old lead `unassigned_at` set, new lead row created
- Pest: assign supporting lawyers; multiple allowed
- Pest: unassign supporting lawyer; subsequent listing excludes them
- Pest: Member cannot assign lawyers (Policy denies)
- Pest: current Lead can assign supporting but cannot change Lead (Policy denies change_lead for non-Admin)
- Pest: workspace isolation (cannot assign a User from another workspace)
- Pest: data migration backfills correctly
- Pest: backward compat — existing Matters without assignments don't break Matter detail page
- Pest: notification dispatch on assign + change_lead
- Pest: "Assigned to me" filter returns correct Matters

**API:**
- `POST /api/v1/matters/{id}/lawyers` — assign (body: `user_id`, `role`)
- `DELETE /api/v1/matters/{id}/lawyers/{user_id}` — unassign
- `PUT /api/v1/matters/{id}/lead-lawyer` — change lead (body: `user_id`)
- `GET /api/v1/matters/{id}/lawyers` — current + historical
- Existing `GET /api/v1/matters` extended with `?assigned_to=current_user|user_id|unassigned`

**Acceptance:** Founder assigns themselves as Lead on Matter A; assigns 2 supporting lawyers; verifies both receive notifications; changes Lead to another lawyer; verifies handoff; verifies "Assigned to me" filter works correctly.

---

### F-13.3 — Hearing Assignment (per-Hearing override)

**Goal:** Each Hearing has its own `assigned_lawyer_user_id`. Default on creation = Matter's current Lead Lawyer; user can override per-Hearing.

**Scope:**

- Migration `add_assigned_lawyer_to_hearings_table`:
  - Add `assigned_lawyer_user_id` ULID FK nullable to `hearings` table
  - Add `assigned_at` TIMESTAMP nullable
  - Add `assigned_by_user_id` ULID FK nullable
  - Backfill: for existing Hearings, set `assigned_lawyer_user_id = (SELECT user_id FROM matter_lawyers WHERE matter_id = hearings.matter_id AND role = 'lead' AND unassigned_at IS NULL LIMIT 1)`; leave NULL if no lead

- Eloquent on `Hearing`:
  - `assignedLawyer()` BelongsTo User
  - Helper: `Hearing::assignLawyer(User $user, User $by)`
  - Hearing creation observer: if `assigned_lawyer_user_id` not set in input, default to Matter's current Lead Lawyer

- Filament `HearingResource` updates:
  - Form: "Assigned Lawyer" picker (defaults to Matter's Lead Lawyer; can override). Lists all active LawyerProfile users in workspace.
  - List view: new column "Assigned Lawyer" with avatar+name
  - Reminder dispatch (S-08 F-08.3): the 24h-before-hearing reminder now goes to the **Hearing's assigned lawyer**, not the Matter's Lead Lawyer (this is the actual reason this feature exists — junior associate attending tomorrow's hearing should get the reminder, not the partner)

- Policy update: `HearingPolicy::update` — anyone with access to the parent Matter can update; specifically, the assigned lawyer can update their own Hearing status/outcome without needing Admin role

**Tests:**
- Pest: creating Hearing without specifying assigned_lawyer auto-populates Matter's Lead
- Pest: creating Hearing with override stores the override correctly
- Pest: hearing reminder dispatches to assigned lawyer, NOT Matter's Lead, when they differ
- Pest: backfill correctly populates existing Hearings
- Pest: existing Hearing tests from S-08 still pass

**Acceptance:** Founder creates a Hearing on a Matter; verifies auto-default to Lead; overrides to a Supporting lawyer; verifies reminder email arrives at correct address; verifies reminder NOT sent to Lead.

**API:** Existing Hearing endpoints accept and return `assigned_lawyer_user_id`.

---

### F-13.4 — Task Assignment UX improvement

**Goal:** When assigning a Task on a Matter, the assignee picker preferentially shows the Matter's assigned lawyers at the top of the list, then other workspace members below. Not a hard restriction — just a UX nudge.

**Scope:**

- Filament Task form `assigned_to_user_id` picker updated:
  - When the Task is attached to a Matter (`taskable_type='matter'`), the dropdown's options are grouped:
    - Group 1: "Matter's Assigned Lawyers" — Lead first, then Supporting
    - Group 2: "Other Workspace Members"
  - When attached to other entity types (Contact, Document, etc.), no grouping; flat list
- Backend: no logic change — assignment is unrestricted; this is purely UI ergonomics

**Tests:**
- Pest browser test: open Task form on a Matter with 3 assigned lawyers (1 Lead + 2 Supporting) and 5 other members; verify dropdown shows lawyers first
- Pest browser test: same form on a Contact (no Matter): flat list

**Acceptance:** Founder creates a Task on a Matter; sees lawyer dropdown grouped correctly.

---

### F-13.5 — KPI extension (per-lawyer rollups)

**Goal:** S-07 F-07.4 KPI system computes per-User and per-Team rollups. Extend to compute per-lawyer rollups using the `matter_lawyers` and `time_entries` data. New metrics surface lawyer workload.

**Scope:**

- New KPI metrics added to the `kpi_targets.metric` enum:
  - `matters_as_lead_active` — count of Matters where user is current Lead and Matter not closed
  - `matters_as_supporting_active` — count where user is current Supporting
  - `matters_closed_as_lead_period` — count of Matters where user was Lead at closure, closed within period
  - `billable_hours_period` — already exists, extend to include time entries from all Matters where user is Lead OR Supporting
- KpiService extended:
  - `getActualValue($user, $metric, $period)` handles new metrics
  - For `matters_*_period`, joins `matters` with `matter_lawyers` filtered by role + period
- Filament KPI dashboard widget: extended to show per-lawyer Matter counts
- New Filament Page: "Lawyer Workload" (Owner/Admin only)
  - Table: each lawyer's active Matter count (Lead/Supporting), billable hours this month, KPI progress
  - Visual indicator: lawyers at >100% of target highlighted in red; <50% in yellow
  - Drill-down: click a lawyer to see their Matter list

**Tests:**
- Pest: each new metric returns correct count for a known dataset
- Pest: KPI cache invalidates correctly on Matter assignment change
- Pest: workspace isolation
- Pest: closing a Matter advances `matters_closed_as_lead_period`

**Acceptance:** Founder views "Lawyer Workload" page; sees self with current Matter counts; verifies numbers match Filament Matter list filtered by "Lead by me".

---

### F-13.6 — Matter detail "Team" tab

**Goal:** Surface the assigned lawyers prominently on Matter detail page. Tab name "Team" (or "الفريق") shows current assignments + history.

**Scope:**

- New Filament Relation Manager `MatterLawyersRelationManager` on `MatterResource`:
  - Tab label: "الفريق" / "Team"
  - Default view: Active assignments only (Lead at top, Supporting below)
  - Filter toggle: "Show history" — includes unassigned past assignments
  - Columns: Lawyer avatar+name, Role badge (Lead/Supporting), Assigned at, Assigned by
  - Actions per row: Unassign (Admin/Owner or current Lead); Change to Lead (Admin/Owner only — promotes Supporting to Lead, demotes previous Lead to Supporting OR fully unassigns previous Lead per founder choice modal)
  - Header actions: "+ Assign Lawyer" — picker for Lead or Supporting
- Position: tab order on Matter detail — Overview / **Team** / Documents / Parties / Obligations / [Tasks / Hearings / Court Reviews if applicable] / Time / Form Submissions

**Tests:**
- Pest browser test: Matter detail "Team" tab renders; shows correct lawyer assignments
- Pest browser test: assign new Supporting lawyer via tab; verify it appears
- Pest browser test: change Lead via tab; verify previous Lead becomes Supporting or unassigned per choice

**Acceptance:** Founder navigates to a Matter; sees Team tab; assigns 2 lawyers; verifies the experience feels intuitive.

---

## Surge acceptance criteria

- [ ] F-13.1: Lawyer Profile CRUD works; profiles distinguishable from non-lawyer Users
- [ ] F-13.2: Matter assignment with Lead+Supporting works; backfill complete; backward compat preserved
- [ ] F-13.3: Hearing per-hearing assignment works; reminders target correct lawyer
- [ ] F-13.4: Task picker UX surfaces Matter's lawyers first
- [ ] F-13.5: KPI extensions work; Lawyer Workload page renders
- [ ] F-13.6: Matter detail Team tab works
- [ ] All Pest tests green (minimum 35 new tests across the 6 Flows)
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated (~10 new endpoints)
- [ ] No regression in S-01 to S-12 tests
- [ ] AR locale renders correctly throughout
- [ ] Data migration's backfill verified manually on a copy of any pre-existing data
- [ ] `[PROVISIONAL-FOUNDER-DECIDED]` items inventoried in `validation/13_lawyer_management_advisor_review.md`

---

## Migration safety

This Surge touches existing Matter and Hearing tables. **All migrations must be additive — no column drops, no enum changes.** The `matter_lawyers` table is net-new. The Hearing migration only adds nullable columns with data migration backfill.

The data migration `backfill_matter_lawyers_from_created_by` is the riskiest step. It must:

1. Be idempotent — running it twice produces the same result, doesn't create duplicates
2. Be reversible — `down()` removes only rows it created
3. Be testable — Pest test runs the migration against a known dataset and asserts correct outcomes
4. Be observable — log the count of Matters backfilled, count of Matters left unassigned (no LawyerProfile creator)

Run on a copy of any pre-existing production data BEFORE merging the PR. If unassigned-count is high, surface that to the founder before deploying — they may want to bulk-assign before going live.

---

## Out of scope for this Surge

- Public lawyer directory / "Meet our team" page — out per founder Q4 answer
- Bar admission verification via external registry API — out (Year-2)
- Conflict-of-interest checking against assigned lawyers' prior matters — out (Year-2)
- Lawyer hierarchy (Senior / Junior reporting relationships beyond Team membership from S-07) — out
- Time-based assignment expiry (e.g., "assigned for 30 days only") — out
- Automatic Matter rebalancing when a lawyer goes on leave — out (manual reassignment)
- Lawyer billing rates per-Matter (overrides the default `default_hourly_rate`) — out (Year-2)
- Lawyer chat / direct messaging — out (per CLAUDE.md §7 still permanently out)

---

## What the Software Engineer agent should produce

1. **For F-13.2 data migration:** include a dry-run mode. The migration script writes its planned changes to a log file BEFORE applying, allowing inspection. Founder reviews the dry-run output; only after confirmation does the migration apply. This is critical because Matter assignment data corruption is hard to recover from.

2. **For F-13.2 Lead change semantics:** in the founder's review of the TTP, surface the question explicitly: "When changing Lead from User A to User B, should A become Supporting OR fully unassigned?" — the TTP must declare a default and offer a toggle (Filament confirm modal) at the moment of action.

3. **For F-13.3 Hearing reminder:** the existing reminder cron job (S-08 F-08.3) must be updated to query `hearing.assigned_lawyer_user_id` first, falling back to Matter's Lead Lawyer ONLY if Hearing's assignment is null. Pest tests verify both paths.

4. **For F-13.5 KPI cache invalidation:** the cache must invalidate when `matter_lawyers` changes (new assignment, unassignment, role change). Use Laravel model events on the MatterLawyer model.

5. **For F-13.6 Team tab Relation Manager:** the bilingual "Team" / "الفريق" label uses the existing localization patterns. Place the tab between "Overview" and "Documents" in the tab order — this is a deliberate visual statement: who's on the matter is the second most important thing to know about it.

6. **For all Filament forms touching lawyer pickers:** the picker queries `LawyerProfile::where('status', 'active')->where('workspace_id', current_workspace_id)` joined with users. Cache this query per request (it's used multiple times in lawyer-heavy forms).

7. **Test count discipline:** F-13 collectively must add at least 35 Pest tests. CLAUDE.md §9 test categories #7 (Filament resource tests), #5 (workspace isolation), and standard backward-compatibility tests all apply.

---

## CLAUDE.md updates needed after F-13.6 completes

After this Surge ships, CLAUDE.md v5 must:

- Add to §7 Domain Glossary: `LawyerProfile`, `MatterLawyer` (pivot)
- Update Matter's glossary entry to reference Lead + Supporting Lawyers assignment
- Update Hearing's glossary entry to reference per-Hearing assignment
- Add to §6 Naming: new patterns for lawyer-related entities
- Update §11 Pipeline References with `validation/13_lawyer_management_advisor_review.md`

The Engineer agent should propose this CLAUDE.md diff at the end of F-13.6 — founder applies it as v5.

---

## What this Surge does NOT solve (and why that's OK)

This Surge addresses lawyer-on-matter assignment, which was the specific gap you identified. It does NOT solve:

- **Onboarding workflow for new lawyers joining the firm.** Currently a new lawyer is invited as a workspace Member (S-01) and then someone fills their LawyerProfile (S-13 F-13.1). A guided onboarding wizard for lawyer-specific intake (bar number, jurisdictions, etc., as a flow rather than a form) could be SURGE-14 if needed.
- **Conflict-of-interest checking.** When you assign a lawyer to a Matter against a counterparty they've represented in the past — no automatic flag. This is a real legal-ethics requirement in many jurisdictions. Year-2 or a separate small Surge if requested by the lawyer advisor.
- **Lawyer departure handoff.** A workflow for when a lawyer leaves the firm: bulk-reassign their Matters, archive their LawyerProfile, etc. Currently manual.
- **Pro hac vice / out-of-jurisdiction work tracking.** Lawyer admitted in Jordan working on a Lebanese matter — no special handling. Year-2.

These are deliberately out. If you discover that one of them is blocking a real customer pilot, supersede this scope with a new Surge plan citing the customer's specific need.
