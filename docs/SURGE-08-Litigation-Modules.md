# SURGE-08 — Litigation Modules

**Surge ID:** S-08
**Name:** Litigation Modules
**Type:** BUILD Surge (post-MVP, breadth-pivot)
**Estimated duration:** 10–14 days (Claude Code-accelerated)
**Depends on:** SURGE-07 complete; lawyer advisor secured for PRODUCTION DEPLOYMENT
**Enables:** SURGE-09
**Pivot reference:** `decisions/D-09_breadth_pivot.md`

---

## ⚠️ Surge-level hard stops

| Hard Stop | Status | When it applies |
|---|---|---|
| `[HARD-STOP-LAWYER-REQUIRED]` for production deployment | UNRESOLVED | Surge can be BUILT without lawyer; cannot be DEPLOYED to a workspace handling real cases without Levant-licensed lawyer signoff on the procedural model |
| Each jurisdiction's procedural shape (Jordan / Lebanon / Palestine / Iraq) | UNRESOLVED | Court taxonomies, hearing types, service-of-process methods differ per jurisdiction. Default model is "Jordan first" — other jurisdictions require advisor review before activation |
| Bar-association ethical rules on case-data handling | UNRESOLVED | Some jurisdictions have requirements on opposing-counsel data, judge data, case-record confidentiality. Advisor must verify the data model complies |

**The Engineer agent should produce TTPs for this Surge BUT mark every PR with a `[HARD-STOP-LAWYER-REQUIRED]` label.** CI deploy step refuses to push to production until a sibling file `validation/08_litigation_lawyer_signoff.md` is present and signed.

Build, test, demo, iterate — all fine without lawyer. Real case data — not fine.

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — breadth additions |
| Legal domain | `[HARD-STOP-LAWYER-REQUIRED]` for ALL flows in this Surge |
| Jurisdictional shape | Jordan-first default; Lebanon / Palestine / Iraq added per advisor signoff |
| Sign-off | PENDING — Founder + Legal Advisor MUST both sign before production deployment |

---

## Goal

Extend the Matter schema and add the litigation-specific entities (Courts, Judges, Hearings, Court Reviews, Service Log, Opposing Counsel) that a Levant litigator needs. Build to Jordan-first procedural model, extensible to other Levant jurisdictions.

By the end of this Surge:

- A lawyer can convert an existing commercial Matter to also track litigation (additive — Matter still works as commercial-only)
- A workspace has a reference set of Courts and Jurisdictions (seeded with Jordan)
- A lawyer can record Hearings with date, court, judge, parties, outcome
- A lawyer can log Court Reviews (judge-issued decisions)
- A lawyer can maintain a Service Log (process service tracking)
- An Opponent Lawyer can be linked to a Matter (variant of Contact)

---

## Flows

### F-08.1 — Matter litigation extension `[HARD-STOP-LAWYER-REQUIRED]`

**Goal:** Additive migration that extends the Matter schema with litigation fields, without breaking existing commercial-only Matters.

**Scope:**

- Migration `add_litigation_fields_to_matters_table`:
  - `is_litigation` BOOLEAN default false — controls whether litigation tabs appear
  - `court_id` ULID FK nullable → `courts(id)`
  - `judge_id` ULID FK nullable → `judges(id)`
  - `court_case_number` VARCHAR(100) nullable
  - `case_number_internal` VARCHAR(100) nullable (firm's internal reference)
  - `litigation_status` ENUM nullable — Jordan-first default values: 
    `'pre_filing','filed','in_evidence','in_judgment','appealed','closed_won','closed_lost','settled','withdrawn'`
  - `filed_date` DATE nullable
  - `next_hearing_date` DATE nullable (computed from hearings)
  - `representation_role` ENUM nullable — Jordan-first values: `'plaintiff','defendant','intervenor','third_party'`
  - composite index `(workspace_id, is_litigation, litigation_status)`

- `Matter` model updates:
  - Casts updated
  - New scopes: `scopeLitigation()`, `scopeCommercial()` (commercial = `is_litigation = false`)
  - Eager-load `court`, `judge`, `hearings` when `is_litigation = true`

- Update Filament `MatterResource`:
  - Toggle "This matter involves litigation" switch on form (sets `is_litigation`)
  - Conditional litigation section appears (court, judge, case number, status)
  - List page filter chip: "Commercial | Litigation | All"
  - Table column: case number visible when filter = Litigation

**API:** Existing endpoints extended; new query parameter `?type=litigation|commercial|all`

**Acceptance:**
- Pest: existing commercial Matters unaffected; `is_litigation = false` default
- Pest: creating a litigation matter persists all litigation fields
- Pest: `Matter::commercial()->count()` matches pre-migration count (no leakage)
- Pest: workspace isolation preserved
- Filament: form correctly toggles litigation section

**`[HARD-STOP-LAWYER-REQUIRED]`** items in this Flow:
- The `litigation_status` enum values (do these match Jordanian / Lebanese / Iraqi / Palestinian civil procedure terminology?)
- The `representation_role` enum values
- Whether `court_case_number` format should be validated (jurisdiction-specific formats)

---

### F-08.2 — Courts and Judges reference data `[HARD-STOP-LAWYER-REQUIRED]`

**Goal:** Reference entities for courts (with seeded Jordan data) and judges (optional registry per court).

**Scope:**

- `courts` table:
  - `id` ULID
  - `workspace_id` ULID FK (workspace-scoped courts — each firm maintains its own list, or seed available)
  - `name_ar` VARCHAR(200)
  - `name_en` VARCHAR(200)
  - `court_type` ENUM — `'magistrate','first_instance','appeal','cassation','specialized_commercial','specialized_labor','specialized_family','administrative','sharia','arbitration'`
  - `jurisdiction_country` CHAR(2) ISO 3166-1
  - `jurisdiction_governorate` VARCHAR(100) nullable
  - `city` VARCHAR(100)
  - `address` TEXT nullable
  - `phone` VARCHAR(50) nullable
  - `notes` TEXT nullable
  - audit timestamps + soft deletes + audit users

- `judges` table (optional registry; many firms won't populate):
  - `id` ULID
  - `workspace_id` ULID FK
  - `court_id` ULID FK nullable (current court — judges rotate)
  - `name_ar` VARCHAR(200)
  - `name_en` VARCHAR(200) nullable
  - `title` VARCHAR(100) nullable
  - `notes` TEXT nullable (sensitive — restrict via policy)
  - audit timestamps + soft deletes + audit users

- `Court` and `Judge` models with `BelongsToWorkspace`
- Filament resources for both (Admin/Owner manage; Members read-only)
- Seeder: `JordanCourtsSeeder` — populates ~30 known Jordanian courts (verified by advisor)

**`[HARD-STOP-LAWYER-REQUIRED]`** items:
- The `court_type` enum (do these reflect actual court structure in target jurisdictions?)
- The seed list of Jordanian courts
- Whether judges should have any sensitive-data restrictions (e.g., "judges marked as recused on a matter")
- Whether judge data has privacy/data-protection implications

**Acceptance:**
- Pest: court CRUD; workspace isolation
- Pest: judges link to courts; soft-deleted court doesn't break related judges
- Pest: seeder populates courts correctly
- Filament: AR/EN names displayed based on current locale

---

### F-08.3 — Hearings `[HARD-STOP-LAWYER-REQUIRED]`

**Goal:** Track court hearings per litigation Matter — scheduled, held, postponed, cancelled.

**Scope:**

- `hearings` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `matter_id` ULID FK (must be `is_litigation=true`)
  - `hearing_date` DATETIME
  - `court_id` ULID FK
  - `judge_id` ULID FK nullable
  - `hearing_type` ENUM — Jordan-first values: `'first_session','evidence','expert_witness','witness_testimony','final_arguments','judgment','enforcement','other'`
  - `status` ENUM('scheduled','held','postponed','cancelled')
  - `held_at` DATETIME nullable (when status transitions to 'held')
  - `outcome` TEXT nullable
  - `next_action_required` TEXT nullable
  - `postponed_to_hearing_id` ULID FK self-referential nullable (when status='postponed')
  - `our_attendee_user_id` ULID FK nullable (who attended)
  - audit timestamps + soft deletes + audit users
  - index `(workspace_id, hearing_date)`, `(matter_id, hearing_date)`

- `Hearing` model with `BelongsToWorkspace`, links to Matter, Court, Judge
- `HearingPolicy`: Member can view + create + update; Admin/Owner can delete
- Filament `HearingResource` with calendar-style view; Relation Manager on Matter showing chronological hearings
- Matter's `next_hearing_date` field auto-computed via Eloquent observer when hearings change
- Documents (from S-03) can be attached to a hearing (polymorphic — uses existing Document model with a hearing context)

**Reminder integration:** Scheduled task (daily) emails Matter's lead lawyer 24h before any `status='scheduled'` hearing.

**`[HARD-STOP-LAWYER-REQUIRED]`** items:
- The `hearing_type` enum
- Reminder timing (24h sufficient? Some jurisdictions require longer notice)
- Whether hearing data has confidentiality requirements

**Acceptance:**
- Pest: hearing CRUD; tied to litigation Matter only (validation rejects commercial-Matter linkage)
- Pest: status transitions work; held_at set on 'held'
- Pest: matter's next_hearing_date updates correctly
- Pest: postponement chain works (Hearing A postponed → Hearing B; A.postponed_to_hearing_id = B.id)
- Reminder email dispatches at correct time

---

### F-08.4 — Court Reviews `[HARD-STOP-LAWYER-REQUIRED]`

**Goal:** Log judge-issued decisions (interlocutory or final) on a Matter. Linked to hearings where decisions are issued.

**Scope:**

- `court_reviews` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `matter_id` ULID FK
  - `hearing_id` ULID FK nullable (when decision issued in a hearing)
  - `decision_date` DATE
  - `decision_type` ENUM — Jordan-first values: `'interim_order','procedural_ruling','expert_appointment','evidence_ruling','partial_judgment','final_judgment','appeal_decision','enforcement_order','other'`
  - `outcome` ENUM('favourable','adverse','mixed','procedural_only')
  - `summary_ar` TEXT nullable
  - `summary_en` TEXT nullable
  - `decision_document_id` ULID FK nullable (S-03 Document if the decision was uploaded)
  - `appealable` BOOLEAN
  - `appeal_deadline_date` DATE nullable
  - `appeal_filed` BOOLEAN default false
  - `next_steps` TEXT nullable
  - audit timestamps + soft deletes + audit users

- `CourtReview` model with `BelongsToWorkspace`
- Filament `CourtReviewResource`; Relation Manager on Matter
- Auto-create Obligation (from S-05) when `appealable=true` and `appeal_deadline_date` set — links to litigation calendar

**`[HARD-STOP-LAWYER-REQUIRED]`** items:
- The `decision_type` enum
- Appeal-deadline logic per jurisdiction (Jordan vs Lebanon vs others differ on standard appeal windows)
- Whether decision summaries have privilege/confidentiality implications

**Acceptance:**
- Pest: court review CRUD
- Pest: appealable decisions auto-create Obligation with correct deadline
- Pest: linked decision_document accessible from review detail
- Filament: outcome badge color (green/red/gray) renders correctly

---

### F-08.5 — Service Log `[HARD-STOP-LAWYER-REQUIRED]`

**Goal:** Track process service (serving legal documents on parties) for litigation Matters.

**Scope:**

- `service_log_entries` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `matter_id` ULID FK
  - `served_party_contact_id` ULID FK (links to Contact)
  - `service_method` ENUM — Jordan-first values: `'personal_service','registered_mail','court_bailiff','substituted_service','publication','electronic','foreign_service'`
  - `service_date` DATE
  - `service_address` TEXT nullable
  - `served_by_name` VARCHAR(200) nullable (who performed the service)
  - `served_to_recipient_name` VARCHAR(200) nullable (who received)
  - `proof_document_id` ULID FK nullable (proof of service, if uploaded)
  - `status` ENUM('successful','failed_no_response','failed_refused','failed_invalid_address','pending_proof')
  - `notes` TEXT nullable
  - audit timestamps + soft deletes + audit users

- `ServiceLogEntry` model with `BelongsToWorkspace`
- Filament resource; Relation Manager on Matter
- Connects to Document (proof) and Contact (served party)

**`[HARD-STOP-LAWYER-REQUIRED]`** items:
- The `service_method` enum (varies significantly across Levant jurisdictions)
- The data-retention requirements for service-of-process records (in some jurisdictions, regulated)

**Acceptance:**
- Pest: service log CRUD
- Pest: failed services trigger Task creation (link to F-07.1) prompting re-attempt
- Filament: timeline view of service history

---

### F-08.6 — Opposing Counsel

**Goal:** Variant of Contact that represents lawyers on the other side. Linked to Matter via `matter_counterparties`.

**Scope:**

- Migration: add `is_opposing_counsel` BOOLEAN to `contacts` (defaults false)
- Migration: add `opposing_counsel_contact_id` ULID FK nullable to `matter_counterparties`
- Contact form (Filament): conditional checkbox "This contact is opposing counsel for cases" (shows if `type=person`)
- Matter Counterparty form: optional "Opposing counsel" picker (Contact filtered to `is_opposing_counsel=true`)
- Display: Matter detail "Parties" tab shows counterparty + their opposing counsel pairing

**Acceptance:**
- Pest: contact CRUD with new flag
- Pest: opposing counsel linkage on Matter persists
- Filament: form fields render correctly
- Pest: contact filtering (`Contact::query()->opposingCounsel()`) works

---

## Surge acceptance criteria

- [ ] F-08.1 through F-08.6 all built, tested, demoable
- [ ] All 6 Flows pass Pest tests
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated (~30 new endpoints)
- [ ] No regression in S-01 through S-07 tests
- [ ] All `[HARD-STOP-LAWYER-REQUIRED]` items inventoried in `validation/08_litigation_lawyer_signoff_pending.md` with the founder-decided defaults documented
- [ ] CI deploy step verifies `validation/08_litigation_lawyer_signoff.md` exists before allowing production push
- [ ] Founder sign-off on the BUILD (acknowledging that production deployment to real cases requires lawyer)

---

## Out of scope for THIS Surge

- E-filing integration with court systems (Year-2 — varies per jurisdiction)
- Legal research integration (Year-2 — partner with Westlaw / LexisNexis / Tashriaat)
- Court fee calculation (Year-2)
- Statute of limitations alerting (Year-2)
- Multi-jurisdictional matter handling (Year-2 — MVP assumes one primary jurisdiction per Matter)
- Pleading templates (already excluded — Form Templates engine remains out)
- Court calendar integration (Year-2)

---

## Specific instructions for the Software Engineer agent

1. **Every Flow's TTP must include a `[HARD-STOP-LAWYER-REQUIRED]` items list** at the top, in addition to standard sections.
2. **Migration safety:** F-08.1 is an additive migration ONLY — never alter or drop existing Matter columns. Existing commercial Matters must remain functional.
3. **Seeders are jurisdiction-aware:** `JordanCourtsSeeder` is run by default. `LebanonCourtsSeeder`, etc., exist but are NOT run automatically — they require a manual artisan call (`php artisan db:seed --class=LebanonCourtsSeeder`) which the founder triggers after lawyer review.
4. **Polymorphic Tasks (F-07.1) extend to litigation entities** — Hearings, Court Reviews, Service Log entries should all be `taskable`.
5. **Performance:** Litigation Matters often have 20+ hearings, 5+ court reviews, 10+ service log entries. Eager-load everywhere on Matter detail; consider DB cursor pagination for hearings list on long-running matters.
6. **Refuse to mark this Surge "Deployment Ready"** until `validation/08_litigation_lawyer_signoff.md` exists. The Surge can be marked "Code Complete" without lawyer; deployment to production is the hard stop.
