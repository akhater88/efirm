# CLAUDE.md — Project Memory for Claude Code

> Read this file first. It encodes everything you need to know before touching the codebase.
> **Last meaningful update: 2026-06-23** (SURGE-FIX-01 — advisor corrections from Khaldoun Khater)

---

## Update log

- **2026-06-17** — Initial draft, pre-build.
- **2026-06-21 (am)** — Stack reconciled after S-01: Laravel 13, Filament v5, Pest 4, ULIDs, Tailwind v4. Filament-everywhere pivot.
- **2026-06-21 (pm)** — D-09 strategic pivot. Depth-wedge superseded by breadth-coverage.
- **2026-06-22** — SURGE-10/11/12 added: Task Workflows, AI Doc Gen, Templates engine, Integrations. Added non-negotiables for rule engine isolation, OAuth token security, PWA constraints.
- **2026-06-23 (am)** — SURGE-13 inserted: Lawyer Management & Matter-Lawyer assignment gap fixed.
- **2026-06-23 (pm)** — SURGE-FIX-01 inserted as priority correction Surge. Khaldoun Khater (Al-Dujani, Amman) provided practitioner input via informal advisor relationship. 24 decisions logged in `validation/02_advisor_meeting_log.md`. New entity: ExpertReport. New service: AppealDeadlineService. Enum extensions: litigation_status, hearing_type, kyc_item_type, judgment_presence. New precedence rule: advisor input cited by decision number takes precedence over earlier provisional defaults.

---

## 1. Project

A **bilingual (Arabic/English), AI-native legal-OS** for Levant law firms (2–10 lawyers). Covers contracts, litigation, practice management, financials, CRM, workflow automation.

**Strategic posture (per D-09):** ~85–90% of HAQQ's nominal surface. Email/calendar via OAuth (NOT native). Mobile via PWA (NOT native). Depth advantage on documents + AI surfaces retained.

**Single hardest test:** a lawyer imports a real `.docx`, edits clauses (mixed AR/EN, RTL/LTR), exports back, opens in Microsoft Word with formatting intact. If round-trip fidelity breaks, the wedge breaks.

**Active advisor relationship (informal):** Khaldoun Khater — commercial litigator at Al-Dujani Office, Amman; 12 years; cousin of founder; cousin favour-economy basis. Ongoing practitioner validation. **Khaldoun cannot sign formal compliance attestations** — those require the separately-engaged paid attorney (introduction pending).

**Hard stops on production deployment (do not waive):**

- Lawyer signoff on litigation procedural model (S-08)
- Lawyer + CPA signoff on Trust Accounts (S-09 F-09.2)
- Lawyer-drafted ToS / Privacy / DPA / AI Disclaimer (S-06)
- Lawyer-reviewed AI prompt templates (clause-level, S-04)
- Lawyer-reviewed AI document-generation templates (S-10 F-10.4, F-10.5)
- Lawyer-reviewed seed Document Templates (S-11 F-11.3)
- Paid-lawyer-drafted PDPL consent text (S-FIX-01.5)

**Informal-advisor input vs formal signoff:** Khaldoun's input IS captured in code (SURGE-FIX-01) and IS cited by decision number in code comments. This is informal validation, not formal attestation. Hard stops above remain RED for production until the formal paid lawyer signs the corresponding `validation/0X_lawyer_signoff.md` files.

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
php artisan queue:work --queue=default,contracts,exports,reminders,invoices,automations,litigation
npm run dev

# Tests
./vendor/bin/pest
./vendor/bin/pest --filter=AppealDeadline
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
18. Integration over native for email/calendar/signature. §4
19. PWA caches static assets only, NEVER data
20. **Advisor input takes precedence over provisional defaults** (NEW v5). §4f
21. **Court-level-dependent deadline calculation** (NEW v5). §4g

### §4a. Filament-everywhere

Filament v5 is the primary UI for all authenticated users. Granular access via Policy methods. `canAccessPanel()` returns true for any workspace member. Exceptions: auth/login, document editor (S-03), Task Board (S-10), Stripe redirect (S-06), public share tokens (S-03), public invoice viewing (S-09), SSO callbacks (S-12), public ICS feed (S-12), onboarding wizard (S-06).

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

### §4f. Advisor input precedence rule (NEW v5)

When `validation/02_advisor_meeting_log.md` contains a decision contradicting an earlier `[PROVISIONAL-FOUNDER-DECIDED]` placeholder, advisor input wins.

**Citation convention** for code comments:
```php
// Per advisor input from Khaldoun Khater, validation/02_advisor_meeting_log.md 
// Conversation N, Decision #NN
```

This builds the audit trail the formal paid lawyer will follow when engaged.

**Boundary:** Advisor input does NOT satisfy formal lawyer signoff hard stops in §10. Khaldoun cannot sign production-deploy attestations.

### §4g. Court-level-dependent deadline calculation (NEW v5)

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

---

## 5. Folder Structure

```
app/
  Concerns/                  Traits (BelongsToWorkspace, AppendOnlyLedger)
  Enums/                     PHP enums (incl. LitigationStatusEnum, HearingTypeEnum, JudgmentPresenceEnum, CourtLevelEnum, KycItemTypeEnum)
  Exceptions/                Custom exceptions (UnsupportedCourtLevelException, MissingJudgmentPresenceException, UnconfirmedRegulationException)
  Filament/
    Resources/               Filament resources (incl. ExpertReportResource)
    Pages/                   Custom Filament pages
    Widgets/                 
  Http/
    Controllers/Api/V1/      Versioned API controllers
    Controllers/Web/         Non-Filament web controllers
    Requests/                FormRequests
    Resources/               API resources (strip OAuth tokens)
    Middleware/              SetLocale, EnsureWorkspaceSelected, EnsurePdplConsentObtained
  Livewire/                  Custom Livewire components
  Models/                    Eloquent models (incl. ExpertReport)
  Services/                  Domain services (incl. AppealDeadlineService, ExpertReportService)
    AutomationActions/       Action handlers (S-11)
  Policies/                  <Entity>Policy
  Jobs/                      Queue jobs
  Observers/                 Eloquent observers (incl. CourtReviewObserver, ExpertReportObserver)
  Mail/                      Bilingual mailables
  Console/Kernel.php         Scheduled tasks
  Providers/                 AppServiceProvider (morph map)

database/
  migrations/
  seeders/
    *.php                    
    data/
      jordan_courts.csv      # Pending from advisor (Khaldoun)
  factories/

resources/
  views/                     auth, documents, invoices, filament, sso, ics, onboarding
  lang/ar/                   litigation_status, hearing_type, kyc_items, ai_disclaimers, pdpl_consent_v1, expert_reports, appeal_deadlines, etc.
  lang/en/                   (same files)
  css/, js/
  pwa/                       manifest, service worker, icons

routes/                      web.php, api.php, console.php

tests/
  Unit/
  Feature/
    Api/V1/, Filament/, Models/, Policies/, Services/, Concerns/, Locale/, Middleware/
    Financial/
    Litigation/              (incl. AppealDeadlineCalculationTest, ExpertReportObjectionTest)
    Compliance/              (Append-only multi-layer)
    Automations/
    Integrations/
  Browser/
  fixtures/

openapi/spec.yaml

decisions/
  D-09_breadth_pivot.md
  D-15_pricing_ceiling.md

prompts/                     Clause-level AI prompts
  document_generation/       Full-document AI generation templates

spikes/

validation/
  00_FOUNDER_WAIVER.md
  01_haqq_ai_test_protocol.md
  02_advisor_meeting_log.md  # SOURCE OF TRUTH for advisor input
  06_legal_docs/             Pending paid lawyer
  08_litigation_lawyer_signoff.md
  09_trust_account_lawyer_signoff.md
  09_trust_account_cpa_signoff.md
  10_ai_generation_lawyer_signoff.md
  13_lawyer_management_advisor_review.md

planning/
  00_MVP_ROADMAP_v0.2.md
  HAQQ_COVERAGE_GAP_ANALYSIS.md
  SURGE-01 through SURGE-09 (shipped)
  SURGE-FIX-01-Advisor-Corrections.md  # ACTIVE — priority
  SURGE-10 through SURGE-13 (queued)
  SURGE-VERIFY.md
```

---

## 6. Naming Conventions

| Thing | Convention |
|---|---|
| Primary key | ULID via `HasUlids` |
| FK column | `string(26)` snake_case ending in `_id` |
| Eloquent model | Singular PascalCase — `ExpertReport`, `LawyerProfile` |
| Table | Plural snake_case — `expert_reports`, `lawyer_profiles` |
| Morph key | Short snake_case — `'expert_report'`, `'matter'`, `'task'` |
| Migration | Verb-shaped, timestamped |
| Filament resource | `<Entity>Resource` |
| Filament page | `<Name>Page` |
| Policy | `<Entity>Policy` |
| FormRequest | `<Action><Entity>Request` |
| API controller | `Api\V1\<Entity>Controller` |
| Service | `<Entity\|Domain>Service` — `AppealDeadlineService` |
| Observer | `<Entity>Observer` — `CourtReviewObserver` |
| Exception | `<Reason>Exception` — `UnsupportedCourtLevelException` |
| PHP enum | `<Entity>Enum` — `LitigationStatusEnum`, `JudgmentPresenceEnum` |
| Localization domain | snake_case filename |
| Route name | dotted snake_case |

---

## 7. Domain Glossary

### Core (S-01 to S-06)

- **Workspace, WorkspaceMember, Role**
- **Contact, Client, Counterparty, OpposingCounsel**
- **Matter** — Commercial OR litigation. `court_level` field added in S-FIX-01.3
- **Document, DocumentVersion, DocumentClause**
- **ContractMetadata**
- **Obligation**
- **LibraryClause**
- **AiInteraction** (append-only)
- **Subscription**

### Practice Management (S-07)

- **Task, TimeEntry, KycChecklist, KycItem, KpiTarget, Team, SmartList**
- **KycItem.item_type** extended in S-FIX-01.6 with `commercial_registration_certificate`, `signatory_authority_document` for corporate Contacts

### Litigation (S-08) `[HARD-STOP-LAWYER-REQUIRED for production]`

- **Court, Judge, Hearing, CourtReview, ServiceLogEntry**
- **CourtReview** extended in S-FIX-01.3 with `judgment_presence` enum (wijahi / mithla_wijahi / ghyabi) and `notified_date`
- **Hearing.hearing_type** extended in S-FIX-01.1 — `plaintiff_evidence`, `defendant_evidence`, `notification_session` replace generic `evidence`
- **Matter.litigation_status** extended in S-FIX-01.1 — `fee_payment_and_registration`, `notification_pending`, `referred_to_expert` added
- **NEW: ExpertReport** (S-FIX-01.2) — first-class entity per Khaldoun's input. 8-day mandatory objection deadline. Auto-creates Obligation on receipt. Models the "Expert Phase" of Jordanian commercial litigation where cases sleep 6 months awaiting expert opinion.

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

### Critical services (S-FIX-01)

- **`AppealDeadlineService`** — court-level-dependent calculation. Replaces hardcoded 30-day. See §4g
- **`ExpertReportService`** — 8-day objection deadline; state transitions

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
21. **AppealDeadlineService calculation tests** (S-FIX-01.3) — minimum 10 cases covering all enum combinations + failures
22. **ExpertReport objection countdown tests** (S-FIX-01.2) — including Observer auto-creating Obligation
23. **Trust ledger adjustment description enforcement** (S-FIX-01.4) — multi-layer
24. **PDPL consent gate tests** (S-FIX-01.5) — Matter creation blocked when consent not obtained
25. **Advisor-citation comment audit** (S-FIX-01) — grep verifies all new code added per S-FIX-01 has `validation/02_advisor_meeting_log.md` decision-number citations

**Test discipline:** SURGE-FIX-01 alone must add ≥ 40 tests. The under-testing pattern (488 tests for 120+ endpoints) cannot continue when corrections involve legal deadline accuracy.

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

### Hard stops on production deployment

| Hard Stop | Gates | Resolution file |
|---|---|---|
| Lawyer-drafted ToS / Privacy / DPA / AI Disclaimer | S-06 paid launch | `validation/06_legal_docs/*` |
| Lawyer-reviewed AI prompt templates (clause-level) | S-04 real-user exposure | `prompts/*.md` headers |
| Lawyer signoff on litigation procedural model | S-08 production | `validation/08_litigation_lawyer_signoff.md` |
| Lawyer + CPA signoff on Trust Accounts | S-09 F-09.2 production | `validation/09_trust_account_lawyer_signoff.md` + `validation/09_trust_account_cpa_signoff.md` |
| Lawyer-reviewed AI doc-gen templates | S-10 F-10.4 production | `prompts/document_generation/*.md` headers |
| Lawyer-reviewed in-DB AI gen templates | S-10 F-10.5 | `ai_generation_templates.legal_review_status='approved'` |
| Lawyer-reviewed seed Document Templates | S-11 F-11.3 production | Per template advisor signoff |
| **Paid-lawyer-drafted PDPL consent text** (NEW S-FIX-01.5) | Production launch | `validation/06_legal_docs/pdpl_consent_v1.md` |
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
- **No hardcoded appeal deadlines — always route through `AppealDeadlineService`**
- **No silent default to 30-day window when court_level or judgment_presence unknown — throw and surface to lawyer**
- **No adjustment trust_ledger_entries without ≥ 10-char description**

### Process

- No work on a Surge whose upstream gates not satisfied
- No skipping Pest tests
- No modifying `CLAUDE.md` without founder direction — append-only Update log
- No modifying `decisions/` files; supersede via new ADR
- No modifying `validation/02_advisor_meeting_log.md` — append only with dated entries
- No marking a Surge "Production Ready" if applicable hard stop unresolved
- **No implementing schema corrections contradicting `validation/02_advisor_meeting_log.md` without raising contradiction to founder for advisor follow-up**

---

## 11. AODC Pipeline Reference

| Location | Contents |
|---|---|
| `planning/00_MVP_ROADMAP_v0.2.md` | Master plan v0.2 (active) |
| `planning/HAQQ_COVERAGE_GAP_ANALYSIS.md` | Coverage map |
| `planning/SURGE-NN-*.md` | Per-Surge plans (S-01 to S-09 shipped) |
| `planning/SURGE-FIX-01-Advisor-Corrections.md` | **ACTIVE — current priority** |
| `planning/SURGE-10/11/12/13` | Queued after S-FIX-01 |
| `planning/SURGE-VERIFY.md` | Standing recommendation |
| `validation/00_FOUNDER_WAIVER.md` | Operating waiver |
| `validation/02_advisor_meeting_log.md` | **Source of truth for advisor input** |
| `validation/08_litigation_lawyer_signoff.md` | Pending paid lawyer |
| `validation/09_trust_account_lawyer_signoff.md` | Pending paid lawyer + CPA |
| `validation/10_ai_generation_lawyer_signoff.md` | Pending paid lawyer |
| `decisions/D-09_breadth_pivot.md` | Strategic pivot ADR |
| `decisions/D-15_pricing_ceiling.md` | Pricing per advisor (JOD 20–30) |
| `prompts/` | Clause-level AI prompts |
| `prompts/document_generation/` | Full-document AI generation templates |
| `spikes/` | Throwaway research |

---

## 12. When in doubt

Order of precedence:

1. **This file (`CLAUDE.md`)**
2. **`decisions/D-NN.md` ADRs** in reverse chronological order
3. **`validation/02_advisor_meeting_log.md`** — practitioner-validated decisions cited by number
4. **`validation/00_FOUNDER_WAIVER.md`** — operating mode
5. **`planning/00_MVP_ROADMAP_v0.2.md`**
6. **The specific Surge plan**
7. **The Tech Task Package**
8. **The Founder**

Do not invent answers. Do not guess at intent. Stop, surface the question, wait for direction.

---

*End of CLAUDE.md. Welcome to the project.*
