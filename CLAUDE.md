# CLAUDE.md — Project Memory for Claude Code

> Read this file first. It encodes everything you need to know before touching the codebase.
> **Last meaningful update: 2026-06-23 (late)** — SURGE-FIX-02 (Case Management refinement) + Path consolidation under `docs/`.
> **Path convention:** All planning, validation, decision, prompt, and spike artifacts live under `docs/` at the repo root. See §5 for the full folder layout.

---

## Update log

- **2026-06-17** — Initial draft, pre-build.
- **2026-06-21 (am)** — Stack reconciled after S-01: Laravel 13, Filament v5, Pest 4, ULIDs, Tailwind v4. Filament-everywhere pivot.
- **2026-06-21 (pm)** — D-09 strategic pivot. Depth-wedge superseded by breadth-coverage.
- **2026-06-22** — SURGE-10/11/12 added: Task Workflows, AI Doc Gen, Templates engine, Integrations. Added non-negotiables for rule engine isolation, OAuth token security, PWA constraints.
- **2026-06-23 (am)** — SURGE-13 inserted: Lawyer Management & Matter-Lawyer assignment gap fixed.
- **2026-06-23 (mid)** — SURGE-FIX-01 inserted. Khaldoun Khater (Al-Dujani, Amman) practitioner input. 24 decisions logged. New entity: ExpertReport. New service: AppealDeadlineService. Court-level-dependent deadlines.
- **2026-06-23 (pm)** — SURGE-FIX-02 inserted. Khaldoun competitive review of HAQQ.ai plus pain-point conversations produced 6 refinement Flows (Hearing Session History, Court Review Trainee Dispatch, Forked Matter Creation, Contextual Quick Timer, Hearing Postponement Chain UX, Case Management Navigation Refinement). New entity: HearingActionItem. New services: HearingSessionService, CourtReviewDispatchService, QuickTimerService. New enum: MatterTypeEnum (14 cases across Transactional and Litigation tracks). 6 new decisions logged (#26-#31) in `docs/validation/02_advisor_meeting_log.md` Conversation 3.5.
- **2026-06-23 (late)** — Path consolidation. All planning, validation, decision, prompt, and spike artifacts now live under `docs/` at the repo root (previously scattered at top level). The §5 folder structure has been rewritten to reflect this. All references in code comments, engineer-agent prompts, and inter-file links use the `docs/` prefix. The old top-level paths (`planning/`, `validation/`, `decisions/`, `prompts/`, `spikes/`) are deprecated and must not be recreated.

---

## 1. Project

A **bilingual (Arabic/English), AI-native legal-OS** for Levant law firms (2–10 lawyers). Covers contracts, litigation, practice management, financials, CRM, workflow automation.

**Strategic posture (per D-09):** ~85–90% of HAQQ's nominal surface. Email/calendar via OAuth (NOT native). Mobile via PWA (NOT native). Depth advantage on documents + AI surfaces retained. Beating HAQQ in workflow precision on Levant-specific friction points where they ship one-size-fits-all forms.

**Single hardest test:** a lawyer imports a real `.docx`, edits clauses (mixed AR/EN, RTL/LTR), exports back, opens in Microsoft Word with formatting intact. If round-trip fidelity breaks, the wedge breaks.

**Active advisor relationship (informal):** Khaldoun Khater — commercial litigator at Al-Dujani Office, Amman; 12 years; cousin of founder; cousin favour-economy basis. Three substantive conversations to date plus competitive review of HAQQ.ai. **Khaldoun cannot sign formal compliance attestations** — those require the separately-engaged paid attorney (introduction pending per Decision #23).

**Hard stops on production deployment (do not waive):**

- Lawyer signoff on litigation procedural model (S-08)
- Lawyer + CPA signoff on Trust Accounts (S-09 F-09.2)
- Lawyer-drafted ToS / Privacy / DPA / AI Disclaimer (S-06)
- Lawyer-reviewed AI prompt templates (clause-level, S-04)
- Lawyer-reviewed AI document-generation templates (S-10 F-10.4, F-10.5)
- Lawyer-reviewed seed Document Templates (S-11 F-11.3)
- Paid-lawyer-drafted PDPL consent text (S-FIX-01.5)

**Informal-advisor input vs formal signoff:** Khaldoun's input IS captured in code (SURGE-FIX-01 and SURGE-FIX-02) and IS cited by decision number in code comments. This is informal validation, not formal attestation. Hard stops above remain RED for production until the formal paid lawyer signs the corresponding `docs/validation/0X_lawyer_signoff.md` files.

---

## 2. Tech Stack

| Layer | Value |
|---|---|
| Backend framework | Laravel 13.16.x |
| Admin/Customer panel | Filament v5.6.x |
| PHP | 8.3 (CI pin) |
| Frontend (Filament) | Livewire 3 + Tailwind v4 |
| Frontend (document editor) | Custom Livewire 3 + Blade + TipTap (S-03) |
| Frontend (Task Board) | Filament Page + Livewire + SortableJS (S-10) |
| Database | MySQL 8.x InnoDB, UTF8MB4 |
| ORM | Eloquent (Laravel 13) — soft deletes; ULID PKs |
| Primary key | ULID via `HasUlids` |
| Cache / queues | Redis (Cloudways-managed) |
| Object storage | S3-compatible |
| Cloud | Cloudways → DigitalOcean Frankfurt FRA1 (triggers PDPL consent) |
| CI/CD | GitHub Actions |
| Auth (web) | Google OAuth via Socialite v5 + optional SAML/OIDC per workspace (S-12) |
| Auth (API) | Laravel Sanctum |
| API style | REST + OpenAPI 3.0 (`openapi/spec.yaml`) |
| LLM provider | Anthropic Claude (behind `LlmProvider`) |
| Billing | Stripe via Laravel Cashier (per D-15 ceiling) |
| PDF | Spatie laravel-pdf |
| Money | Laravel `decimal` + bcmath. No floats |
| Email integration | Microsoft Graph + Gmail API (OAuth 2.0) |
| Calendar integration | Google Calendar API + MS Graph Calendar |
| SSO | `aacotroneo/laravel-saml2` (SAML 2.0 + OIDC) |
| PWA | Web App Manifest + Service Worker (static-asset cache only) |
| Encryption at rest | Laravel `encrypted` casts (OAuth tokens, IdP certs) |
| Tests | Pest 4.7.x |
| E2E | Pest Browser Plugin (Playwright) |
| Static analysis | Larastan 3.10 at level 6 |
| Code style | Laravel Pint |
| Localization | `laravel-lang/common` + `resources/lang/{ar,en}/*.php` |
| Default locale | `ar` (Arabic, RTL) |

---

## 3. Common Commands

```bash
# First-time
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed && npm run build

# Daily
php artisan serve
php artisan queue:work --queue=default,contracts,exports,reminders,invoices,automations,litigation,court_reviews
npm run dev

# Tests
./vendor/bin/pest
./vendor/bin/pest --filter=AppealDeadline
./vendor/bin/pest --filter=HearingSessionHistory
./vendor/bin/pest --filter=ForkedMatterCreation
./vendor/bin/pest tests/Browser
./vendor/bin/pest --parallel
./vendor/bin/pest --coverage --min=80

# Code quality
./vendor/bin/pint && ./vendor/bin/phpstan analyse

# Migrations
php artisan migrate
php artisan migrate:fresh --seed   # LOCAL ONLY

# Jurisdiction seeders — manual, gated
php artisan db:seed --class=JordanCourtsSeeder       # CSV-driven (S-FIX-01.8)
php artisan db:seed --class=JordanChartOfAccountsSeeder  # CPA-gated
php artisan db:seed --class=AiGenerationTemplatesSeeder  # Lawyer-gated
php artisan db:seed --class=DocumentTemplatesSeeder
php artisan db:seed --class=WorkflowBundlesSeeder

# OpenAPI
./vendor/bin/spectral lint openapi/spec.yaml
```

---

## 4. Architectural Non-Negotiables

These are constraints, not preferences.

### Core principles

1. Workspace scoping mandatory via `BelongsToWorkspace`
2. Policy + FormRequest on every write endpoint AND every Filament resource
3. Soft deletes everywhere — except append-only ledgers (§4b)
4. Audit columns everywhere — except append-only ledgers
5. Optimistic locking via `updated_at` → 409 mismatch
6. OpenAPI spec sync in same PR
7. Bilingual via Laravel localization only
8. No N+1 (eager load for lists)
9. No raw SQL outside Repository + integration test
10. Document editing is client-side (S-03 only)
11. Filament is the primary UI for ALL roles. §4a
12. ULIDs as primary keys via `HasUlids`
13. Polymorphic morph map registered in `AppServiceProvider` (short stable keys)
14. No floating-point arithmetic on money. §4c
15. Append-only ledgers immutable at multiple layers. §4b
16. Rule engine isolation (S-11): no `eval()`, no user-supplied code. §4d
17. OAuth tokens encrypted at rest (S-12). §4e
18. Integration over native for email/calendar/signature
19. PWA caches static assets only, NEVER data
20. Advisor input takes precedence over provisional defaults. §4f
21. Court-level-dependent deadline calculation. §4g
22. **Forked Matter Creation backward compatibility** (NEW v6). §4h
23. **Single active timer per user** (NEW v6). §4i
24. **Hearing session content requires status='held'** (NEW v6). §4j

### §4a. Filament-everywhere

Filament v5 is the primary UI for all authenticated users. Granular access via Policy methods. `canAccessPanel()` returns true for any workspace member. Exceptions: auth/login, document editor (S-03), Task Board (S-10), Stripe redirect (S-06), public share tokens (S-03), public invoice viewing (S-09), SSO callbacks (S-12), public ICS feed (S-12), onboarding wizard (S-06).

**S-FIX-02.6 update:** Case Management is now a unified navigation group containing 4 resources in order: MatterResource (sort 1), HearingResource (sort 2), CourtReviewResource (sort 3), ServiceLogEntryResource (sort 4). Bilingual group label "إدارة القضايا" / "Case Management".

### §4b. Append-only ledgers

| Ledger | Model | Surge |
|---|---|---|
| `ai_interactions` | AiInteraction | S-04 |
| `trust_ledger_entries` | TrustLedgerEntry | S-09 |
| `financial_audit_log` | FinancialAuditLog | S-09 |
| `ai_document_generations` | AiDocumentGeneration | S-10 |
| `automation_runs` | AutomationRun | S-11 |
| `audit_logs` | AuditLog | S-12 |

Enforcement: model lifecycle hook blocks `updating`/`deleting`; Policy denies; DB trigger (trust_ledger_entries only); multi-layer Pest tests.

**S-FIX-01 strengthening:** `trust_ledger_entries.description` REQUIRED (min 10 chars) when `entry_type='adjustment'` (Decision #7).

### §4c. Money handling

DECIMAL(15,2). Cast `'amount' => 'decimal:2'`. All arithmetic via bcmath or `MoneyService`. Currency separate `CHAR(3)`. No floats. No exceptions.

### §4d. Rule engine isolation (S-11)

Condition evaluator = fixed-operator interpreter (eq, neq, gt, lt, gte, lte, in, contains, is_null, is_not_null, and, or, not). No `eval()`. No arbitrary class instantiation. Action handlers fixed in `app/Services/AutomationActions/`.

### §4e. OAuth token security (S-12)

Tokens use `encrypted` cast. Never in API responses, audit diffs, or logs. Zeroed on disconnect before soft delete. Pest tests verify each protection actively.

### §4f. Advisor input precedence rule

When `docs/validation/02_advisor_meeting_log.md` contains a decision contradicting an earlier `[PROVISIONAL-FOUNDER-DECIDED]` placeholder, advisor input wins.

**Citation convention** for code comments:
```php
// Per advisor input from Khaldoun Khater, docs/validation/02_advisor_meeting_log.md 
// Conversation N, Decision #NN
```

This builds the audit trail the formal paid lawyer will follow when engaged.

**Boundary:** Advisor input does NOT satisfy formal lawyer signoff hard stops in §10. Khaldoun cannot sign production-deploy attestations.

### §4g. Court-level-dependent deadline calculation

No hardcoded appeal deadlines or court-procedural windows. All such calculations route through `AppealDeadlineService` or equivalents.

**Window depends on court level:**
- Magistrate Court (محاكم الصلح) → 10 days
- First Instance Court (محاكم البداية) → 30 days
- Appeal / Cassation → throws `UnsupportedCourtLevelException` (advisor input pending)

**Start date depends on `judgment_presence`:**
- `wijahi` (وجاهي — in-presence) → day after `decision_date`
- `mithla_wijahi` (بمثابة الوجاهي — deemed in-presence) → day after `notified_date`
- `ghyabi` (غيابي — pure default) → throws `UnconfirmedRegulationException` (pending advisor confirmation)
- NULL → throws `MissingJudgmentPresenceException`

When the service throws, the caller creates Obligation with `status='requires_input'` + notifies Lead Lawyer. **No silent fallback to hardcoded windows.**

Per Decision #18, Conversation 2, 2026-06-23. **A 10-day deadline rendered as 30 days = missed appeal = malpractice exposure for the firm using this product.** This rule exists to prevent that.

### §4h. Forked Matter Creation backward compatibility (NEW v6)

The `Matter.matter_type` field (added in S-FIX-02.3 via `MatterTypeEnum`) is the source of truth for whether a Matter is Transactional or Litigation. The legacy `is_litigation` boolean column is preserved for backward compatibility with pre-S-FIX-02 data and legacy API clients but is DERIVED from `matter_type` going forward.

**Rules:**
- New Matter creation requires `matter_type` to be set via the stepped wizard or API. Legacy API clients passing `is_litigation` boolean receive a deprecation warning header but the request succeeds with auto-derived `matter_type`.
- Server-side validation enforces field/track consistency: Transactional matters cannot have `court_id`, `case_number`, `judge_id`, `opposing_counsel_id` (422 if supplied). Litigation matters cannot have `target_closing_date`, `deal_value_amount`, `expected_document_types` (422 if supplied).
- Existing Matters with NULL `matter_type` (pre-migration data) continue to function. The backfill migration assigns conservative defaults (`is_litigation=true` → `commercial_litigation`; `false` → `commercial_contracts`) and produces `docs/validation/fix-02_matter_type_backfill_audit.csv` listing every assigned default for founder manual review. NO automatic correction of legacy data.
- The `is_litigation` accessor on the Matter model derives from `matter_type` when present; falls back to the legacy column when NULL.

Per Decision #26, Conversation 3.5.

### §4i. Single active timer per user (NEW v6)

The `QuickTimerService` (added in S-FIX-02.4) enforces that any user has at most ONE active `TimeEntry` (where `ended_at IS NULL`) at any time across the entire system.

**Rules:**
- Starting a new timer when user has an active timer: returns 409 with bilingual error message "You have an active timer; stop it before starting a new one" / "لديك مؤقت نشط بالفعل. أوقفه قبل بدء واحد جديد"
- Active timer enforcement is at the service layer, not just the database. Race condition between two concurrent start requests resolved by service-level lock (DB row lock on the user's existing active TimeEntry).
- The Active Timer Indicator widget rendered in Filament chrome via `panels::user-menu.before` render hook shows the active timer state and provides a Stop action visible from any page.
- Workspace isolation applies: a user with active timer in Workspace A cannot also start a timer in Workspace B. Cross-workspace active timer prevented at service layer.

Per Decision #31, Conversation 3.5.

### §4j. Hearing session content requires status='held' (NEW v6)

The session content fields on Hearing (`judge_statement_ar`, `judge_statement_en`, `outcome_summary_ar`, `outcome_summary_en`, `our_submissions_made`, `opposing_submissions_made`, `next_session_required_actions_ar`, `next_session_required_actions_en`, `session_attended_by`) and the `HearingActionItem` records can only be created/edited when the Hearing's `status === 'held'`.

**Rules:**
- API endpoints reject session content writes on Hearings with non-held status with 422 + bilingual message "Session outcome can only be recorded for hearings that have been held" / "لا يمكن تسجيل نتيجة جلسة لم تنعقد بعد"
- Filament form's "Session Outcome" tab visibility condition: `fn($record) => $record && $record->status === 'held'`
- HearingActionItem auto-creates a linked Obligation via `HearingActionItemObserver` on the `creating` event. Status changes propagate one-way: action_item.status → obligation.status. Soft-deleting an action item marks the linked Obligation as 'waived' with reason "Action item removed".
- Hearing status transition from 'held' back to 'scheduled' (i.e., postponement after holding) preserves existing session content as historical record. New session content is captured on the new Hearing created as the postponement.
- Only users in `session_attended_by` array OR the Matter's lead lawyer can edit session content (enforced via `HearingPolicy`).

Per Decision #28, Conversation 3.5.

---

## 5. Folder Structure

```
app/
  Concerns/                  Traits (BelongsToWorkspace, AppendOnlyLedger)
  Enums/                     PHP enums (incl. LitigationStatusEnum, HearingTypeEnum, JudgmentPresenceEnum, CourtLevelEnum, KycItemTypeEnum, MatterTypeEnum)
  Exceptions/                Custom exceptions (UnsupportedCourtLevelException, MissingJudgmentPresenceException, UnconfirmedRegulationException)
  Filament/
    Resources/               Filament resources (incl. ExpertReportResource, HearingResource with session outcome tab, CourtReviewResource with dispatch fields)
    Pages/                   Custom Filament pages (incl. MyCourtTasksToday)
    Widgets/                 Dashboard + chrome widgets (incl. ActiveTimerIndicator)
  Http/
    Controllers/Api/V1/      Versioned API controllers
    Controllers/Web/         Non-Filament web controllers
    Requests/                FormRequests (incl. StoreMatterRequest with track validation)
    Resources/               API resources (strip OAuth tokens)
    Middleware/              SetLocale, EnsureWorkspaceSelected, EnsurePdplConsentObtained
  Livewire/                  Custom Livewire components (incl. SessionsTimeline)
  Models/                    Eloquent models (incl. ExpertReport, HearingActionItem)
  Services/                  Domain services (incl. AppealDeadlineService, ExpertReportService, HearingSessionService, CourtReviewDispatchService, QuickTimerService)
    AutomationActions/       Action handlers (S-11)
  Policies/                  <Entity>Policy (incl. HearingActionItemPolicy, CourtReviewPolicy with dispatch/complete methods)
  Jobs/                      Queue jobs
  Observers/                 Eloquent observers (incl. CourtReviewObserver, ExpertReportObserver, HearingActionItemObserver)
  Mail/                      Bilingual mailables
  Console/Kernel.php         Scheduled tasks
  Providers/                 AppServiceProvider (morph map), AppPanelProvider (Case Management navigation group)

database/
  migrations/
  seeders/
    *.php                    
    data/
      jordan_courts.csv      # From advisor (Khaldoun)
  factories/

resources/
  views/                     auth, documents, invoices, filament, sso, ics, onboarding, livewire/sessions-timeline
  lang/ar/                   litigation_status, hearing_type, kyc_items, ai_disclaimers, pdpl_consent_v1, expert_reports, appeal_deadlines, navigation, matter_types, hearings, sessions_timeline, hearing_action_items, quick_timer, etc.
  lang/en/                   (same files)
  css/, js/
  pwa/                       manifest, service worker, icons

routes/                      web.php, api.php, console.php

tests/
  Unit/
  Feature/
    Api/V1/, Filament/, Models/, Policies/, Services/, Concerns/, Locale/, Middleware/
    Financial/
    Litigation/              (incl. AppealDeadlineCalculationTest, ExpertReportObjectionTest, HearingSessionHistoryTest, CourtReviewDispatchTest, HearingPostponementChainTest)
    Matters/                 (incl. ForkedCreationTest)
    TimeTracking/            (incl. ContextualTimerTest)
    Compliance/              (Append-only multi-layer)
    Automations/
    Integrations/
  Browser/
  fixtures/

openapi/spec.yaml

docs/                        ALL PROJECT DOCUMENTATION (planning, validation, decisions, prompts, spikes)
  README.md                  Index of docs/ subfolders for new readers
  decisions/                 Architecture Decision Records (ADRs) — append-only, supersede via new ADR
    D-09_breadth_pivot.md
    D-15_pricing_ceiling.md
  prompts/                   Clause-level AI prompts (Lawyer-gated for production)
    document_generation/     Full-document AI generation templates (Lawyer-gated)
  spikes/                    Throwaway research notes
  validation/                Founder waiver, advisor logs, lawyer signoffs, backfill audits
    00_FOUNDER_WAIVER.md
    01_haqq_ai_test_protocol.md
    02_advisor_meeting_log.md  # SOURCE OF TRUTH for advisor input (Conversations 1, 2, 3, 3.5)
    06_legal_docs/             Pending paid lawyer
    08_litigation_lawyer_signoff.md
    09_trust_account_lawyer_signoff.md
    09_trust_account_cpa_signoff.md
    10_ai_generation_lawyer_signoff.md
    13_lawyer_management_advisor_review.md
    court_reviews_pre_deadline_fix_audit.csv          # From S-FIX-01.3
    fix-02_matter_type_backfill_audit.csv             # From S-FIX-02.3
    fix-02_evidence_hearings_to_reclassify.csv        # From S-FIX-01.1 (if any)
  planning/                  Roadmap, Surge plans, Flow specs, Wave-Ready Packages
    00_MVP_ROADMAP_v0.2.md
    HAQQ_COVERAGE_GAP_ANALYSIS.md
    SURGE-01-... through SURGE-09-... (shipped)
    SURGE-FIX-01-Advisor-Corrections.md  (shipped)
    SURGE-FIX-02-Case-Management-Refinement.md  # ACTIVE — current priority
    F-FIX-02-01-Hearing-Session-History.md
    F-FIX-02-01-Hearing-Session-History-WRP-1.md
    F-FIX-02-02-Court-Review-Trainee-Dispatch.md
    F-FIX-02-03-Forked-Matter-Creation.md
    F-FIX-02-04-Contextual-Quick-Timer.md
    F-FIX-02-05-Hearing-Postponement-Chain-UX.md
    F-FIX-02-06-Case-Management-Navigation-Refinement.md
    SURGE-10 through SURGE-13 (queued)
    SURGE-VERIFY.md
```

**Path convention (NEW in v6):** All planning, validation, decision, prompt, and spike artifacts live under the `docs/` folder at the repo root. References from code comments, README files, and engineer-agent prompts MUST use the `docs/` prefix. The old top-level paths (`planning/`, `validation/`, etc.) are deprecated and must not be created.

---

## 6. Naming Conventions

| Thing | Convention |
|---|---|
| Primary key | ULID via `HasUlids` |
| FK column | `string(26)` snake_case ending in `_id` |
| Eloquent model | Singular PascalCase — `ExpertReport`, `HearingActionItem`, `LawyerProfile` |
| Table | Plural snake_case — `expert_reports`, `hearing_action_items`, `lawyer_profiles` |
| Morph key | Short snake_case — `'expert_report'`, `'matter'`, `'task'`, `'hearing_action_item'` |
| Migration | Verb-shaped, timestamped |
| Filament resource | `<Entity>Resource` |
| Filament page | `<Name>Page` (e.g., `MyCourtTasksToday`) |
| Filament widget | `<Name>` (e.g., `ActiveTimerIndicator`) |
| Policy | `<Entity>Policy` |
| FormRequest | `<Action><Entity>Request` (e.g., `StoreMatterRequest`) |
| API controller | `Api\V1\<Entity>Controller` |
| Service | `<Entity\|Domain>Service` — `AppealDeadlineService`, `HearingSessionService`, `CourtReviewDispatchService`, `QuickTimerService` |
| Observer | `<Entity>Observer` — `CourtReviewObserver`, `HearingActionItemObserver` |
| Exception | `<Reason>Exception` — `UnsupportedCourtLevelException` |
| PHP enum | `<Entity>Enum` — `LitigationStatusEnum`, `JudgmentPresenceEnum`, `MatterTypeEnum` |
| Localization domain | snake_case filename |
| Route name | dotted snake_case |

---

## 7. Domain Glossary

### Core (S-01 to S-06)

- **Workspace, WorkspaceMember, Role**
- **Contact, Client, Counterparty, OpposingCounsel**
- **Matter** — Commercial OR litigation. `court_level` field added in S-FIX-01.3. **`matter_type` field added in S-FIX-02.3** — enum-driven track (Transactional vs Litigation). `is_litigation` boolean preserved for backward compat; derived from `matter_type` going forward.
- **Document, DocumentVersion, DocumentClause**
- **ContractMetadata**
- **Obligation**
- **LibraryClause**
- **AiInteraction** (append-only)
- **Subscription**

### Practice Management (S-07)

- **Task, TimeEntry, KycChecklist, KycItem, KpiTarget, Team, SmartList**
- **KycItem.item_type** extended in S-FIX-01.6 with `commercial_registration_certificate`, `signatory_authority_document` for corporate Contacts
- **TimeEntry** extended in S-FIX-02.4 with `started_via_context` enum for analytics

### Litigation (S-08) `[HARD-STOP-LAWYER-REQUIRED for production]`

- **Court, Judge, Hearing, CourtReview, ServiceLogEntry**
- **CourtReview** extended in S-FIX-01.3 with `judgment_presence` enum (wijahi / mithla_wijahi / ghyabi) and `notified_date`. Further extended in S-FIX-02.2 with dispatch fields (`dispatched_to_user_id`, `dispatched_at`, `completed_by_user_id`, `location_in_courthouse_*`, `expected_outcome_*`, `completion_notes`, `evidence_document_id`).
- **Hearing.hearing_type** extended in S-FIX-01.1 — `plaintiff_evidence`, `defendant_evidence`, `notification_session` replace generic `evidence`
- **Matter.litigation_status** extended in S-FIX-01.1 — `fee_payment_and_registration`, `notification_pending`, `referred_to_expert` added
- **Hearing extended in S-FIX-02.1** with session content fields (`judge_statement_*`, `outcome_summary_*`, `our_submissions_made`, `opposing_submissions_made`, `next_session_required_actions_*`, `session_attended_by`). Editable only when `status='held'` per §4j.
- **Hearing extended in S-FIX-02.5** with postponement metadata (`postponement_reason_ar`, `postponement_reason_en`, `postponement_initiated_by` enum).
- **NEW: ExpertReport** (S-FIX-01.2) — first-class entity. 8-day mandatory objection deadline. Auto-creates Obligation on receipt. Models the "Expert Phase" of Jordanian commercial litigation.
- **NEW: HearingActionItem** (S-FIX-02.1) — follow-up actions captured during a held hearing; auto-creates Obligation via observer; status changes propagate to Obligation.

### Financial (S-09) `[F-09.2 HARD-STOP-LAWYER+CPA-REQUIRED for production]`

- **Account, TrustAccount, TrustLedgerEntry, JournalEntry, JournalEntryLine, Invoice, InvoiceLine, Receipt**
- **TrustLedgerEntry.description** REQUIRED (min 10 chars) for `entry_type='adjustment'` (S-FIX-01.4)

### CRM (S-09)

- **Pipeline, Lead, Opportunity**

### Workflow & Generation (S-10)

- **TaskWorkflow, TaskWorkflowStage, TaskWorkflowTransition, TaskWorkflowApproval**
- **AiDocumentGeneration, AiGenerationTemplate**

### Platform engine (S-11)

- **FormTemplate, FormTemplateField, FormSubmission**
- **Automation, AutomationAction, AutomationRun**
- **DocumentTemplate**
- **WorkflowBundle**

### Integration (S-12)

- **EmailIntegration, EmailAttachment, CalendarIntegration, ExternalCalendarEvent, WorkspaceSsoConfig, AuditLog**

### Lawyer Management (S-13)

- **LawyerProfile, MatterLawyer** (pivot with `lead`/`supporting` roles)

### Critical services

- **`AppealDeadlineService`** (S-FIX-01.3) — court-level-dependent calculation. See §4g.
- **`ExpertReportService`** (S-FIX-01.2) — 8-day objection deadline; state transitions
- **`HearingSessionService`** (S-FIX-02.1) — records outcome, manages action items, returns sessions timeline for Matter
- **`CourtReviewDispatchService`** (S-FIX-02.2) — dispatches Court Review to user, manages completion workflow
- **`QuickTimerService`** (S-FIX-02.4) — single-active-timer enforcement per §4i

### Words NOT in our domain (permanently OUT)

- "Case" (we say Matter)
- "Pleading" (out — generate via AI)
- **Native Email** — `emails` for native content (out — EmailIntegration OAuth)
- **Native Calendar** — `calendar_events` with recurrence (out — CalendarIntegration OAuth)
- "Chat / Messaging entity"
- "Native Mobile App" — PWA only
- "Visual workflow designer with drag-drop nodes"
- "Custom code/script actions" (security)
- "Cron triggers below hourly granularity"
- "Webhook actions"

---

## 8. Localization Rules

- Default locale `ar`
- Every user-facing string in `resources/lang/{ar,en}/<domain>.php`
- Framework strings from `laravel-lang/common`
- `SetLocale` middleware sets `<html dir>` and `<html lang>`
- Tailwind v4 logical properties handle RTL
- Dates: Gregorian only at MVP
- Numbers: Latin digits both locales
- Currency: ISO code + amount
- AR translations NEVER auto-generated
- Mixed-direction content respects per-paragraph `dir`
- Court / Judge / Expert names stored bilingual (`name_ar`, `name_en`)
- Workflow stages, Form fields, Automation names — all bilingual
- Email integration UI in user's locale; email content in original language
- **Litigation enum labels (S-FIX-01.1)** — AR is authoritative form for court communications; EN for client-facing
- **AI Disclaimer (S-FIX-01.7)** — standardized text both locales per advisor input
- **PDPL Consent text (S-FIX-01.5)** — versioned per revision; placeholder until paid lawyer drafts production version
- **MatterTypeEnum labels (S-FIX-02.3)** — 14 cases across 2 tracks. Both locales required. Stored in `resources/lang/{ar,en}/matter_types.php`.
- **Hearing session content (S-FIX-02.1)** — judge_statement_ar required for capture; judge_statement_en optional translation for client reporting. Stored in `resources/lang/{ar,en}/hearings.php`.
- **Sessions Timeline UI (S-FIX-02.1)** — bilingual via `resources/lang/{ar,en}/sessions_timeline.php`.
- **Hearing Action Items (S-FIX-02.1)** — description_ar required, description_en optional. Status labels bilingual.
- **Court Review dispatch (S-FIX-02.2)** — location_in_courthouse_ar and expected_outcome_ar required for dispatch.
- **Quick Timer (S-FIX-02.4)** — UI labels and toast messages bilingual via `resources/lang/{ar,en}/quick_timer.php`.
- **Navigation labels (S-FIX-02.6)** — Case Management group + 4 sub-resources bilingual via `resources/lang/{ar,en}/navigation.php`.

---

## 9. Testing & Quality Gates

Every PR must pass:

1. Pest test suite green
2. Pint clean
3. Larastan level 6 clean
4. OpenAPI spec valid + in sync
5. Workspace isolation tested
6. AR locale smoke test
7. Filament resource tests
8. Append-only enforcement (6 ledgers, multi-layer)
9. Financial idempotency
10. Money precision
11. Litigation procedural tests (per jurisdiction, per court-level)
12. Task Workflow backward compatibility (S-10)
13. AI document generation refuses non-approved templates in production
14. Rule engine condition-evaluator (S-11) — every operator, every type coercion
15. Automation loop-prevention
16. Action handler isolation
17. OAuth token security (encryption, redaction, zeroing)
18. SAML signature validation
19. ICS export RFC 5545 compliance
20. PWA manifest valid; service worker static-only
21. AppealDeadlineService calculation tests (S-FIX-01.3) — minimum 10 cases
22. ExpertReport objection countdown tests (S-FIX-01.2) — including Observer auto-creating Obligation
23. Trust ledger adjustment description enforcement (S-FIX-01.4) — multi-layer
24. PDPL consent gate tests (S-FIX-01.5) — Matter creation blocked when consent not obtained
25. Advisor-citation comment audit — grep verifies all new code added per S-FIX-01 + S-FIX-02 has `docs/validation/02_advisor_meeting_log.md` decision-number citations
26. **Hearing session content tests** (S-FIX-02.1) — minimum 12 cases including status='held' enforcement, observer auto-Obligation creation, policy enforcement on session_attended_by
27. **Court Review dispatch tests** (S-FIX-02.2) — minimum 8 cases including mobile breakpoint rendering
28. **Forked Matter Creation tests** (S-FIX-02.3) — minimum 10 cases including wizard renders, API track validation, backfill audit CSV produced, legacy `is_litigation` deprecation warning
29. **Contextual Timer tests** (S-FIX-02.4) — minimum 8 cases including single-active-timer constraint enforcement, race condition handling, mobile breakpoint
30. **Hearing Postponement Chain tests** (S-FIX-02.5) — minimum 5 cases including circular reference detection
31. **Case Management Navigation tests** (S-FIX-02.6) — minimum 4 cases including locale-respecting labels

**Test discipline:** SURGE-FIX-02 alone must add ≥ 50 tests. Cumulative target after S-FIX-02: ≥ 727 tests. Industry baseline for system this size: 2,500-4,000. Gap remains; flag as standing risk.

---

## 10. What NOT to Do

### Schema-level — STILL REJECT

- **Native email module:** `emails` table for native content
- **Native calendar module:** `calendar_events` with full recurrence
- **Mobile app code:** iOS / Android / React Native
- **Native messaging/chat:** `messages`, `chat_threads`
- **Custom code execution in automations**
- **Webhook trigger receivers** + **Outbound webhook actions** (Year-2)
- **Service Worker caching data** (security/UX)
- **AI extraction tables populated without user review**
- **OAuth token visibility in any non-encrypted form**
- **Hardcoded appeal deadlines or court-procedural windows** — must route through service classes
- **Adjustment entries on trust ledger without ≥ 10-char description** — multi-layer enforced
- **Matter creation without `matter_type`** (S-FIX-02.3) — legacy `is_litigation`-only requests trigger deprecation warning but still must derive a matter_type
- **Multiple concurrent active TimeEntries per user** (S-FIX-02.4) — service layer enforces single-active constraint
- **Hearing session content writes when status != 'held'** (S-FIX-02.1) — multi-layer rejection (API, Filament, Policy)
- **Hardcoded matter type field rules** — must use MatterTypeEnum::isTransactional() / isLitigation() / track() methods

### Hard stops on production deployment

| Hard Stop | Gates | Resolution file |
|---|---|---|
| Lawyer-drafted ToS / Privacy / DPA / AI Disclaimer | S-06 paid launch | `docs/validation/06_legal_docs/*` |
| Lawyer-reviewed AI prompt templates (clause-level) | S-04 real-user exposure | `docs/prompts/*.md` headers |
| Lawyer signoff on litigation procedural model | S-08 production | `docs/validation/08_litigation_lawyer_signoff.md` |
| Lawyer + CPA signoff on Trust Accounts | S-09 F-09.2 production | `docs/validation/09_trust_account_lawyer_signoff.md` + `docs/validation/09_trust_account_cpa_signoff.md` |
| Lawyer-reviewed AI doc-gen templates | S-10 F-10.4 production | `docs/prompts/document_generation/*.md` headers |
| Lawyer-reviewed in-DB AI gen templates | S-10 F-10.5 | `ai_generation_templates.legal_review_status='approved'` |
| Lawyer-reviewed seed Document Templates | S-11 F-11.3 production | Per template advisor signoff |
| Paid-lawyer-drafted PDPL consent text | Production launch | `docs/validation/06_legal_docs/pdpl_consent_v1.md` |
| KYC checklist content review | S-07 F-07.3 — recommended | `[ADVISOR-REVIEW-RECOMMENDED]` markers |

**Khaldoun's input does NOT clear these hard stops.** Cousin favour-economy basis; cannot sign external compliance attestations. Formal signoff requires separately-engaged paid lawyer (introduction pending per Decision #23).

### Operational

- No LLM calls direct from browser
- No LLM keys outside `.env`
- No logging prompt content (except in audit ledger rows)
- No bypass of `BelongsToWorkspace` scope
- No commit of `.env`, real client data, API keys
- No auto-translate Arabic strings
- No UPDATE or DELETE on append-only ledgers
- No floating-point arithmetic on money
- No seeding jurisdiction data without lawyer/CPA signoff
- No user-supplied expressions to `eval`/`assert`/`compile`
- No unencrypted OAuth tokens
- No PWA service worker data caching
- No hardcoded appeal deadlines — always route through `AppealDeadlineService`
- No silent default to 30-day window when court_level or judgment_presence unknown — throw and surface to lawyer
- No adjustment trust_ledger_entries without ≥ 10-char description
- **No Hearing session content edits unless status='held'** (S-FIX-02.1)
- **No HearingActionItem creation without auto-creating linked Obligation** (S-FIX-02.1)
- **No Matter creation without matter_type via API or Filament** (S-FIX-02.3)
- **No second active timer per user** (S-FIX-02.4)
- **No Case Management resource added to a navigation group other than 'Case Management'** (S-FIX-02.6)

### Process

- No work on a Surge whose upstream gates not satisfied
- No skipping Pest tests
- No modifying `CLAUDE.md` without founder direction — append-only Update log
- No modifying `docs/decisions/` files; supersede via new ADR
- No modifying `docs/validation/02_advisor_meeting_log.md` — append only with dated entries
- No marking a Surge "Production Ready" if applicable hard stop unresolved
- No implementing schema corrections contradicting `docs/validation/02_advisor_meeting_log.md` without raising contradiction to founder for advisor follow-up

---

## 11. AODC Pipeline Reference

| Location | Contents |
|---|---|
| `docs/README.md` | Index of docs/ subfolders for new readers |
| `docs/planning/00_MVP_ROADMAP_v0.2.md` | Master plan v0.2 (active) |
| `docs/planning/HAQQ_COVERAGE_GAP_ANALYSIS.md` | Coverage map |
| `docs/planning/SURGE-NN-*.md` | Per-Surge plans (S-01 to S-09 shipped) |
| `docs/planning/SURGE-FIX-01-Advisor-Corrections.md` | Shipped |
| `docs/planning/SURGE-FIX-02-Case-Management-Refinement.md` | **ACTIVE — current priority** |
| `docs/planning/F-FIX-02-NN-*.md` | Flow specs for SURGE-FIX-02 |
| `docs/planning/F-FIX-02-01-Hearing-Session-History-WRP-1.md` | Wave-Ready Package for F-FIX-02.1 |
| `docs/planning/SURGE-10/11/12/13` | Queued after S-FIX-02 |
| `docs/planning/SURGE-VERIFY.md` | Standing recommendation |
| `docs/validation/00_FOUNDER_WAIVER.md` | Operating waiver |
| `docs/validation/02_advisor_meeting_log.md` | **Source of truth for advisor input (Conversations 1, 2, 3, 3.5)** |
| `docs/validation/08_litigation_lawyer_signoff.md` | Pending paid lawyer |
| `docs/validation/09_trust_account_lawyer_signoff.md` | Pending paid lawyer + CPA |
| `docs/validation/10_ai_generation_lawyer_signoff.md` | Pending paid lawyer |
| `docs/decisions/D-09_breadth_pivot.md` | Strategic pivot ADR |
| `docs/decisions/D-15_pricing_ceiling.md` | Pricing per advisor (JOD 20–30) |
| `docs/prompts/` | Clause-level AI prompts |
| `docs/prompts/document_generation/` | Full-document AI generation templates |
| `docs/spikes/` | Throwaway research |

---

## 12. When in doubt

Order of precedence:

1. **This file (`CLAUDE.md`)**
2. **`docs/decisions/D-NN.md` ADRs** in reverse chronological order
3. **`docs/validation/02_advisor_meeting_log.md`** — practitioner-validated decisions cited by number
4. **`docs/validation/00_FOUNDER_WAIVER.md`** — operating mode
5. **`docs/planning/00_MVP_ROADMAP_v0.2.md`**
6. **The specific Surge plan**
7. **The Tech Task Package**
8. **The Founder**

Do not invent answers. Do not guess at intent. Stop, surface the question, wait for direction.

---

*End of CLAUDE.md v6. Welcome to the project.*
