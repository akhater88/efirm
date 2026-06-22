# CLAUDE.md — Project Memory for Claude Code

> This file is loaded by Claude Code on every session against this repository.
> Read it first. It encodes everything you need to know before touching the codebase.
> **Last meaningful update: 2026-06-22** (post-SURGE-09 expansion — Tasks Workflow + Board + AI Doc Gen + Templates Engine + Integrations + Upmarket)

---

## Update log

- **2026-06-17** — Initial draft, pre-build.
- **2026-06-21 (am)** — Reconciled with actual installed stack after SURGE-01 F-01.1–F-01.5. Laravel 11 → 13, Filament v3 → v5, Pest 2 → 4, UUIDs → ULIDs, Tailwind v4. Added Filament-everywhere architectural pivot.
- **2026-06-21 (pm)** — D-09 strategic pivot. Depth-wedge thesis superseded by breadth-coverage. Schema-level rejections rewritten: litigation, accounting, CRM, KYC are now IN scope under SURGE-07/08/09. New hard stops added.
- **2026-06-22** — SURGE-10/11/12 added to scope: Task Workflows + Board, AI Document Generation, Form Templates engine, Automations engine, OAuth integrations (email + calendar), SSO/SAML, PWA, audit log UI. New architectural non-negotiables for rule engine isolation, OAuth token encryption, and integration-vs-native policy.

---

## 1. Project

A **bilingual (Arabic/English), AI-native legal-OS** for Levant law firms (2–10 lawyers). Covers full legal-practice surface — contracts, litigation, practice management, financials, CRM, workflow automation — at depths competitive with HAQQ.ai's eFirm, with deeper document/clause/AI workflow than HAQQ and locally-priced for the Levant market.

**Strategic posture (per D-09 + 2026-06-22 expansion):** maximum-breadth coverage targeting ~85–90% of HAQQ's nominal surface area. Email and calendar via OAuth integration (NOT native build). Mobile via PWA (NOT native apps). Retaining depth advantage on document workspace + AI surfaces.

**Single hardest test we must still pass (unchanged):** a lawyer imports a real `.docx` contract, edits clauses (mixed AR/EN, RTL/LTR), exports back to `.docx`, opens the file in Microsoft Word with formatting intact. If round-trip fidelity breaks, the wedge breaks — regardless of breadth coverage.

**Hard stops on production deployment (do not waive):**

- **Lawyer/Advisor signoff** before any litigation module (SURGE-08) handles real cases
- **Lawyer + CPA signoff** before any trust account (SURGE-09 F-09.2) handles real client funds
- **Lawyer-drafted legal documents** (ToS, Privacy, DPA, AI Disclaimer) before paid launch (SURGE-06)
- **Lawyer-reviewed AI prompt templates** before AI features expose to real users (SURGE-04)
- **Lawyer-reviewed AI document-generation templates** before SURGE-10 F-10.4 exposes to real users
- **Lawyer-reviewed seed document templates** before SURGE-11 F-11.3 exposes to real users

**Operating mode (per `validation/00_FOUNDER_WAIVER.md`):** SURGE-00 customer-interview gate deferred. Build proceeds with founder-decided placeholders marked `[PROVISIONAL-FOUNDER-DECIDED]`. Lawyer/advisor gate is a **production-deployment hard stop** for the modules listed above.

---

## 2. Tech Stack (actual installed versions)

| Layer | Value | Notes |
|---|---|---|
| Backend framework | **Laravel 13.16.x** | |
| Admin/Customer panel | **Filament v5.6.x** | |
| Backend language | PHP 8.3 (CI pin) / PHP 8.5 (local OK) | Production targets 8.3 |
| Frontend (Filament-driven UI) | Filament's Livewire 3 + Tailwind v4 | RTL via Tailwind v4 logical properties |
| Frontend (document editor only) | Custom Livewire 3 + Blade + TipTap/ProseMirror | Per SURGE-03 F-03.1 |
| Frontend (Task Board kanban) | Filament Page + Livewire + SortableJS / livewire-sortable | Per SURGE-10 F-10.2 |
| Database | MySQL 8.x InnoDB | UTF8MB4 |
| ORM | Eloquent (Laravel 13) | Soft deletes; ULID PKs |
| Primary key strategy | **ULID** via `HasUlids` trait | All tenant entities |
| Cache / queues | Redis | Cloudways-managed |
| Object storage | S3-compatible | `.docx` blobs, exports, PDFs |
| Cloud | Cloudways → DigitalOcean Frankfurt FRA1 | |
| CI/CD | GitHub Actions | `.github/workflows/ci.yml` |
| Auth (web) | Google OAuth via Socialite v5 | + optional SAML/OIDC SSO per workspace (SURGE-12) |
| Auth (API) | Laravel Sanctum | Bearer tokens |
| API style | REST + OpenAPI 3.0 | `openapi/spec.yaml` |
| LLM provider | Anthropic Claude | Abstracted behind `LlmProvider` interface |
| Billing | Stripe via Laravel Cashier | |
| PDF generation | Spatie laravel-pdf | Invoice PDFs, legal-doc exports |
| Money handling | Laravel `decimal` casts + bcmath | **No floating-point on money** |
| Email integration (S-12) | Microsoft Graph API + Gmail API | OAuth 2.0 per-user |
| Calendar integration (S-12) | Google Calendar API + Microsoft Graph Calendar | OAuth 2.0 per-user |
| SSO (S-12) | `aacotroneo/laravel-saml2` or equivalent | SAML 2.0 + OIDC support |
| PWA (S-12) | Web App Manifest + Service Worker (vanilla) | Static-asset cache only; NO offline data |
| Drag-drop (kanban) | SortableJS via livewire-sortable | Server-state authoritative; optimistic UI |
| Encryption at rest | Laravel `encrypted` casts | OAuth tokens, IdP certificates, sensitive metadata |
| Tests | **Pest 4.7.x** | `tests/Unit/`, `tests/Feature/`, `tests/Browser/` |
| E2E tests | Pest Browser Plugin (Playwright) | Editor + .docx + board drag-drop |
| Static analysis | Larastan 3.10 / PHPStan 2.2 at level 6 | `phpstan.neon` (phpVersion: 80300) |
| Code style | Laravel Pint | `pint.json` |
| Localization (framework AR) | `laravel-lang/common` | |
| Localization (project) | `resources/lang/{ar,en}/*.php` | |
| Default locale | `ar` (Arabic, RTL) | English secondary |

---

## 3. Common Commands

```bash
# First-time setup
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build

# Daily dev
php artisan serve
php artisan queue:work --queue=default,contracts,exports,reminders,invoices,automations
npm run dev

# Tests
./vendor/bin/pest                                # unit + feature
./vendor/bin/pest --filter=Document              # subset
./vendor/bin/pest tests/Browser                  # E2E (slow)
./vendor/bin/pest --parallel                     # faster locally
./vendor/bin/pest --coverage --min=80            # coverage gate

# Code quality
./vendor/bin/pint                                # auto-format
./vendor/bin/pint --test                         # check only (CI)
./vendor/bin/phpstan analyse                     # static analysis

# Migrations
php artisan make:migration create_<table>_table --create=<table>
php artisan migrate
php artisan migrate:rollback --step=1
php artisan migrate:fresh --seed                 # local only — never in shared envs

# Filament v5
php artisan make:filament-resource <Entity>
php artisan filament:upgrade
php artisan filament:install --panels

# Seeders (default)
php artisan db:seed
php artisan db:seed --class=DemoWorkspaceSeeder

# Jurisdiction-specific (SURGE-08) — manual, lawyer-signoff-gated
php artisan db:seed --class=JordanCourtsSeeder
php artisan db:seed --class=LebanonCourtsSeeder
php artisan db:seed --class=PalestineCourtsSeeder
php artisan db:seed --class=IraqCourtsSeeder

# Chart of Accounts (SURGE-09) — manual, CPA-review-gated
php artisan db:seed --class=JordanChartOfAccountsSeeder

# AI document generation templates (SURGE-10) — manual, lawyer-signoff-gated
php artisan db:seed --class=AiGenerationTemplatesSeeder

# Static document templates (SURGE-11) — manual, advisor-review-gated
php artisan db:seed --class=DocumentTemplatesSeeder

# Workflow bundles (SURGE-11)
php artisan db:seed --class=WorkflowBundlesSeeder

# OpenAPI
./vendor/bin/spectral lint openapi/spec.yaml
```

**Local services required:**

- MySQL 8.x on `localhost:3306`
- Redis on `localhost:6379`
- (Optional) MinIO for S3-compatible storage
- (For S-12 development) OAuth dev credentials configured in Google Cloud Console + Microsoft Azure Portal

---

## 4. Architectural Non-Negotiables

These are constraints, not preferences. If a task requires violating one, stop and surface the conflict.

### Core principles

1. **Workspace scoping is mandatory.** Every tenant-scoped model uses `BelongsToWorkspace`. Global Eloquent scope auto-filters. Cross-workspace data is P0.
2. **Policy + FormRequest on every write endpoint AND every Filament resource.** No inline validation. No role checks in controllers.
3. **Soft deletes everywhere** — except append-only ledgers (see §4b).
4. **Audit columns everywhere** — `created_at`, `updated_at`, `created_by_user_id`, `updated_by_user_id`. Except append-only ledgers.
5. **Optimistic locking** on concurrent edits via `updated_at` mismatch → 409.
6. **OpenAPI spec sync.** Every endpoint change updates `openapi/spec.yaml` in the same PR.
7. **Bilingual via Laravel localization only.** Never hardcode user-facing strings.
8. **No N+1.** Lists use eager loading.
9. **No raw SQL** unless wrapped in a Repository with integration tests.
10. **Document editing is client-side** (SURGE-03 only — TipTap in custom Livewire+Blade).
11. **Filament is the primary UI for ALL roles.** See §4a.
12. **ULIDs as primary keys.** `HasUlids` trait. FK columns `string(26)`.
13. **Polymorphic relationships use a registered morph map** in `app/Providers/AppServiceProvider.php`. Short stable keys only.
14. **No floating-point arithmetic on money.** DECIMAL(15,2) + bcmath. See §4c.
15. **Append-only ledgers** enforce immutability at multiple layers. See §4b.
16. **Rule engine isolation** (added SURGE-11): the automations engine's condition evaluator and action handlers run in isolated scopes — never `eval()`, never user-supplied code execution. See §4d.
17. **OAuth tokens encrypted at rest** (added SURGE-12): every OAuth refresh/access token uses Laravel's `encrypted` cast. Never appears in logs, API responses, or audit changes-diffs. See §4e.
18. **Integration over native** (added SURGE-12): for email, calendar, signature workflows, and any third-party-app-native domain — integrate via official API, do NOT build native. See §10 still-out list.
19. **PWA, not native mobile.** Service workers cache static assets only. NO offline data sync (data corruption risk).

### §4a. Filament-everywhere — the UI architecture

- Filament v5 is the primary UI for all authenticated users, all roles.
- Granular access is enforced via Policy methods on each Resource.
- `canAccessPanel()` returns true for any workspace member.
- **Exceptions** (use Blade or custom Livewire outside Filament): auth/login, document editor (S-03), Task Board page (S-10 F-10.2 — Filament Page with custom Livewire), Stripe redirect flow (S-06), public legal-doc acceptance pages (S-06), public document share token endpoints (S-03), public invoice viewing (S-09), SSO callback endpoints (S-12), public ICS feed (S-12 F-12.2).

### §4b. Append-only ledgers

| Ledger | Model | Surge | Why append-only |
|---|---|---|---|
| `ai_interactions` | `AiInteraction` | S-04 | Audit + cost reconciliation |
| `trust_ledger_entries` | `TrustLedgerEntry` | S-09 | **Bar-association regulatory** |
| `financial_audit_log` | `FinancialAuditLog` | S-09 | Financial compliance |
| `ai_document_generations` | `AiDocumentGeneration` | S-10 | AI usage + cost audit + replay |
| `automation_runs` | `AutomationRun` | S-11 | Execution trace + debugging |
| `audit_logs` | `AuditLog` | S-12 | System-wide compliance trail |

Enforcement: model lifecycle hook blocks `updating` + `deleting` events; Policy denies update + delete methods; DB trigger (trust_ledger_entries only); multi-layer Pest tests verify all denials.

### §4c. Money handling

DECIMAL(15,2). Eloquent cast `'amount' => 'decimal:2'`. All arithmetic via bcmath or `MoneyService`. Currency stored as separate `CHAR(3)` ISO 4217. No floats anywhere in financial code paths.

### §4d. Rule engine isolation (SURGE-11)

The automations engine (F-11.2) evaluates user-defined conditions and runs user-configured actions. The evaluator must NEVER:
- Use PHP `eval()` or any equivalent code-execution primitive
- Allow user expressions to reach the filesystem, network, or process control
- Accept arbitrary class instantiation from user input

The condition evaluator is a fixed-operator interpreter (eq, neq, gt, lt, gte, lte, in, contains, is_null, is_not_null, and, or, not). Adding new operators requires a code change + PR review. The action handler set is fixed in `app/Services/AutomationActions/` — user input cannot extend it at runtime.

Form template field types (F-11.1) similarly: closed set, not user-extensible.

### §4e. OAuth token security (SURGE-12)

- All OAuth access tokens and refresh tokens use Laravel `encrypted` cast on the column
- Tokens never appear in API responses (resource transformers strip them)
- Tokens never appear in audit log `changes` diffs (auditing layer redacts)
- Tokens never logged (Laravel `Log::redact` patterns applied)
- On disconnect: tokens are zeroed out before soft delete (so even DB recovery cannot retrieve them)
- Pest tests must verify each of these protections actively

---

## 5. Folder Structure

```
app/
  Concerns/                  Shared traits (BelongsToWorkspace, AppendOnlyLedger)
  Enums/                     PHP enums
  Filament/
    Resources/               Filament v5 resources (primary UI)
    Pages/                   Custom Filament pages (KPI dashboard, Task Board, Workflow Library, Email Inbox, My Calendar, AI Usage)
    Widgets/                 Dashboard widgets
  Http/
    Controllers/
      Api/V1/                Versioned API controllers
      Web/                   Non-Filament web controllers (auth, document editor, share tokens, public invoice, SSO callbacks, ICS feed)
    Requests/                FormRequest classes
    Resources/               API resources (transformers — must strip OAuth tokens)
    Middleware/              SetLocale, EnsureWorkspaceSelected
  Livewire/                  Custom Livewire components (document editor, share modal, AI panel, Task Board, AI generation intake)
  Models/                    Eloquent models — singular PascalCase, HasUlids
  Services/                  Domain services
    AutomationActions/       One handler class per automation action type (S-11)
  Policies/                  <Entity>Policy
  Jobs/                      Queue jobs
  Mail/                      Bilingual Markdown mailables
  Console/
    Kernel.php               Scheduled tasks
  Providers/                 Service providers (incl. morph map in AppServiceProvider)

database/
  migrations/                Timestamped migrations
  seeders/
    DatabaseSeeder.php
    DemoWorkspaceSeeder.php
    JordanCourtsSeeder.php          # S-08, manual, lawyer-gated
    LebanonCourtsSeeder.php
    PalestineCourtsSeeder.php
    IraqCourtsSeeder.php
    JordanChartOfAccountsSeeder.php # S-09, manual, CPA-gated
    AiGenerationTemplatesSeeder.php # S-10, manual, lawyer-gated
    DocumentTemplatesSeeder.php     # S-11, manual, advisor-recommended
    WorkflowBundlesSeeder.php       # S-11
  factories/                 Model factories

resources/
  views/
    auth/                    Login (Blade — pre-auth)
    documents/               Document editor (Livewire+Blade)
    invoices/                Invoice PDF templates
    filament/                Filament render hooks
    sso/                     SSO setup wizard views
    ics/                     ICS feed templates
  lang/ar/                   Arabic translations
  lang/en/                   English translations
  css/, js/                  Frontend assets (Vite-bundled)
  pwa/                       Web App Manifest, service worker, icons

routes/
  web.php                    Web routes
  api.php                    API routes (Sanctum)
  console.php                Artisan commands

tests/
  Unit/
  Feature/
    Api/V1/
    Filament/
    Models/
    Policies/
    Services/
    Concerns/
    Locale/
    Middleware/
    Financial/
    Litigation/
    Compliance/                # Append-only enforcement multi-layer tests
    Automations/               # S-11 rule engine + action handlers
    Integrations/              # S-12 email + calendar + SSO
  Browser/
  fixtures/

openapi/
  spec.yaml

decisions/                   D-NN.md ADRs (append-only series)
prompts/                     AI prompt templates
  document_generation/       S-10 full-contract generation templates
spikes/                      Throwaway research
validation/                  SURGE-00 + FOUNDER_WAIVER + signoffs
planning/                    Surge/Flow .md files
```

---

## 6. Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| Primary key | ULID via `HasUlids` | `protected $keyType = 'string';` |
| FK column | `string(26)` ending in `_id` | `workspace_id`, `created_by_user_id` |
| Eloquent model | Singular PascalCase | `TaskWorkflow`, `Automation`, `EmailIntegration` |
| Table | Plural snake_case | `task_workflows`, `automations`, `email_integrations` |
| Pivot/junction with model | Singular PascalCase | `WorkspaceMember` |
| Polymorphic morph key | Short snake_case in morph map | `'matter'`, `'task'`, `'automation'`, `'invoice'` |
| Migration | Verb-shaped, timestamped | `2026_06_25_create_task_workflows_table` |
| Filament resource | `<Entity>Resource` | `TaskWorkflowResource`, `FormTemplateResource` |
| Filament page (custom) | `<Name>Page` | `TasksBoardPage`, `EmailInboxPage`, `MyCalendarPage`, `AiUsagePage` |
| Policy | `<Entity>Policy` | `TaskWorkflowPolicy` |
| FormRequest | `<Action><Entity>Request` | `StoreTaskWorkflowRequest` |
| API controller | `Api\V1\<Entity>Controller` | `TaskWorkflowController` |
| Web controller (non-Filament) | `Web\<Entity>Controller` | `DocumentEditorController`, `SsoController`, `IcsFeedController` |
| Livewire component | `App\Livewire\<Domain>\<Component>` | `App\Livewire\Tasks\Board`, `App\Livewire\Ai\GenerationIntake` |
| Service | `<Entity\|Domain>Service` | `TaskTransitionService`, `AutomationEvaluatorService`, `EmailFetcherService` |
| Action handler (S-11) | `<Action>Action` in `app/Services/AutomationActions/` | `CreateTaskAction`, `SendEmailAction` |
| Job | Verb-shaped, `Job` suffix | `SendHearingReminderJob`, `RunAutomationJob`, `SyncCalendarJob` |
| Test (API feature) | `tests/Feature/Api/V1/<Entity>ApiTest.php` | — |
| Test (Filament) | `tests/Feature/Filament/<Entity>ResourceTest.php` | — |
| Test (unit service) | `tests/Unit/Services/<Service>Test.php` | — |
| Test (compliance) | `tests/Feature/Compliance/<Topic>Test.php` | — |
| Test (automations) | `tests/Feature/Automations/<Topic>Test.php` | — |
| Test (integrations) | `tests/Feature/Integrations/<Topic>Test.php` | — |
| Localization domain | snake_case filename | `resources/lang/ar/task_workflows.php` |
| Route name | dotted snake_case | `tasks.board`, `automations.test_run` |

---

## 7. Domain Glossary

### Core entities (SURGE-01 to SURGE-06)

- **Workspace, WorkspaceMember, Role** — Tenant + membership + role enum.
- **Contact, Client, Counterparty, OpposingCounsel** — Polymorphic Person/Org with flag combinations.
- **Matter** — Commercial OR litigation work (extended by S-08).
- **Document, DocumentVersion, DocumentClause** — Editable document with versions and addressable clauses.
- **ContractMetadata** — Per-Document contract economics.
- **Obligation** — Dated commitment from a contract clause. Distinct from Task.
- **LibraryClause** — Reusable clause; AR+EN paired.
- **AiInteraction** — Append-only AI audit (clause-level operations).
- **Subscription** — Stripe-managed workspace billing.

### Practice Management entities (SURGE-07)

- **Task** — Generic to-do. Polymorphic — attaches to ANY entity. Distinct from Obligation. **Now uses TaskWorkflow (SURGE-10) for advanced status handling.**
- **TimeEntry** — Billable time. Drives KPIs and Invoice lines.
- **KycChecklist, KycItem** — Per-Contact document collection workflow.
- **KpiTarget** — Per-User or per-Team metric target.
- **Team** — Nested groups within a workspace.
- **SmartList** — Saved filter combination per entity, per user or shared.

### Litigation entities (SURGE-08) `[HARD-STOP-LAWYER-REQUIRED for production]`

- **Court, Judge, Hearing, CourtReview, ServiceLogEntry** — Litigation tracking entities.

### Financial entities (SURGE-09) `[F-09.2 HARD-STOP-LAWYER+CPA-REQUIRED for production]`

- **Account, TrustAccount, TrustLedgerEntry, JournalEntry, JournalEntryLine, Invoice, InvoiceLine, Receipt** — Financial system.
- **Pipeline, Lead, Opportunity** — CRM pre-Matter pipeline.

### Workflow & Generation entities (SURGE-10)

- **TaskWorkflow** — Per-workspace definition of stages + transitions for Tasks. Optionally scoped to a specific Task type.
- **TaskWorkflowStage** — Named stage in a workflow (e.g., "Drafting", "Internal Review"). Has key, sort_order, color, can require approval on entry.
- **TaskWorkflowTransition** — Allowed move from one Stage to another. May require specific role or specific approver.
- **TaskWorkflowApproval** — Pending approval record when an approval-gated transition is requested.
- **AiDocumentGeneration** — Append-only audit row per AI document-generation request. Includes prompt, response, tokens, cost, resulting Document.
- **AiGenerationTemplate** — In-DB template for AI document generation. Per-workspace overrides of system templates. Each carries a `legal_review_status`.

### Platform engine entities (SURGE-11)

- **FormTemplate, FormTemplateField, FormSubmission** — Custom-form layer attached to entities.
- **Automation** — User-defined rule: trigger event + conditions + actions.
- **AutomationAction** — Single action attached to an Automation (e.g., create_task, send_email).
- **AutomationRun** — Append-only audit row per automation execution.
- **DocumentTemplate** — Static-skeleton document template (distinct from AiGenerationTemplate). Placeholder substitution, not AI-generated.
- **WorkflowBundle** (configuration) — A pre-configured set of TaskWorkflow + Automation + DocumentTemplate items activatable per workspace.

### Integration entities (SURGE-12)

- **EmailIntegration** — Per-user OAuth connection to Outlook or Gmail. Tokens encrypted at rest.
- **EmailAttachment** — Cached metadata of an email attached to a Matter or Contact. Body content NEVER stored — only first-500-char snippet.
- **CalendarIntegration** — Per-user OAuth connection to Google Calendar or Outlook Calendar.
- **ExternalCalendarEvent** — Mirror of external calendar events (read-only display).
- **WorkspaceSsoConfig** — Per-workspace SAML 2.0 or OIDC SSO configuration. IdP certificate encrypted at rest.
- **AuditLog** — Append-only workspace-wide audit trail. Surfaced via Filament resource (Owner/Admin only).

### Words NOT in our domain (intentionally — still out even at 90% HAQQ coverage)

- "Case" (we say Matter)
- "Pleading" (out — generate via S-10 AI generation instead)
- "Discovery procedure entity" (out — Year-2)
- **"Native Email"** — `emails` table storing native email content (out — EmailIntegration via OAuth instead)
- **"Native Calendar"** — `calendar_events` table with full ICS, recurrence, etc. (out — CalendarIntegration via OAuth instead)
- "Chat / Messaging entity" — out
- "Native Mobile App" — PWA only
- "Visual workflow designer with drag-drop nodes" — out (Filament Repeater UI is sufficient)
- "Custom code/script actions" — out (security)
- "Cron triggers below hourly granularity" — out (Year-2 if demanded)
- "Webhook actions" — out (Year-2)

---

## 8. Localization Rules

- Default locale is `ar`. Anonymous visitors see Arabic unless `?lang=en`.
- Every user-facing string in `resources/lang/{ar,en}/<domain>.php`.
- Framework strings from `laravel-lang/common`.
- Filament strings from `laravel-lang/common`'s `ar.json`.
- `SetLocale` middleware sets `<html dir>` and `<html lang>`.
- Tailwind v4 logical properties handle RTL (`ms-4`, `pe-2`). No RTL plugin.
- Dates: Gregorian only at MVP.
- Numbers: Latin digits in both locales.
- Currency: ISO code + amount.
- Plurals: `trans_choice()` with `:count`; account for Arabic's 6 plural forms.
- AR translations NEVER auto-generated.
- Mixed-direction content in documents respects per-paragraph `dir`.
- Court / Judge names stored bilingual (`name_ar`, `name_en`).
- **Task Workflow stages, Form Template fields, Automation names, Document Template names, AI Generation Template names** — all bilingual fields by default.
- **Email integration UI** in user's locale; email content itself in its original language (don't translate user's actual emails).
- **ICS export** uses the locale of the requesting user for summary/description fields.

---

## 9. Testing & Quality Gates

Every PR must pass:

1. Pest test suite green
2. Pint clean
3. Larastan level 6 clean on CI (PHP 8.3)
4. OpenAPI spec valid + in sync
5. Workspace isolation tested for every tenant-scoped entity
6. AR locale smoke test for every UI-bearing Flow
7. Filament resource tests for every new resource
8. Append-only enforcement tests for all 6 ledgers (ai_interactions, trust_ledger_entries, financial_audit_log, ai_document_generations, automation_runs, audit_logs)
9. Financial idempotency tests for invoice payments, retainer drawdowns
10. Money precision tests
11. Litigation procedural tests per jurisdiction
12. **(SURGE-10)** Task Workflow backward-compat tests — existing Tasks (from S-07) must continue to function without modification
13. **(SURGE-10)** AI document generation refuses to run in production with `[LEGAL-REVIEW-PENDING]` templates
14. **(SURGE-11)** Rule engine condition-evaluator exhaustive tests (every operator, every type coercion, every edge case)
15. **(SURGE-11)** Automation loop-prevention tests (depth limit, transitive triggers, self-trigger detection)
16. **(SURGE-11)** Action handler isolation tests (each handler independently invokable, no shared mutable state)
17. **(SURGE-12)** OAuth token security tests (encryption at rest, redaction from logs/API/audit diffs, zeroing on disconnect)
18. **(SURGE-12)** SAML signature/audience/recipient/conditions/NotBefore/NotOnOrAfter validation tests (security-critical)
19. **(SURGE-12)** ICS export validates against RFC 5545 schema
20. **(SURGE-12)** PWA manifest valid; service worker caches static assets ONLY (test: data requests bypass cache)

**Test data hygiene:**
- Factories for all test data
- `beforeEach()` per file; no global state
- Browser tests use real `.docx` fixtures
- Litigation tests use anonymized real Levant court structures
- Financial tests exercise decimal-precision edge cases
- **OAuth tests use mocked provider responses** — never hit real Google/Microsoft APIs in CI
- **SAML tests use saved SAML response fixtures** — never live IdP in CI

---

## 10. What NOT to Do

### Schema-level constraints — STILL REJECT (even after the 2026-06-22 expansion)

The codebase must NEVER contain:

- **Native email module:** `emails` table for storing native-stored email content. EmailIntegration via OAuth + cached metadata only (snippet, NOT body) is the correct pattern.
- **Native calendar module:** `calendar_events` table with full recurrence/ICS handling. CalendarIntegration via OAuth + ExternalCalendarEvent mirror is the correct pattern.
- **Mobile app code:** iOS / Android / React Native. PWA is the only mobile path.
- **Native messaging/chat:** `messages`, `chat_threads`, `conversations` tables. Out.
- **Custom code execution in automations:** PHP `eval()`, dynamic class instantiation from user input, file system or shell access from rule evaluator. Hard security boundary.
- **Webhook trigger receivers** (incoming HTTP triggers for automations): out at MVP.
- **Outbound webhook actions** (automation → arbitrary URL POST): out at MVP.
- **Service Worker that caches data** (not just static assets): out. Stale data + UI confusion guaranteed.
- **AI extraction tables populated automatically without user review** — user must confirm AI-extracted data before it becomes "real."
- **OAuth token visibility in any non-encrypted form** — tokens never in responses, logs, audit diffs.

### Schema-level constraints LIFTED under recent ADRs

- D-09 (2026-06-21): litigation, accounting, CRM, KYC entities — IN scope.
- 2026-06-22 expansion: Task Workflow, Automations, Form Templates, Document Templates, Email/Calendar integration entities, SSO configs, Audit Log — IN scope.

### Hard stops on production deployment

| Hard Stop | Gates which Surge/Flow | Resolution file |
|---|---|---|
| Lawyer-drafted ToS / Privacy / DPA / AI Disclaimer | S-06 paid launch | `validation/06_legal_docs/*` |
| Lawyer-reviewed AI prompt templates (clause-level) | S-04 real-user exposure | `prompts/*.md` headers signed |
| Lawyer signoff on litigation procedural model | S-08 production for real cases | `validation/08_litigation_lawyer_signoff.md` |
| Lawyer + CPA signoff on Trust Accounts | S-09 F-09.2 production for real funds | `validation/09_trust_account_lawyer_signoff.md` + `validation/09_trust_account_cpa_signoff.md` |
| **Lawyer-reviewed AI document-generation templates** | **S-10 F-10.4 production exposure** | `prompts/document_generation/*.md` headers signed |
| **Lawyer-reviewed AI generation in-DB templates** | **S-10 F-10.5** | `ai_generation_templates.legal_review_status='approved'` for each |
| **Lawyer-reviewed seed Document Templates** | **S-11 F-11.3 production exposure** | Document Template advisor signoff per template |
| KYC checklist content review | S-07 F-07.3 — recommended | `[ADVISOR-REVIEW-RECOMMENDED]` markers |

The Engineer agent refuses to mark a Surge "Production Ready" when any applicable hard stop is unresolved. Code-complete is permitted; production deployment is not.

### UI architecture constraints

- Do not build parallel Blade pages for entities that have Filament resources.
- Do not block Filament panel access by role; use Policies for method-level access.
- Do not move the Task Board outside the Filament Page wrapper (it needs Filament's locale, tenancy, and policy hooks).
- Do not implement document-editor-like surfaces inside Filament (TipTap doesn't fit Filament's form schema).

### Operational constraints

- Do not call LLM providers directly from the browser.
- Do not store LLM API keys outside `.env`.
- Do not log prompt content (unless to `AiInteraction` / `AiDocumentGeneration` audit rows).
- Do not bypass `BelongsToWorkspace` scope.
- Do not commit `.env`, real client data, or API keys.
- Do not auto-translate Arabic strings.
- Do not UPDATE or DELETE rows in append-only ledgers under any circumstance.
- Do not perform floating-point arithmetic on monetary values.
- Do not seed jurisdiction-specific data (courts, accounts) without the corresponding lawyer/CPA signoff.
- **(SURGE-11)** Do not allow user-supplied expressions to reach `eval`, `assert`, `compile`, or any dynamic code execution primitive.
- **(SURGE-12)** Do not store OAuth tokens unencrypted. Do not log them. Do not include them in audit changes-diffs. Do not return them in API responses.
- **(SURGE-12)** Do not extend PWA service worker to cache data — only static assets (JS, CSS, fonts, icons).

### Process constraints

- Do not begin work on a Surge whose upstream gates are not satisfied per `validation/00_FOUNDER_WAIVER.md`.
- Do not skip Pest tests.
- Do not modify `CLAUDE.md` without explicit founder direction. Append-only Update log.
- Do not modify `decisions/` files; supersede via new ADR.
- Do not mark a Surge "Production Ready" if any applicable hard stop is unresolved.

---

## 11. AODC Pipeline Reference

| Location | Contents |
|---|---|
| `planning/00_MVP_ROADMAP_v0.2.md` | Master plan v0.2 — breadth strategy (active) |
| `planning/00_MVP_ROADMAP.md` | v0.1 (superseded; kept for history) |
| `planning/HAQQ_COVERAGE_GAP_ANALYSIS.md` | Module-by-module coverage map |
| `planning/SURGE-NN-*.md` | Per-Surge plans (S-01 to S-09 shipped; S-10 to S-12 to build; SURGE-VERIFY recommended) |
| `validation/00_FOUNDER_WAIVER.md` | Active operating waiver |
| `validation/08_litigation_lawyer_signoff.md` | S-08 production gate |
| `validation/09_trust_account_lawyer_signoff.md` | S-09 F-09.2 lawyer gate |
| `validation/09_trust_account_cpa_signoff.md` | S-09 F-09.2 CPA gate |
| `validation/10_ai_generation_lawyer_signoff.md` | S-10 F-10.4 templates gate |
| `decisions/D-NN.md` | Architecture decision records |
| `decisions/D-09_breadth_pivot.md` | The strategic pivot ADR |
| `prompts/` | Clause-level AI prompts (S-04) |
| `prompts/document_generation/` | Full-document AI generation templates (S-10) |
| `spikes/` | Throwaway research |

---

## 12. When in doubt

Order of precedence:

1. **This file (`CLAUDE.md`).**
2. **`decisions/D-09_breadth_pivot.md` and subsequent ADRs.**
3. **`validation/00_FOUNDER_WAIVER.md`.**
4. **`planning/00_MVP_ROADMAP_v0.2.md`.**
5. **The specific Surge plan.**
6. **The Tech Task Package.**
7. **The Founder.**

Do not invent answers. Do not guess at intent. Stop, surface the question, wait for direction.

---

*End of CLAUDE.md. Welcome to the project.*
