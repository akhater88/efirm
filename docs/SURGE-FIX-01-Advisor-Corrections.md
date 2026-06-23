# SURGE-FIX-01 — Advisor Corrections (Khaldoun, June 22–23)

**Surge ID:** S-FIX-01
**Name:** Advisor Corrections — schema, enums, services
**Type:** CORRECTION Surge (additive only — no breaking changes)
**Estimated duration:** 3–5 days (Claude Code-accelerated)
**Depends on:** SURGE-01 through SURGE-09 complete
**Gates:** Should ship BEFORE further SURGE-10/11/12/13 work to avoid building new features on incorrect litigation/KYC/UPL primitives
**Source:** `validation/02_advisor_meeting_log.md` — Khaldoun Khater conversations 2026-06-22 and 2026-06-23
**Pivot reference:** Follow-up to D-09; informal-advisor input not subject to hard-stop signoff (those remain RED for production)

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — strengthens existing wedge surfaces |
| Legal domain | Informal advisor input (Khaldoun Khater). Does NOT satisfy the formal lawyer signoff hard stops in CLAUDE.md §10 — those still require the separately-engaged paid attorney's attestation. |
| Sign-off | Founder + Khaldoun confirmation only (informal). Production deployment of S-08 still RED until formal signoff. |
| Backward compatibility | **CRITICAL** — every migration is additive. No column drops, no existing enum value removals. Existing data must continue to function unchanged. |
| Test discipline | This Surge MUST add ≥ 40 Pest tests across the 7 Flows. The recurring under-testing pattern in prior Surges (488 tests for ~120 endpoints) cannot continue when the corrections involve life-or-death deadlines for client cases. |

---

## Goal

Apply every piece of validated advisor input from Khaldoun's two conversations into the codebase. Convert the project from "code built against assumed enums" to "code built against practitioner-validated enums." Capture the source attribution in code comments so future maintainers can trace decisions back to the advisor log.

By the end of this Surge:

- `litigation_status`, `hearing_type`, `kyc_item.item_type` enums match Jordanian commercial practice
- A dedicated `ExpertReport` entity exists with mandatory 8-day objection countdown
- A dedicated `AppealDeadlineService` correctly calculates 10-day vs 30-day windows based on court level + judgment presence type
- Trust account corrections enforce mandatory description text on adjustment entries
- Jordan PDPL Law 24/2023 explicit consent flow exists in onboarding
- Corporate KYC includes Company Registration Certificate + Signatory Authority document
- AI Disclaimer text updated everywhere with Khaldoun's specific language
- ToS jurisdiction language documented for the paid lawyer's drafting (NOT drafted in code — that's for the lawyer)
- `JordanCourtsSeeder` reads from CSV (file pending from Khaldoun)
- D-15 ADR documents the pricing decision (JOD 20–30/user/month ceiling)

---

## Flows

### F-FIX-01.1 — Litigation enum extensions

**Goal:** Extend `litigation_status` and `hearing_type` enums to match Jordanian commercial practice per advisor input.

**Source:** `validation/02_advisor_meeting_log.md` Conversation 1, decisions #1 and #2.

**Scope:**

- Migration `extend_litigation_status_enum`:
  - Add to `matters.litigation_status` enum: `fee_payment_and_registration` (قيد الدعوى ودفع الرسوم), `notification_pending` (تبليغ), `referred_to_expert` (الإحالة للخبرة)
  - Existing values preserved unchanged
  - Update typical order documented in service comment

- Migration `extend_hearing_type_enum`:
  - Replace `evidence` with `plaintiff_evidence` (بينات المدعي) and `defendant_evidence` (بينات المدعى عليه)
  - Add `notification_session` (جلسة تبليغ)
  - **Data migration:** any existing `hearings` rows with `hearing_type='evidence'` get flagged into `validation/fix-01_evidence_hearings_to_reclassify.csv` for manual founder review (these can't be auto-split into plaintiff vs defendant without legal judgment); the rows themselves keep `evidence` as a deprecated value for backward compat — a `[DEPRECATED]` flag added to the enum comment

- Eloquent `Matter` model:
  - Update `LitigationStatusEnum` PHP enum class to include new cases
  - Update Filament `MatterResource` litigation status filter chips to surface new states
  - Update bilingual lang files: `resources/lang/ar/litigation_status.php` and `en/litigation_status.php`

- Eloquent `Hearing` model:
  - Update `HearingTypeEnum` PHP enum class
  - Update Filament `HearingResource` form picker
  - Bilingual lang updates

**Tests:**
- Pest: each new enum value persists correctly through CRUD
- Pest: existing matters with old enum values continue to work
- Pest: AR + EN locale renders new enum labels correctly
- Pest: data migration audit CSV produced when existing 'evidence' hearings detected
- Pest: Filament resource filter chips display new states

**Acceptance:** Migration applies cleanly to a copy of existing data; no regressions; founder reviews `validation/fix-01_evidence_hearings_to_reclassify.csv` if any rows exist.

---

### F-FIX-01.2 — Expert Report entity + 8-day objection countdown

**Goal:** Create `expert_reports` as a first-class entity attached to litigation Matters, with automatic 8-day objection deadline calculation.

**Source:** Decisions #3 and #19.

**Scope:**

- Migration `create_expert_reports_table`:
  - `id` ULID
  - `workspace_id` ULID FK
  - `matter_id` ULID FK (must be `is_litigation=true` — CHECK constraint at Eloquent level)
  - `expert_name_ar` VARCHAR(200)
  - `expert_name_en` VARCHAR(200) NULLABLE
  - `report_type` ENUM('damages_calculation', 'account_audit', 'technical_specification', 'real_estate_valuation', 'medical', 'handwriting_authentication', 'other') — extensible via lawyer review
  - `received_date` DATE (the day the report was formally received by us)
  - `objection_deadline_date` DATE (computed: received_date + 8 days; persisted for fast filtering)
  - `objection_filed` BOOLEAN default false
  - `objection_filed_date` DATE NULLABLE
  - `our_position` ENUM('not_yet_reviewed', 'accept', 'object_partial', 'object_full', 'objection_filed', 'objection_overruled', 'objection_upheld')
  - `summary_ar` TEXT NULLABLE
  - `summary_en` TEXT NULLABLE
  - `document_id` ULID FK NULLABLE (link to uploaded report Document from S-03)
  - audit timestamps + soft deletes + audit users
  - index `(workspace_id, objection_deadline_date)` for the alerting cron

- Model `ExpertReport` with `BelongsToWorkspace`, `BelongsTo` Matter, `BelongsTo` Document
- Service `ExpertReportService`:
  - `calculateObjectionDeadline(Date $received): Date` — adds 8 days, skipping no weekends (Jordan working week handling deferred — confirm with Khaldoun whether Friday/Saturday counts toward the 8 days; for now assume calendar days as he stated)
  - `markObjectionFiled(ExpertReport $report, Date $filedDate, User $by): void`
- Observer: on ExpertReport create, auto-creates an Obligation (from S-05) with due_date = objection_deadline_date, title localized to "Object to Expert Report — [matter name]"
- Scheduled job (daily): identify ExpertReports with `objection_deadline_date` within next 3 days and `objection_filed=false`; send escalating reminders to Matter's Lead Lawyer (T-3 day, T-1 day, day-of)
- Filament `ExpertReportResource`:
  - Form: standard fields + file upload for report document
  - List: sortable by objection_deadline_date; visual urgency indicator (red < 1 day, orange < 3 days, yellow < 5 days)
  - Relation Manager on Matter detail (`MatterResource`): "Expert Reports" tab
  - Action: "Mark objection filed" — sets objection_filed_date, transitions our_position

**`[PROVISIONAL-FOUNDER-DECIDED]`** items (flag for next Khaldoun conversation):
- Whether the 8-day window counts calendar days or working days (Friday/Saturday in Jordan)
- The `report_type` enum values — does this list cover what Khaldoun's firm actually sees? Are there Jordanian-specific report types missing?

**Tests:** ≥ 8 tests including
- Pest: creating ExpertReport with received_date=today persists objection_deadline_date = today + 8
- Pest: Observer auto-creates Obligation with correct due_date
- Pest: scheduled reminder job dispatches at correct T-3/T-1/day-of intervals
- Pest: cannot attach ExpertReport to commercial-only Matter (is_litigation=false)
- Pest: workspace isolation
- Pest: marking objection filed updates both expert_report + the obligation
- Pest: Filament urgency indicator renders correctly
- Pest: AR + EN locale labels render

**Acceptance:** Founder creates an ExpertReport on a test litigation Matter, sees Obligation auto-created, verifies reminder dispatches at correct intervals.

---

### F-FIX-01.3 — AppealDeadlineService (court-level-dependent)

**Goal:** Replace any hardcoded 30-day appeal logic with court-level-dependent calculation per advisor's critical correction.

**Source:** Decision #18 (SUPERSEDES #4) — Khaldoun's Conversation 2 critical correction.

**Scope:**

- Migration `add_court_level_and_judgment_presence_fields`:
  - `matters.court_level` ENUM('magistrate', 'first_instance', 'appeal', 'cassation', 'specialized_commercial', 'specialized_labor', 'administrative', 'sharia', 'arbitration') NULLABLE
    - Denormalized from `courts.court_type` at Matter creation/update; cached for fast deadline calculation
  - `court_reviews.judgment_presence` ENUM('wijahi', 'mithla_wijahi', 'ghyabi') NULLABLE
    - `wijahi` (وجاهي) = in-presence — countdown from day after decision_date
    - `mithla_wijahi` (بمثابة الوجاهي) = deemed in-presence — countdown from day after notified_date
    - `ghyabi` (غيابي) = pure default — **OPEN: confirmed by advisor as separate category? appeal window same as mithla_wijahi or different? — pending Khaldoun's reply to Conversation 2 follow-up question**
  - `court_reviews.notified_date` DATE NULLABLE (when formal notification served, for non-in-presence judgments)

- Service `app/Services/AppealDeadlineService.php`:
  - Method: `calculate(CourtReview $review): Date`
  - Reads `review.matter.court_level`, `review.judgment_presence`, `review.decision_date`, `review.notified_date`
  - Decision logic:
    - `court_level = 'magistrate'` → window = 10 days
    - `court_level = 'first_instance'` → window = 30 days
    - `court_level = 'appeal'` or `'cassation'` → THROWS `UnsupportedCourtLevelException` (advisor hasn't specified yet — must be confirmed)
    - Other court_level values → THROWS
  - Start date logic:
    - `judgment_presence = 'wijahi'` → starts day after `decision_date`
    - `judgment_presence = 'mithla_wijahi'` → starts day after `notified_date`; if `notified_date` IS NULL, THROWS `MissingNotificationDateException`
    - `judgment_presence = 'ghyabi'` → **OPEN: throws `UnconfirmedRegulationException` until Khaldoun confirms; this is intentional fail-safe**
    - NULL → THROWS `MissingJudgmentPresenceException`
  - Returns Carbon date

- Update `CourtReviewObserver` (or equivalent):
  - When `court_review.appealable=true` is saved, invoke `AppealDeadlineService::calculate()` instead of any hardcoded offset
  - If calculation succeeds → create Obligation as before with computed `due_date`
  - If calculation throws (any of the 4 exception types) → create Obligation with `status='requires_input'`, attach the exception message to the Obligation's `notes` field, dispatch notification to Matter's Lead Lawyer

- Update reminder cron job:
  - Send alerts at T-50%, T-25%, T-10%, T-2 days of the window
  - For 10-day window: T-50%=day 5, T-25%=day 7-8, T-10%=day 9, T-2 days=day 8
  - For 30-day window: T-50%=day 15, T-25%=day 22-23, T-10%=day 27, T-2 days=day 28
  - Verify percentages calculated correctly for short windows (no negative days, no duplicates)

- **One-time data audit:** for existing CourtReviews created before this Surge:
  - Leave `judgment_presence` NULL, leave `notified_date` NULL
  - Existing Obligations they created retain their previously-calculated deadlines
  - Generate `validation/court_reviews_pre_deadline_fix_audit.csv` listing every CourtReview that may have an incorrect-window Obligation
  - Columns: court_review_id, matter_id, matter.title, current_obligation_due_date, current_obligation_id, suggested_action ("update_to_correct_window" or "verify_manually")
  - Founder reviews manually — does NOT auto-correct old data

**Tests:** ≥ 10 tests including
- magistrate + in-presence → 10-day deadline from day after decision_date
- magistrate + deemed-in-presence → 10-day from day after notified_date
- first_instance + in-presence → 30-day from day after decision_date
- first_instance + deemed-in-presence → 30-day from day after notified_date
- pure default (ghyabi) → throws UnconfirmedRegulationException (intentional)
- appeal court level → throws UnsupportedCourtLevelException
- missing notified_date for deemed-in-presence → throws + Obligation created with status='requires_input'
- changing matter.court_level after CourtReview exists → existing Obligation deadline recalculated
- reminder schedule fires at T-50%/T-25%/T-10%/T-2 for both 10-day and 30-day windows
- backfill audit CSV produced correctly for pre-existing data

**Acceptance:** Founder creates a Magistrate-level test Matter with in-presence judgment, verifies 10-day Obligation. Same with First Instance + deemed-in-presence, verifies 30-day from notified_date.

**Code attribution comment** (in `AppealDeadlineService.php`):
```php
/**
 * Court-level appeal window logic per advisor input from Khaldoun Khater 
 * (Al-Dujani Office, Amman), 2026-06-23 — see validation/02_advisor_meeting_log.md 
 * Conversation 2, Decision #18.
 *
 * CRITICAL: This service replaces the prior hardcoded 30-day assumption.
 * A 10-day Magistrate deadline rendered as 30 days = missed appeal = 
 * potential malpractice exposure for the firm using this product.
 */
```

---

### F-FIX-01.4 — Trust account adjustment description requirement

**Goal:** Adjustment-type entries on the trust ledger must carry a mandatory description; enforce at multiple layers per existing append-only pattern.

**Source:** Decisions #6 and #7.

**Scope:**

- Migration `enforce_description_on_trust_ledger_adjustments`:
  - Existing `trust_ledger_entries.description` column already exists; this Flow adds a CHECK constraint at the MySQL level: `description IS NOT NULL AND LENGTH(description) >= 10` when `entry_type = 'adjustment'`
  - For other entry types, description remains optional
  - Backward compat: existing adjustment rows without description (if any) are flagged in `validation/fix-01_trust_adjustments_no_description.csv` for founder manual review and updating; the CHECK constraint applies only to new rows via `NOT VALID` initially, then `VALIDATE CONSTRAINT` after audit

- Update `TrustLedgerEntry` model:
  - `validation` rule: when entry_type=adjustment, description required, min 10 chars
  - Custom validation message in AR + EN explaining the requirement (bar audit compliance)

- Update Filament `TrustLedgerEntryResource`:
  - Description field becomes required + min-length validated when entry_type=adjustment
  - Helper text explains regulatory requirement

**Tests:** ≥ 4 tests
- Pest: creating adjustment without description fails validation
- Pest: creating adjustment with < 10-char description fails
- Pest: creating adjustment with ≥ 10-char description succeeds
- Pest: non-adjustment entries don't require description

**Acceptance:** Founder attempts to create an adjustment entry without description; sees clear error in their UI locale.

---

### F-FIX-01.5 — PDPL explicit consent in onboarding

**Goal:** Add Jordan PDPL Law 24/2023 explicit consent step to client onboarding flow.

**Source:** Decision #8 and #21.

**Scope:**

- Migration `add_pdpl_consent_to_workspaces_and_clients`:
  - `workspaces.pdpl_consent_obtained` BOOLEAN default false
  - `workspaces.pdpl_consent_date` TIMESTAMP NULLABLE
  - `workspaces.pdpl_consent_text_version` VARCHAR(50) NULLABLE (which version of consent text was accepted)
  - For Contact-level data subjects (clients whose data is stored): `contacts.pdpl_consent_obtained`, `contacts.pdpl_consent_date`, `contacts.pdpl_consent_text_version`

- Onboarding flow update (S-06 wizard):
  - New step: "Cross-Border Data Transfer Consent"
  - Displays bilingual consent text explaining:
    - Where data is hosted (Frankfurt, Germany)
    - That this is a cross-border transfer under Jordan PDPL Law 24/2023
    - What categories of data are stored
    - Right to withdraw consent (with implication: account must be migrated or terminated)
  - User must check "I have read and consent" — workspace.pdpl_consent_obtained=true
  - Captures timestamp + version

- Per-client KYC update:
  - When adding a Contact, additional consent field: "Client has provided written consent to cross-border data storage" — Contact.pdpl_consent_obtained
  - File upload for the written consent document (link to Document via `consent_document_id`)

- Privacy Policy generation hook:
  - `pdpl_consent_text_version` references the version in `resources/lang/{ar,en}/pdpl_consent_v1.php`
  - When the consent text is updated (e.g., by paid lawyer), increment version; existing workspaces show a "Re-consent required" banner

**`[PENDING-PAID-LAWYER-DRAFT]`** items:
- The actual consent text in AR and EN — Khaldoun confirmed the principle; the paid lawyer (per Decision #23) drafts the legal text. Placeholder text used in development; refuses production launch until lawyer-drafted version installed (CI gate).

**Tests:** ≥ 5 tests
- Pest: workspace without pdpl_consent_obtained=true cannot create Matters with client data (gate enforced via middleware)
- Pest: re-consent flow triggers when text version updated
- Pest: contact-level consent required for clients (not for opposing counsel)
- Pest: consent text renders bilingually
- Pest: consent document upload links correctly

**Acceptance:** Founder walks through onboarding, sees PDPL step, accepts; verifies Matter creation blocked if consent not obtained.

---

### F-FIX-01.6 — Corporate KYC extensions

**Goal:** Add Company Registration Certificate and Signatory Authority document to KYC checklist for corporate (organization-type) Contacts.

**Source:** Decision #12.

**Scope:**

- Update `KycItemTypeEnum` for organization-type Contacts:
  - Add `commercial_registration_certificate` (شهادة تسجيل الشركة من وزارة الصناعة والتجارة)
  - Add `signatory_authority_document` (شهادة مفوضين بالتوقيع)
  - Existing values preserved

- Update `KycChecklistSeederForOrganization` to seed these two items when starting a KYC checklist on a corporate Contact

- Bilingual labels in `resources/lang/{ar,en}/kyc_items.php`

**Tests:** ≥ 3 tests
- Pest: starting KYC on organization Contact seeds both new items
- Pest: starting KYC on person Contact does NOT seed organization items
- Pest: AR + EN labels render correctly

**Acceptance:** Founder starts KYC on a corporate Contact, sees both new items in checklist.

---

### F-FIX-01.7 — AI Disclaimer language update

**Goal:** Replace generic AI disclaimer everywhere with Khaldoun's specific language.

**Source:** Decision #10.

**Scope:**

- Update `resources/lang/ar/ai_disclaimers.php`:
  ```php
  return [
      'standard' => 'هذه أداة صياغة داخلية للمحامين المؤهلين. لا تغني عن التحليل القانوني المستقل.',
      // ... other context-specific variants
  ];
  ```

- Update `resources/lang/en/ai_disclaimers.php`:
  ```php
  return [
      'standard' => 'This is an internal drafting aid for qualified legal professionals. It does not replace independent legal analysis.',
      // ...
  ];
  ```

- Audit every AI surface — AI Assistant panel (SURGE-04), AI document generation result (SURGE-10), AI suggestion diff view (SURGE-04) — to ensure the disclaimer is displayed prominently:
  - Footer of AI Assistant panel
  - Bottom of every generated document
  - Persistent in AI suggestion modals

- Prompt templates (`prompts/*.md` and `prompts/document_generation/*.md`) — append the disclaimer to the prompt instructions so the AI itself includes it in output

**`[PENDING-LEGAL-REVIEW]`** flag status:
- The disclaimer LANGUAGE is now advisor-validated by Khaldoun
- The FORMAL signoff (the `[LEGAL-REVIEW-APPROVED]` header on each prompt file) STILL requires the separately-engaged paid lawyer's attestation
- Khaldoun's input documented as informal validation in code comments referencing the meeting log

**Tests:** ≥ 3 tests
- Pest: AI Assistant panel renders new disclaimer in current locale
- Pest: AI-generated documents include disclaimer in output text
- Pest: disclaimer locale switches with workspace locale

**Acceptance:** Founder runs AI operations in AR locale and EN locale; both show correct disclaimer.

---

### F-FIX-01.8 — JordanCourtsSeeder CSV-driven

**Goal:** Replace hardcoded `JordanCourtsSeeder` array with CSV-driven seeder, in preparation for Khaldoun's incoming court list.

**Source:** Decision #24.

**Scope:**

- Refactor `database/seeders/JordanCourtsSeeder.php`:
  - Reads from `database/seeders/data/jordan_courts.csv`
  - CSV columns: `name_ar`, `name_en`, `court_type`, `jurisdiction_country` (default 'JO'), `jurisdiction_governorate`, `city`, `address`, `phone`, `notes`
  - Idempotent: upserts by (workspace_id, name_ar, jurisdiction_governorate) composite key
  - Logs count of courts created/updated/skipped

- Placeholder CSV in `database/seeders/data/jordan_courts.csv` with header row + 1 sample row — replaced when Khaldoun's CSV arrives

- `[PENDING-CSV-FROM-ADVISOR]` flag in the seeder docblock

- Pest test verifies seeder reads CSV correctly, handles missing CSV gracefully (skip seeding with log warning)

**Tests:** ≥ 3 tests

**Acceptance:** Seeder runs; produces courts matching CSV row count; missing CSV produces graceful skip with log entry.

---

### F-FIX-01.9 — D-15 ADR for pricing

**Goal:** Document the pricing decision in an architectural decision record per advisor input.

**Source:** Decision #14.

**Scope:**

- Create `decisions/D-15_pricing_ceiling.md`:

```markdown
# D-15 — Pricing Ceiling for Levant SMB Market

**Status:** Decided  
**Date:** 2026-06-23  
**Decider:** Founder, based on advisor input (Khaldoun Khater)

## Context

Pricing was previously left unspecified. Advisor input establishes a clear ceiling.

## Decision

For the Levant SMB target segment (firms of 2–10 lawyers in Jordan / Lebanon / Palestine / Iraq), per-user-per-month pricing must remain AT OR BELOW JOD 30 (~ USD 40).

## Rationale (Khaldoun's exact phrasing)

> "If you want a firm like ours to adopt this without months of partner arguments, price it per user. If it costs more than 20 to 30 JOD ($30–$40 USD) per lawyer per month, the firm owners will overthink it and say 'let's just stick to Excel.' Keep it under that psychological barrier."

## Pricing tier proposal (initial — to be refined with paying pilots)

- Starter: JOD 20/user/month — solo + 2-lawyer firms
- Standard: JOD 25/user/month — 3-10 lawyer firms
- Pro: JOD 30/user/month — full feature set including AI document generation, automations

## Implications

- All Stripe price configurations must conform to this ceiling for the Levant geography
- GCC pricing may be independently set higher (separate decision required if D-11 geography is re-evaluated)
- Marketing positioning emphasizes price affordability vs HAQQ ($80–250)

## Re-evaluation triggers

- Pilot customers indicate willingness to pay higher
- D-11 geography pivot to GCC adopts different pricing
- Material cost increase in infrastructure or LLM API pricing
```

**Tests:** None (documentation only)

**Acceptance:** ADR exists; pricing planning in Stripe config respects ceiling.

---

## Surge acceptance criteria

- [ ] F-FIX-01.1 through F-FIX-01.9 all built and tested
- [ ] All Pest tests green (minimum 40 new tests; under-testing pattern broken)
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated
- [ ] No regression in S-01 to S-09 tests
- [ ] Migration safety verified: all migrations applied to a copy of existing data without breakage
- [ ] Code comments referencing `validation/02_advisor_meeting_log.md` decision numbers everywhere relevant
- [ ] CLAUDE.md updated to v5 reflecting new entities + service + flagged items
- [ ] Founder sign-off recorded in `validation/02_advisor_meeting_log.md` under "Decisions implemented"

---

## What this Surge does NOT do (still pending)

- **Formal lawyer signoff on hard stops** — Khaldoun's input is informal validation; the formal `[LEGAL-REVIEW-APPROVED]` headers and `validation/0X_lawyer_signoff.md` files still require the separately-engaged paid lawyer (via Decision #23 introduction)
- **ToS / Privacy / DPA / AI Disclaimer drafts** — these are documents the paid lawyer drafts, not in scope here
- **Confirmation on `ghyabi` (pure default) appeal window** — Open question pending Khaldoun's reply
- **Hijri date support, lawyer departure handoff, conflict-of-interest checks** — Year-2
- **GCC procedural adjustments** — not Khaldoun's coverage; separate advisor needed if D-11 pivots

---

## What the Software Engineer agent should produce

1. **Gate Status Report (GSR)** for all 9 Flows — expected GREEN for build, RED for production deployment (waiting on formal lawyer signoff per the existing hard stops)
2. **TTP for F-FIX-01.3 FIRST** (the appeal-deadline correction) — this is the most urgent because it represents a real-world bug that would cost a law firm a case. Ship this independently and fast.
3. **TTPs for F-FIX-01.1, F-FIX-01.2, F-FIX-01.4 next** in parallel — they are mostly independent
4. **TTPs for F-FIX-01.5, F-FIX-01.6, F-FIX-01.7, F-FIX-01.8, F-FIX-01.9 last** — these are smaller and can be batched

The agent must produce ONE TTP at a time, awaiting founder confirmation between each.

For every code change, include a code comment citing the relevant `validation/02_advisor_meeting_log.md` decision number. This builds the audit trail that future maintainers (and the formal lawyer when secured) will follow.
