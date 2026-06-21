# CLAUDE.md — Project Memory for Claude Code

> This file is loaded by Claude Code on every session against this repository.
> Read it first. It encodes everything you need to know before touching the codebase.
> **Last meaningful update: 2026-06-21** (post-D-09 strategic pivot: depth-wedge → breadth-coverage)

---

## Update log

- **2026-06-17** — Initial draft, pre-build.
- **2026-06-21 (am)** — Reconciled with actual installed stack after SURGE-01 F-01.1–F-01.5. Laravel 11 → 13, Filament v3 → v5, Pest 2 → 4, UUIDs → ULIDs, Tailwind v4. Added Filament-everywhere architectural pivot.
- **2026-06-21 (pm)** — D-09 strategic pivot. Depth-wedge thesis superseded by breadth-coverage. Schema-level rejections in §10 rewritten: litigation, accounting, CRM, KYC modules are now IN scope under SURGE-07/08/09. New hard stops added (litigation production, trust accounts production). Domain glossary expanded.

---

## 1. Project

A **bilingual (Arabic/English), AI-native legal-OS** for Levant law firms (2–10 lawyers). The product covers the full legal-practice surface — contracts, litigation, practice management, financials, CRM — at depths competitive with HAQQ.ai's eFirm, with deeper document/clause/AI workflow than HAQQ and locally-priced for the Levant market.

**Strategic posture (per D-09 breadth pivot, 2026-06-21):** breadth coverage targeting ~75–80% of HAQQ's surface area, retaining depth advantage on the document workspace + AI surfaces (SURGE-03 + SURGE-04). The previous "depth-only wedge" thesis is superseded.

**Single hardest test we must still pass (unchanged):** a lawyer imports a real `.docx` contract, edits clauses (mixed AR/EN, RTL/LTR), exports back to `.docx`, and opens the file in Microsoft Word with formatting intact. If round-trip fidelity breaks, the wedge breaks — regardless of breadth coverage.

**Hard stops on production deployment (do not waive):**

- **Lawyer/Advisor signoff** before any litigation module (SURGE-08) handles real cases — see §10
- **Lawyer + CPA signoff** before any trust account (SURGE-09 F-09.2) handles real client funds — see §10
- **Lawyer-drafted legal documents** (ToS, Privacy, DPA, AI Disclaimer) before paid launch (SURGE-06)
- **Lawyer-reviewed AI prompt templates** before AI features are exposed to real users (SURGE-04)

**Operating mode (per `validation/00_FOUNDER_WAIVER.md`):** SURGE-00 customer-interview and Figma gates are deferred. Build proceeds with founder-decided placeholders for legal-domain enums marked `[PROVISIONAL-FOUNDER-DECIDED]`. The lawyer/advisor gate is no longer just deferred — it has been promoted to a **production-deployment hard stop** for the modules listed above.

---

## 2. Tech Stack (actual installed versions)

| Layer | Value | Notes |
|---|---|---|
| Backend framework | **Laravel 13.16.x** | Upgraded from planned v11 due to security advisories |
| Admin/Customer panel | **Filament v5.6.x** | Upgraded from planned v3 |
| Backend language | PHP 8.3 (CI pin) / PHP 8.5 (local OK) | Production targets 8.3 |
| Frontend (Filament-driven UI) | Filament's Livewire 3 + Tailwind v4 | RTL via Tailwind v4 logical properties — no RTL plugin |
| Frontend (document editor only) | Custom Livewire 3 + Blade + TipTap/ProseMirror | Per SURGE-03 F-03.1 spike output (decision in `decisions/D-02.md`) |
| Database | MySQL 8.x InnoDB | UTF8MB4 charset throughout |
| ORM | Eloquent (Laravel 13) | Soft deletes everywhere; ULID primary keys |
| Primary key strategy | **ULID** via `HasUlids` trait | All tenant entities |
| Cache / queues | Redis | Cloudways-managed |
| Object storage | S3-compatible | `.docx` blobs, document exports, PDF invoices |
| Cloud | Cloudways | Region: DigitalOcean Frankfurt FRA1 |
| CI/CD | GitHub Actions | `.github/workflows/ci.yml` |
| Auth (web) | Google OAuth via Socialite v5 | Session cookies, 7-day lifetime |
| Auth (API) | Laravel Sanctum | Bearer tokens |
| API style | REST + OpenAPI 3.0 | Source of truth: `openapi/spec.yaml` |
| LLM provider | Anthropic Claude (per `decisions/D-03.md`) | Abstracted behind `LlmProvider` interface |
| Billing | Stripe via Laravel Cashier | Per `decisions/D-06.md` |
| PDF generation | Spatie laravel-pdf (or dompdf fallback) | Used for invoices (SURGE-09), legal-doc exports (SURGE-06) |
| Money handling | Laravel `decimal` casts + bcmath | **No floating-point arithmetic on money — ever** |
| Tests | **Pest 4.7.x** | `tests/Unit/`, `tests/Feature/`, `tests/Browser/` |
| E2E tests | Pest Browser Plugin (Playwright) | Editor + .docx round-trip especially |
| Static analysis | **Larastan 3.10 / PHPStan 2.2** at level 6 | Set `phpVersion: 80300` in `phpstan.neon` |
| Code style | Laravel Pint | `pint.json` — laravel preset + ordered imports |
| Localization (framework AR) | **`laravel-lang/common`** | Comprehensive community-maintained AR |
| Localization (project) | `resources/lang/{ar,en}/*.php` | Per-domain files |
| Default locale | `ar` (Arabic, RTL) | English secondary, equal-weight |

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
php artisan queue:work
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
./vendor/bin/phpstan analyse                     # static analysis (level 6)
# Note: PHPStan exits 1 with no output on PHP 8.5. Use CI (PHP 8.3) for definitive runs.

# Migrations
php artisan make:migration create_<table>_table --create=<table>
php artisan make:migration add_<col>_to_<table>_table --table=<table>
php artisan migrate
php artisan migrate:rollback --step=1
php artisan migrate:fresh --seed                 # local only — never in shared envs

# Filament v5
php artisan make:filament-resource <Entity>
php artisan filament:upgrade
php artisan filament:install --panels

# Seeders
php artisan db:seed
php artisan db:seed --class=DemoWorkspaceSeeder
# Jurisdiction-specific (SURGE-08) — NOT run automatically; requires manual invocation post-lawyer-signoff:
php artisan db:seed --class=JordanCourtsSeeder
php artisan db:seed --class=LebanonCourtsSeeder
php artisan db:seed --class=PalestineCourtsSeeder
php artisan db:seed --class=IraqCourtsSeeder

# OpenAPI
./vendor/bin/spectral lint openapi/spec.yaml

# Queue
php artisan queue:listen --queue=default,contracts,exports,reminders,invoices
```

**Local services required:**

- MySQL 8.x on `localhost:3306`
- Redis on `localhost:6379`
- (Optional) MinIO for S3-compatible storage on `localhost:9000`

---

## 4. Architectural Non-Negotiables

These are constraints, not preferences. **Do not violate them.** If a task seems to require violating one, stop and surface the conflict.

### Core principles (apply to every Surge)

1. **Workspace scoping is mandatory.** Every tenant-scoped model uses the `BelongsToWorkspace` trait (`app/Concerns/`). Global Eloquent scope auto-filters by workspace. Cross-workspace data is a P0 bug class.
2. **Policy + FormRequest on every write endpoint AND every Filament resource.** No exceptions. No inline validation. No `if ($user->role === ...)` checks in controllers or Filament pages.
3. **Soft deletes everywhere.** Every tenant-scoped model uses `SoftDeletes`. Hard delete only via explicit admin tooling.  
   **Exception:** trust ledger entries are APPEND-ONLY — no soft delete, no update, no delete at all (see §4b).
4. **Audit columns everywhere.** `created_at`, `updated_at`, `created_by_user_id`, `updated_by_user_id` on every tenant entity.  
   **Exception:** append-only ledgers have only `created_at` + `created_by_user_id` (no update columns by design).
5. **Optimistic locking on concurrent edits.** Compare `updated_at` on update; reject 409 if mismatch.
6. **OpenAPI spec sync.** Every API endpoint added/changed updates `openapi/spec.yaml` in the same PR. CI enforces via `spectral lint`.
7. **Bilingual via Laravel localization only.** Never hardcode user-facing strings. Always `__('domain.key')` or `trans('domain.key')`. Filament resource labels resolve via `static::getNavigationLabel()` / `static::getModelLabel()`.
8. **No N+1.** Lists use eager loading. Larastan rule enforces.
9. **No raw SQL** in app code unless wrapped in a Repository with a corresponding integration test.
10. **Document editing is client-side** (SURGE-03 only). Server stores editor JSON state + `.docx` blob; the TipTap/ProseMirror editor runs in the browser.
11. **Filament is the primary UI for ALL roles.** See §4a.
12. **ULIDs as primary keys.** Use `HasUlids` trait. FK columns are `string(26)`. Never `bigIncrements`.
13. **Polymorphic relationships use a registered morph map** in `app/Providers/AppServiceProvider.php`. Never expose class names as polymorphic type strings (e.g., `App\Models\Matter`); always map to short stable keys (`'matter'`). This survives namespace reorganization and makes the API stable.
14. **No floating-point arithmetic on money.** All monetary columns use `DECIMAL(15,2)` in MySQL and `decimal:2` casts in Eloquent. Multi-step calculations use bcmath. See §4c.
15. **Append-only ledgers** (trust accounts, AI interactions audit, financial audit log) reject UPDATE and DELETE at multiple layers: DB trigger or model lifecycle hook (whichever the engineer agent chooses, document the choice), Policy denial, Pest tests covering both denials. See §4b.

### §4a. Filament-everywhere — the UI architecture

**Decision (2026-06-21, supersedes the SURGE-01 "Filament Owner+Admin only" decision):**

- **Filament v5 is the primary UI for all authenticated users**, regardless of role. The `/admin/{workspace_slug}` path serves the application UI for every role.
- **Granular access is enforced via Policy methods on each Resource**, NOT via panel-level gating.
- **`canAccessPanel()` returns true for any workspace member.**
- **The ONLY exception** is the SURGE-03 **document editor surface**, which is custom Livewire+Blade.
- **Allowed exceptions** (use Blade outside Filament): auth/login (pre-auth), document editor (SURGE-03), Stripe redirect flow (SURGE-06), public legal-doc acceptance pages (SURGE-06), public document share token endpoints (SURGE-03), client-portal public invoice viewing (SURGE-09 future).

### §4b. Append-only ledgers

Three ledgers in the system are append-only and must enforce immutability at multiple layers:

| Ledger | Lives in | Surge | Why append-only |
|---|---|---|---|
| `ai_interactions` | `app/Models/AiInteraction.php` | SURGE-04 | Audit + cost reconciliation |
| `trust_ledger_entries` | `app/Models/TrustLedgerEntry.php` | SURGE-09 | **Bar-association regulatory requirement** |
| `financial_audit_log` | `app/Models/FinancialAuditLog.php` | SURGE-09 | Financial compliance audit |

Enforcement layers (all three required for trust_ledger_entries; first two sufficient for the others):

1. **Model lifecycle hook** in the model boot method blocks `updated` and `deleting` events:
   ```php
   protected static function boot(): void {
       parent::boot();
       static::updating(function () { throw new \LogicException('Append-only ledger'); });
       static::deleting(function () { throw new \LogicException('Append-only ledger'); });
   }
   ```
2. **Policy** denies `update` and `delete` methods (returns false unconditionally).
3. **DB trigger** (for trust_ledger_entries only) at the MySQL level — defense in depth against direct SQL.
4. **Pest tests** verifying all three layers reject mutation attempts.

Corrections to append-only data require offsetting/adjustment entries, never edits.

### §4c. Money handling

Every monetary column:
- DB type: `DECIMAL(15, 2)` (room for billions with cents)
- Eloquent cast: `'amount' => 'decimal:2'`
- All arithmetic uses `bcmath` functions or wrapper service (`MoneyService::add()`, `subtract()`, `multiply()`, `divide()`)
- Currency stored separately as `CHAR(3)` (ISO 4217) — never combined into a single string
- Display formatting via Laravel localization helper, not raw `number_format`

**Anti-patterns to reject:**
- `$total = $a + $b;` where $a and $b are decimal columns → use `bcadd($a, $b, 2)`
- Currency embedded in amount column (`"50.00 USD"`) → split into two columns
- Floats anywhere in financial code path → reject the PR

---

## 5. Folder Structure

```
app/
  Concerns/                  Shared traits (BelongsToWorkspace, AppendOnlyLedger)
  Enums/                     PHP enums (Role, MatterStatus, LitigationStatus, HearingType, AccountType, etc.)
  Filament/
    Resources/               Filament v5 resources (primary UI for all roles)
    Pages/                   Custom Filament pages (KPI dashboard, reconciliation reports)
    Widgets/                 Dashboard widgets (upcoming obligations, KPI progress, pipeline rollup)
  Http/
    Controllers/
      Api/V1/                Versioned API controllers (Sanctum-authed)
      Web/                   Non-Filament web controllers (auth, document editor, share tokens, public invoice viewing)
    Requests/                FormRequest classes
    Resources/               API resources (transformers)
    Middleware/              SetLocale, EnsureWorkspaceSelected
  Livewire/                  Custom Livewire components (document editor, share modal, AI panel)
  Models/                    Eloquent models — singular PascalCase, HasUlids
  Services/                  Domain services (DocumentService, AiOrchestrationService, TrustAccountService, InvoiceService, KpiService, etc.)
  Policies/                  <Entity>Policy
  Jobs/                      Queue jobs
  Mail/                      Bilingual Markdown mailables
  Console/
    Kernel.php               Scheduled tasks (renewal reminders, obligation overdue scan, hearing reminders, KYC expiry scan)
  Providers/                 Service providers (incl. morph map registration in AppServiceProvider)

database/
  migrations/                Timestamped migrations
  seeders/
    DatabaseSeeder.php       Default seeder
    DemoWorkspaceSeeder.php  Demo data
    JordanCourtsSeeder.php   Manually invoked (lawyer-signoff-gated)
    LebanonCourtsSeeder.php  Manually invoked
    PalestineCourtsSeeder.php Manually invoked
    IraqCourtsSeeder.php     Manually invoked
    JordanChartOfAccountsSeeder.php  Manually invoked (CPA-review-gated)
  factories/                 Model factories

resources/
  views/
    auth/                    Login (Blade — pre-auth)
    documents/               Document editor (custom Livewire+Blade)
    invoices/                Invoice PDF templates (AR + EN)
    filament/                Filament render hooks
  lang/ar/                   Arabic translations (per-domain files)
  lang/en/                   English translations
  css/, js/                  Frontend assets

routes/
  web.php                    Web routes
  api.php                    API routes (Sanctum)
  console.php                Artisan commands

tests/
  Unit/                      Pure unit tests
  Feature/
    Api/V1/                  API integration tests
    Filament/                Filament resource tests
    Models/                  Eloquent tests (including append-only enforcement)
    Policies/                Policy tests
    Services/                Service tests
    Concerns/                Trait tests
    Locale/                  Locale + RTL tests
    Middleware/              Middleware tests
    Financial/               Money arithmetic + idempotency tests
    Litigation/              Litigation-specific tests
    Compliance/              Append-only enforcement tests (multi-layer)
  Browser/                   E2E
  fixtures/                  Test files (.docx, sample data)

openapi/
  spec.yaml

decisions/                   D-NN.md ADRs (append-only series)
prompts/                     AI prompt templates (header signoff required)
spikes/                      Throwaway research
validation/                  SURGE-00 deliverables + FOUNDER_WAIVER + lawyer signoffs
planning/                    Surge/Flow .md files
```

---

## 6. Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| Primary key | ULID via `HasUlids` trait | `protected $keyType = 'string';` |
| Foreign key column | `string(26)`, snake_case ending in `_id` | `workspace_id`, `created_by_user_id` |
| Eloquent model | Singular PascalCase | `Contact`, `Matter`, `Hearing`, `TrustAccount` |
| Table | Plural snake_case | `contacts`, `matters`, `hearings`, `trust_accounts` |
| Pivot/junction with model | Singular PascalCase | `WorkspaceMember` |
| Polymorphic morph key | Short snake_case in morph map | `'matter'`, `'contact'`, `'document'`, `'hearing'`, `'invoice'` |
| Migration | Verb-shaped, timestamped | `2026_06_25_140000_create_hearings_table` |
| Filament resource | `<Entity>Resource` | `HearingResource` |
| Policy | `<Entity>Policy` | `HearingPolicy` |
| FormRequest | `<Action><Entity>Request` | `StoreHearingRequest`, `UpdateHearingRequest` |
| API controller | `Api\V1\<Entity>Controller` | `HearingController` |
| Web controller (non-Filament) | `Web\<Entity>Controller` | `DocumentEditorController`, `PublicInvoiceController` |
| Livewire component | `App\Livewire\<Domain>\<Component>` | `App\Livewire\Documents\Editor` |
| Service | `<Entity\|Domain>Service` | `TrustAccountService`, `InvoiceService`, `KpiService` |
| Job | Verb-shaped, `Job` suffix | `SendHearingReminderJob`, `GenerateInvoicePdfJob` |
| Test (API feature) | `tests/Feature/Api/V1/<Entity>ApiTest.php` | — |
| Test (Filament) | `tests/Feature/Filament/<Entity>ResourceTest.php` | — |
| Test (unit service) | `tests/Unit/Services/<Service>Test.php` | — |
| Test (compliance) | `tests/Feature/Compliance/<Topic>Test.php` | `tests/Feature/Compliance/AppendOnlyTrustLedgerTest.php` |
| Localization domain | snake_case filename | `resources/lang/ar/hearings.php`, `resources/lang/ar/invoices.php` |
| Route name | dotted snake_case | `matters.show`, `hearings.create`, `invoices.export` |

---

## 7. Domain Glossary

The product's bounded context. When in doubt about a noun, this is the canonical meaning.

### Core entities (SURGE-01 to SURGE-06 — shipped)

- **Workspace** — A tenant. Every other entity belongs to exactly one. Users can belong to multiple workspaces. Filament v5 tenant scope wired here.
- **WorkspaceMember** — A user's role within a workspace (Owner / Admin / Member). Modeled separately from the pivot for Policy bindings.
- **Role** — Owner, Admin, Member. Enum at `app/Enums/Role.php`. Three values, no more at MVP.
- **Contact** — A person or organization. Polymorphic via `type` enum. Can be flagged as Client, Counterparty, and/or **OpposingCounsel** (added in SURGE-08).
- **Client** — A Contact with `is_client = true`.
- **Counterparty** — A Contact with `is_counterparty = true`. First-class on Matter via `matter_counterparties` pivot.
- **OpposingCounsel** *(SURGE-08)* — A Contact (always `type='person'`) with `is_opposing_counsel = true`. Linked to Matter via the matter_counterparties pivot.
- **Matter** — A piece of legal work for a Client. **Commercial OR litigation** (SURGE-08 added litigation extension). The `is_litigation` boolean determines which tabs appear and which fields are populated.
- **Document** — A working artifact tied to a Matter. Has versions, clauses, optional contract metadata.
- **DocumentVersion** — A snapshot of a Document. Immutable once created.
- **DocumentClause** — A structural addressable unit within a DocumentVersion.
- **ContractMetadata** — One-to-one with Document where `document_type = 'contract'`. Carries value, currency, dates, term, governing law.
- **Obligation** — A dated commitment derived from a contract. Distinct from Task (see below).
- **LibraryClause** — A reusable clause in the workspace's Clause Library. AR + EN paired bodies. Fallback chain support.
- **AiInteraction** — Append-only audit row for every AI call. Prompt, response, model, tokens, cost, accepted/rejected.
- **Subscription** — Stripe-managed (via Cashier). Workspace-level, per-seat pricing.

### Practice Management entities (SURGE-07 — to be built)

- **Task** — A generic to-do. Polymorphic — attaches to ANY tenant entity (Matter, Contact, Document, Counterparty, Obligation, Hearing, Invoice). **Distinct from Obligation.** A Task is operational; an Obligation is contractual.
- **TimeEntry** — Billable or non-billable time logged by a user against a Matter, Document, or Task. Drives KPI computation and Invoice line items.
- **KycChecklist** — A document-collection workflow attached to a Contact. Per-jurisdiction item lists, status tracking, expiry reminders.
- **KycItem** — Individual line item within a KycChecklist (e.g., "National ID", "Beneficial Owner Declaration").
- **KpiTarget** — A target value for a metric (billable hours, matters opened, revenue, win rate) over a period, applicable to a User OR a Team.
- **Team** — A group of users within a workspace. Nested (sub-teams allowed). Used for KPI rollups and Matter assignment.
- **SmartList** — A saved filter combination on any entity list. Per-user or shared-to-workspace. Pinnable for quick access.

### Litigation entities (SURGE-08 — to be built) `[HARD-STOP-LAWYER-REQUIRED for production]`

- **Court** — Reference entity per jurisdiction. Bilingual names, court type, location. Workspace-scoped (firms maintain their own list, or seed available).
- **Judge** — Optional registry of known judges per court. Many firms won't populate.
- **Hearing** — A court hearing for a litigation Matter. Date, court, judge, type, status (scheduled/held/postponed/cancelled), outcome notes.
- **CourtReview** — A judge-issued decision on a Matter. Linked optionally to the Hearing where issued. Auto-creates an Obligation if appealable (links to the appeal-deadline reminder system).
- **ServiceLogEntry** — Process service tracking (serving legal documents on parties). Method, date, served party, recipient, proof document.

### Financial entities (SURGE-09 — to be built) `[F-09.2 HARD-STOP-LAWYER+CPA-REQUIRED for production]`

- **Account** — Entry in the workspace's Chart of Accounts. Tree structure (parent/child). Type: asset/liability/equity/income/expense/trust.
- **TrustAccount** — Per-client regulated trust account. Has a current_balance (computed from the append-only ledger).
- **TrustLedgerEntry** — Append-only entry in a TrustAccount's ledger. Deposit / withdrawal / retainer-applied / refund / interest / adjustment.
- **JournalEntry** — A double-entry posting (multiple JournalEntryLines summing to balanced debits = credits) against the Chart of Accounts.
- **JournalEntryLine** — Single debit or credit against one Account, child of a JournalEntry.
- **Invoice** — A bill issued by the firm to a Client. Lines optionally link to TimeEntry records (auto-bill time). Status workflow: draft → sent → viewed → partially_paid → paid (or overdue / cancelled / void).
- **InvoiceLine** — Line item on an Invoice.
- **Receipt** — A payment received from a client. May or may not be applied to a specific Invoice. Deposited to either an operating Account or a TrustAccount.

### CRM entities (SURGE-09 — to be built)

- **Pipeline** — A configurable workflow of stages for processing Leads. Per-workspace.
- **Lead** — A pre-Contact entity representing a prospective client. NOT a Contact. NOT a Matter. Has its own pipeline progression.
- **Opportunity** — A specific deal under a Lead. Has expected value, win probability, expected matter type. On close-won, converts to a Matter (via OpportunityConversionService).

### Words NOT in our domain (intentionally — even under the breadth pivot)

- "Case" (we say Matter)
- "Pleading" (out — Year-2 templates if asked)
- "Discovery" (out — Year-2)
- "Email-as-an-entity" (out — Year-2 integration only)
- "Calendar-as-an-entity" (out — Year-2 integration only)
- "KPI dashboard widget per-account" beyond what's in SURGE-07 (out — Year-2 deeper analytics)
- "Workflow automation" (out — no SURGE-XX exists for this)
- "Form template" (out — same)

---

## 8. Localization Rules

- **Default locale is `ar`.** Anonymous visitors see Arabic unless they override via `?lang=en`.
- **Every user-facing string lives in `resources/lang/{ar,en}/<domain>.php`.** No exceptions.
- **Framework strings (validation, auth, pagination, passwords, actions, HTTP statuses) come from `laravel-lang/common`** — already installed. Do NOT duplicate.
- **Filament strings** for AR come from `laravel-lang/common`'s `ar.json` (~230 keys). Filament v5 auto-loads.
- **`<html dir>` and `<html lang>`** are set by `SetLocale` middleware (5-step resolution chain).
- **Tailwind v4 logical properties** handle RTL automatically (`ms-4`, `pe-2`). No `tailwindcss-rtl` plugin needed. Direction-specific styling uses `ltr:` / `rtl:` variants.
- **Dates** — Gregorian only at MVP. Hijri is Year-2 backlog.
- **Numbers** — Latin digits (1234) in both locales for now.
- **Currency** — Display ISO code + amount (e.g., `USD 50,000.00`). Currency stored separately from amount (see §4c).
- **Plurals** — Use `trans_choice()` and `:count` placeholders. Arabic has 6 plural forms; account for all.
- **AR translations are NEVER auto-generated.** Either the Product Designer's Content Spec provides them, or a key is flagged `[NEEDS-AR-TRANSLATION-REVIEW]` and routed to advisor/translator.
- **Mixed-direction content in documents** — paragraphs inherit their own direction via `dir="auto"` or per-block `dir` attribute from the editor's JSON state.
- **Court names / Judge names (SURGE-08)** — stored bilingual (`name_ar`, `name_en`); UI renders the locale-matching variant; both indexed for search.

---

## 9. Testing & Quality Gates

Every PR must pass:

1. **Pest test suite green.** All `tests/Unit/`, `tests/Feature/`, and (for affected Surges) `tests/Browser/`.
2. **Pint clean.** `pint --test` returns no diffs.
3. **Larastan level 6 clean.** `phpstan analyse` returns no errors on CI (PHP 8.3).
4. **OpenAPI spec valid + in sync.** `spectral lint openapi/spec.yaml` clean. Every new route has a matching spec entry.
5. **Workspace isolation tested.** For any new tenant-scoped entity, at least one test verifies cross-workspace data is invisible.
6. **AR locale smoke test.** For any new UI-bearing Flow, at least one test asserts AR-locale rendering with `<html dir="rtl">`.
7. **Filament resource tests.** Any new Filament resource gets a `<Entity>ResourceTest.php` in `tests/Feature/Filament/` covering: list page renders, create works, edit works, role-based access enforced via policy, AR-locale label rendering.
8. **Append-only enforcement tests** (for SURGE-04 ai_interactions, SURGE-09 trust_ledger_entries, SURGE-09 financial_audit_log). Multi-layer tests verifying: model lifecycle blocks update + delete; Policy denies update + delete; DB trigger blocks (for trust_ledger_entries); offsetting/adjustment entry pattern works as correction mechanism.
9. **Financial idempotency tests** (for SURGE-09 invoice payments, retainer drawdowns). Replaying a request with the same Idempotency-Key MUST NOT double-post. Tests cover: same-key duplicate, different-key parallel, key-collision-across-workspaces.
10. **Money precision tests** (for any new financial calculation). Verify no floating-point arithmetic; verify decimal precision retained through multi-step operations; verify currency mismatch rejected (cannot add USD to JOD without explicit conversion).
11. **Litigation procedural tests** (for SURGE-08). Each Levant jurisdiction's procedural assumptions verified independently. Adding a new jurisdiction requires its own test suite extension.

**Test data hygiene:**
- Use factories for all test data. Never insert raw rows.
- For shared setup, use Pest's `beforeEach()` per test file; avoid global state.
- Browser tests use real `.docx` fixtures from `tests/fixtures/docx/`.
- Litigation tests use anonymized real Levant court case structures (no real case data — manually constructed but realistic).
- Financial tests use specific dollar amounts that exercise decimal-precision edge cases (e.g., `123.456` should reject; `1/3` repeating should round consistently).

---

## 10. What NOT to Do (post-D-09 breadth pivot)

### Schema-level constraints — STILL REJECT these even under the breadth pivot

The codebase must NEVER contain:

- **Native email module:** `emails` table for storing native-stored emails (incoming/outgoing). Year-2 integration with Outlook/Gmail via OAuth — do NOT build a native email store.
- **Native calendar module:** `calendar_events` table with full ICS support, recurring events, attendees, etc. Year-2 integration with Google Calendar / Outlook — do NOT build a native calendar.
- **Form templates engine:** `form_templates`, `form_template_fields` tables. Hardcoded sensible defaults instead.
- **Automations / workflow engine:** `automations`, `automation_triggers`, `automation_actions` tables. Out of scope.
- **Global config builder:** `custom_fields` on entities. Use proper schema additions per Surge, not user-configurable custom fields.
- **Mobile app code:** any iOS/Android/React Native code. Web-only remains.
- **Native messaging/chat:** `messages` or `chat_threads` tables. Out of scope entirely.
- **AI extraction tables** populated automatically without user review (e.g., auto-extracted obligations marked as approved). User must confirm AI-extracted data before it becomes "real" data.

### Schema constraints REMOVED under D-09 (these are now in-scope under SURGE-07/08/09)

The previous v0.1 prohibition on the following is **LIFTED**:

- `judges`, `courts`, `hearings`, `court_reviews`, `service_log_entries` — built in SURGE-08
- `accounts` (Chart of Accounts), `trust_accounts`, `trust_ledger_entries`, `journal_entries`, `invoices`, `receipts` — built in SURGE-09
- `leads`, `pipelines`, `opportunities` — built in SURGE-09 CRM
- `tasks`, `time_entries`, `kyc_checklists`, `kyc_items`, `kpi_targets`, `teams`, `smart_lists` — built in SURGE-07
- Matter litigation fields (`is_litigation`, `judge_id`, `court_id`, `court_case_number`, `representation_role`, `litigation_status`, `filed_date`, `next_hearing_date`) — added in SURGE-08 F-08.1

### Hard stops on production deployment (do not waive)

Even under the breadth pivot, these gates remain binding:

| Hard Stop | Gates which Surge/Flow | Resolution |
|---|---|---|
| Lawyer-drafted ToS, Privacy, DPA, AI Disclaimer | SURGE-06 paid launch | Lawyer drafts in AR + EN; signed PDFs in `validation/06_legal_docs/` |
| Lawyer-reviewed AI prompt templates | SURGE-04 real-user exposure | Each prompt in `prompts/` carries `[LEGAL-REVIEW-APPROVED: <name> <date>]` header |
| Lawyer signoff on litigation procedural model | SURGE-08 production deployment for real cases | `validation/08_litigation_lawyer_signoff.md` signed |
| Lawyer + CPA signoff on Trust Accounts | SURGE-09 F-09.2 production deployment for real client funds | `validation/09_trust_account_lawyer_signoff.md` AND `validation/09_trust_account_cpa_signoff.md` signed |
| KYC checklist content review | SURGE-07 F-07.3 — recommended not hard-blocked | `[ADVISOR-REVIEW-RECOMMENDED]` marker in code |

The Engineer agent reads this section and refuses to mark a Surge "Production Ready" when any applicable hard stop is unresolved. Code-complete is permitted; production deployment is not.

### UI architecture constraints

- Do not build parallel Blade pages for entities that have Filament resources. Filament is the UI. Blade only for: pre-auth, document editor (SURGE-03), Stripe redirect (SURGE-06), public share tokens (SURGE-03), public legal-doc acceptance (SURGE-06), public invoice viewing (SURGE-09 future).
- Do not block Filament panel access by role. Use policies for granular method-level access. `canAccessPanel()` returns true for any workspace member.

### Operational constraints

- Do not call LLM providers directly from the browser. Always proxy through the backend.
- Do not store LLM API keys anywhere except `.env`.
- Do not log prompt content unless the `AiInteraction` audit row receives it.
- Do not bypass `BelongsToWorkspace` scope with `withoutGlobalScopes()` except in explicit system-admin context.
- Do not commit `.env` files, fixture files containing real client data, or API keys.
- Do not auto-translate Arabic strings via Google Translate / DeepL / LLM. Flag for human review.
- **Do not update or delete rows in append-only ledgers under any circumstance.** Corrections via offsetting entries only.
- **Do not perform floating-point arithmetic on monetary values.** Reject any PR that does.
- **Do not seed jurisdiction-specific data (courts, accounts) without the corresponding lawyer/CPA signoff in `validation/`.**

### Process constraints

- Do not begin work on a Surge whose upstream gates are not satisfied per `validation/00_FOUNDER_WAIVER.md`. Use the AODC_Software_Engineer agent's Gate Status Report.
- Do not skip Pest tests "just to ship faster." CI will block.
- Do not modify `CLAUDE.md` without explicit founder direction. Updates are append-only via the §Update log at top.
- Do not modify any file under `decisions/` without an ADR-style superseding entry. ADRs are append-only.
- Do not mark a Surge "Production Ready" if any applicable hard stop (§10 hard stops table) is unresolved.

---

## 11. AODC Pipeline Reference

| Location | Contents |
|---|---|
| `planning/00_MVP_ROADMAP_v0.2.md` | Master plan v0.2 — breadth strategy (active) |
| `planning/00_MVP_ROADMAP.md` | Master plan v0.1 — depth wedge (superseded by D-09, kept for history) |
| `planning/README.md` | Index + non-negotiables + naming conventions |
| `planning/HAQQ_COVERAGE_GAP_ANALYSIS.md` | Module-by-module coverage map |
| `planning/SURGE-NN-*.md` | Per-Surge plans (S-01 to S-06 shipped; S-07 to S-09 to build; S-10 production hardening) |
| `validation/00_FOUNDER_WAIVER.md` | **Active operating waiver** — which gates are deferred, which are hard stops |
| `validation/08_litigation_lawyer_signoff.md` | Hard-stop signoff for SURGE-08 production (when signed) |
| `validation/09_trust_account_lawyer_signoff.md` | Hard-stop signoff for SURGE-09 F-09.2 production (when signed) |
| `validation/09_trust_account_cpa_signoff.md` | Hard-stop CPA signoff for SURGE-09 F-09.2 production (when signed) |
| `decisions/D-NN.md` | Architecture decision records (append-only series) |
| `decisions/D-09_breadth_pivot.md` | **The strategic pivot ADR — read this before any breadth-Surge work** |
| `prompts/` | AI prompt templates — each requires `[LEGAL-REVIEW]` header signoff |
| `spikes/` | Throwaway research |

**When asked to do work, expect a Tech Task Package (TTP)** from the AODC_Software_Engineer agent.

**If a task arrives without a TTP** — stop and ask: "Should this be routed through the AODC_Software_Engineer agent first to produce a Tech Task Package?"

---

## 12. When in doubt

Order of precedence for resolving uncertainty:

1. **This file (`CLAUDE.md`).** Wins if it contradicts other documents. Flag the contradiction.
2. **`decisions/D-09_breadth_pivot.md` and subsequent ADRs.** Strategic locks.
3. **`validation/00_FOUNDER_WAIVER.md`.** Current operating mode.
4. **`planning/00_MVP_ROADMAP_v0.2.md`.** Strategic source of truth.
5. **The specific Surge plan** for the work in question.
6. **The Tech Task Package** for the Flow in question.
7. **The Founder** (via the AO). If 1–6 do not resolve it, escalate.

Do not invent answers. Do not guess at intent. Stop, surface the question, wait for direction.

---

*End of CLAUDE.md. Welcome to the project.*
