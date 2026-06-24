# CLAUDE.md — Project Memory for Claude Code

> This file is loaded by Claude Code on every session against this repository.
> Read it first. It encodes everything you need to know before touching the codebase.
> **Version: v7** • **Last meaningful update: 2026-06-24**
> Changes since v6: stack version corrections (Laravel 13.x, Filament v5.x, Pest 4.x), direct Stripe SDK (not Cashier), planning path migration (`docs/`), SURGE-14 architectural decisions (admin guard, append-only ledgers, defense-in-depth invariants, no-secrets-in-audit, platform-vs-tenant scope split, locale key parity, no-hardcoded-strings enforcement).

---

## 1. Project

A **bilingual (Arabic/English), AI-native commercial-contracts workspace** for small Levant law firms (2–10 lawyers). The product makes the contract document itself the workspace — editable, versioned, clause-aware, with AI inline — and explicitly avoids the litigation, accounting, and CRM breadth of competitors (HAQQ.ai, Clio).

**Single hardest test we must pass:** a lawyer imports a real `.docx` contract, edits clauses (mixed AR/EN, RTL/LTR), exports back to `.docx`, and opens the file in Microsoft Word with formatting intact. If round-trip fidelity breaks, the wedge breaks.

**MVP audience:** small commercial-law firms in Jordan, Lebanon, Palestine, Iraq.
**Wedge (PROVISIONAL until Arabic AI test against HAQQ completes):** either Arabic-legal depth or integrated single-surface UX. Build is wedge-agnostic at the structural level.

---

## 2. Tech Stack

| Layer | Value | Notes |
|---|---|---|
| Backend framework | **Laravel 13.x** | Single monolith for MVP. Wave-target: 13.16. |
| Admin panel | **Filament v5.x** | Wave-target: 5.6. Schema-based form API (`Schema::make()`), NOT v3's `Form::make()`. |
| Workspace panel | **Filament v5.x (second panel)** | Separate panel registered alongside admin panel. |
| Backend language | PHP 8.3 | Use modern syntax (readonly, enums, etc.) |
| Frontend (web) | Blade + Tailwind + Livewire 3 | No SPA. Server-rendered first. |
| Editor (in-document) | TipTap or CKEditor (decided in `decisions/D-02.md`) | Lives client-side, called via Livewire |
| Database | MySQL 8.x InnoDB | UTF8MB4 charset throughout |
| ORM | Eloquent | Soft deletes on tenant-scoped models only |
| Cache / queues | Redis | Cloudways managed |
| Object storage | S3-compatible | For .docx blobs, document exports |
| Cloud | Cloudways (DigitalOcean FRA1) | Region per `decisions/D-01.md` |
| CI/CD | GitHub Actions | `.github/workflows/` |
| Auth (web) | Google OAuth via Socialite | Session cookies, `web` guard |
| Auth (admin) | **Email + password via `admin` guard** | Session-based, distinct cookie (`platform_admin_session`), path-scoped to `/admin`, SameSite=Strict. **NOT Sanctum tokens.** |
| Auth (API) | Laravel Sanctum | Bearer tokens. **Workspace users only — never admins.** |
| API style | REST + OpenAPI 3.0 | Source of truth: `openapi/spec.yaml`. **Admin panel introduces zero public REST endpoints.** |
| LLM provider | Anthropic Claude (default) or per `decisions/D-03.md` | Abstracted behind `LlmProvider` interface |
| Billing | **Stripe direct SDK** (NOT Laravel Cashier) | Custom subscription lifecycle. Stripe API version pinned to `2025-08-27.acacia` in `config/services.php`. Webhooks idempotent via `stripe_webhook_events` ledger. |
| Tests | **Pest 4.x** | Wave-target: 4.7. `tests/Unit/`, `tests/Feature/`, `tests/Browser/`. PHPUnit syntax not acceptable. |
| E2E tests | Pest Browser Plugin (Playwright) | For editor + .docx round-trip especially. Also RTL snapshot tests. |
| Static analysis | Larastan level 6 | `phpstan.neon` |
| Code style | Laravel Pint | `pint.json` |
| Localization | Laravel localization | `resources/lang/{ar,en}/*.php`. **Key structure parity enforced.** |
| Default locale | `ar` (Arabic, RTL) | `en` is secondary, equal-weight at UI level |

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
./vendor/bin/pest                       # unit + feature (Pest 4.x)
./vendor/bin/pest --filter=Document     # subset
./vendor/bin/pest tests/Browser         # E2E (slow)
./vendor/bin/pest --coverage --min=95   # coverage gate

# Code quality
./vendor/bin/pint                       # auto-format
./vendor/bin/pint --test                # check only (CI)
./vendor/bin/phpstan analyse            # static analysis (level 6)

# Migrations
php artisan make:migration create_<table>_table --create=<table>
php artisan make:migration add_<col>_to_<table>_table --table=<table>
php artisan migrate
php artisan migrate:rollback --step=1
php artisan migrate:fresh --seed         # local only — never in shared envs

# Filament (v5.x)
php artisan make:filament-resource <Entity> --panel=admin
php artisan make:filament-resource <Entity> --panel=app
php artisan filament:assets              # publish assets on deploy
php artisan filament:upgrade

# Seeders
php artisan db:seed
php artisan db:seed --class=AdminUserSeeder       # idempotent; production-safe
php artisan db:seed --class=DemoWorkspaceSeeder

# OpenAPI
./vendor/bin/spectral lint openapi/spec.yaml

# Queue
php artisan queue:listen --queue=default,contracts,exports,stripe-webhooks
php artisan horizon                      # if Horizon installed (TBD)

# Stripe (local testing)
stripe listen --forward-to localhost:8000/webhooks/stripe
```

**Local services required:**

- MySQL 8.x on `localhost:3306`
- Redis on `localhost:6379`
- (Optional) MinIO for S3-compatible storage on `localhost:9000`
- (Optional) Stripe CLI for local webhook testing

---

## 4. Architectural Non-Negotiables

These are constraints, not preferences. **Do not violate them.** If a task seems to require violating one, stop and surface the conflict.

### Tenant-scoped (workspace) entities

1. **Workspace scoping is mandatory for tenant-scoped entities.** Every tenant-scoped model uses the `BelongsToWorkspace` trait. Every query is automatically scoped via a global Eloquent scope. Cross-workspace data leakage is a P0 bug class. **Exception: platform-level entities** (see rule 12 below).
2. **Policy + FormRequest on every write endpoint.** No exceptions. No inline validation. No `if ($user->role === ...)` checks in controllers.
3. **Soft deletes on tenant-scoped entities.** Every tenant-scoped model uses `SoftDeletes`. Hard delete only via explicit admin tooling. **Append-only ledger tables and platform-level entities are exempt** (see rules 11 and 12).
4. **Audit columns everywhere.** `created_at`, `updated_at`, `created_by_id`, `updated_by_id` on every tenant entity.
5. **Optimistic locking on concurrent edits.** Compare `updated_at` on update; reject HTTP 409 if mismatch.

### Cross-cutting

6. **OpenAPI spec sync.** Every public API endpoint added/changed updates `openapi/spec.yaml` in the same PR. CI enforces. **Admin-panel work does NOT touch `openapi/spec.yaml`** (admin panel is browser-only Livewire/Filament, not REST).
7. **Bilingual via Laravel localization only.** Never hardcode user-facing strings. Always `__('domain.key')`. **Enforced by `NoHardcodedStringsTest` for every domain folder.**
8. **No N+1.** Lists use eager loading. Larastan rule enforces (`larastan/larastan` baseline).
9. **No raw SQL in app code** unless wrapped in a Repository with a corresponding integration test.
10. **Document editing is client-side.** Server stores editor JSON state + .docx blob; the editor library runs in the browser. AI calls go through the backend, never browser → LLM provider directly.

### Ledgers, invariants, secrets, scope, locale (SURGE-14 additions)

11. **Cursor pagination only.** No `paginate()` (offset). All list/index endpoints — public API, admin Filament resources, internal services — use Laravel cursor pagination. Offset pagination is a defect.
12. **Append-only ledger tables.** The following are append-only: `admin_activity_log`, `subscription_events`, `stripe_webhook_events`, `admin_impersonation_sessions`. For each: no `updated_at` column, no soft deletes, no Filament Edit/Delete actions, no policy methods for update/delete. Corrections via new offsetting rows only.
13. **Platform-level entities are NOT workspace-scoped.** `AdminUser`, `AdminActivityLog`, `Plan`, `Subscription` (the subscription record itself, owned by a workspace but written/managed by platform admins), `subscription_events`, `stripe_webhook_events`, `admin_impersonation_sessions` are platform-level. They do NOT use `BelongsToWorkspace`. They live in `app/Models/` without the trait.
14. **Defense-in-depth for critical invariants.** Last-super-admin-must-exist, single-active-timer-per-user (Year-2 feature), single-active-impersonation-per-admin, and similar invariants are enforced at THREE layers: FormRequest validation + Policy method + Pest test asserting database-level invariant. All three layers must exist; any one alone is insufficient.
15. **No secrets in audit payloads.** `password`, `password_confirmation`, `new_password`, `current_password`, Stripe webhook secrets, Stripe API keys, Sanctum tokens — none of these appear in any audit log row, console log, error report, queue job argument, or Stripe webhook event row. Enforced by `AuditLogPasswordLeakageTest`.
16. **Auth guard isolation.** Three guards: `web` (workspace session), `admin` (admin panel session), `sanctum` (workspace API tokens). They do not cross-authenticate. `AdminUser` model NEVER uses `HasApiTokens` trait. `User` model can use `HasApiTokens` (workspace users authenticate the API via Sanctum). Session cookies are path-scoped and distinct.
17. **Localization key parity.** `resources/lang/ar/*.php` and `resources/lang/en/*.php` must have identical key structures. Enforced by `LocaleKeyParityTest` in CI.
18. **Stripe webhook idempotency.** Every Stripe webhook is recorded in `stripe_webhook_events` keyed on `stripe_event_id` BEFORE any side effect. Replays no-op. Signature verification non-negotiable.

---

## 5. Folder Structure

```
app/
  Models/                    Eloquent models — singular PascalCase
                             (both tenant-scoped and platform-level live here)
  Concerns/                  Shared model traits (e.g., BelongsToWorkspace)
  Enums/                     PHP 8.1+ enums (Role, MatterStatus, AdminRole,
                             AdminActivityEventType, SubscriptionState, etc.)
  Http/
    Controllers/
      Api/V1/                Versioned API controllers (workspace-side)
      Web/                   Server-rendered web controllers (workspace-side)
      Webhooks/              External webhooks (e.g., StripeWebhookController)
    Requests/                FormRequest classes — Store<X>Request, Update<X>Request
    Resources/               API resources (transformers)
    Middleware/              Custom middleware
      Admin/                 Admin-guard-specific middleware
                             (EnforceIdleTimeout, EnforceAbsoluteTimeout)
  Filament/
    Admin/                   Admin panel (platform operators)
      Resources/             <Entity>Resource for admin panel
      Pages/                 Custom pages (Auth\Login, Dashboard)
      Widgets/               Dashboard widgets
      Components/            Custom render-hook components (e.g., UserMenu)
    App/                     Workspace panel (firm users)
      Resources/             <Entity>Resource for workspace panel
  Services/                  Domain services (e.g., DocumentService,
                             SubscriptionEntitlementService,
                             AdminActivityLogger)
  Policies/                  Authorization policies — <Entity>Policy
  Jobs/                      Queue jobs — verb-shaped names
  Mail/                      Mailable classes
  Console/
    Kernel.php               Scheduled tasks
  Providers/
    AdminPanelProvider.php   Filament admin panel registration
    AppPanelProvider.php     Filament workspace panel registration

config/
  admin.php                  Admin-panel-specific config (session timeouts,
                             password rules, locale defaults)

database/
  migrations/                Timestamped migrations
  seeders/                   Seeders (AdminUserSeeder is production-safe)
  factories/                 Model factories

resources/
  views/                     Blade templates
  lang/ar/                   Arabic translations (parity-enforced with en/)
  lang/en/                   English translations (parity-enforced with ar/)
  css/, js/                  Frontend assets (Vite-bundled)

routes/
  web.php                    Web routes (session auth, `web` guard)
                             Includes Stripe webhook route (no auth, signed)
  api.php                    API routes (Sanctum auth, `sanctum` guard)
  console.php                Artisan commands
  (admin routes registered via Filament panel provider, NOT a route file)

tests/
  Unit/                      Pure unit tests
  Feature/                   HTTP + Filament integration tests
    Api/V1/                  Mirror controller structure (workspace-side)
    Filament/                Workspace Filament resource tests
    Admin/                   Admin panel tests (auth, resources, middleware,
                             policy enforcement, guard isolation)
  Browser/                   E2E via Pest Browser Plugin
    Admin/                   Admin panel RTL snapshot tests
    __snapshots__/admin/     Baseline screenshots (8 screens × 2 locales)
  fixtures/                  Test files (.docx samples, etc.)

openapi/
  spec.yaml                  REST API specification — single source of truth
                             (workspace API only; admin panel has no public API)

docs/
  README.md                  Index of all planning artifacts
  00_MVP_ROADMAP.md          Master plan
  SURGE-NN-*.md              Per-Surge plans
  WAVE-NN.N-*.md             Per-Wave Wave-Ready Packages
  validation/                SURGE-00 deliverables + advisor meeting logs
    02_advisor_meeting_log.md    Khaldoun's documented decisions (source of truth)

decisions/
  D-01.md … D-NN.md          Architecture decision records (append-only)

prompts/                     AI prompt templates (must have legal-review header)
spikes/                      Throwaway research code (deleted post-decision)
```

**Note on folder migration:** v6 referenced `planning/` for Surge/Wave files; v7 canonicalizes to `docs/`. Any reference to `planning/` in older Surge plans should be read as `docs/`.

---

## 6. Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| Eloquent model (tenant) | Singular PascalCase | `Contract`, `Matter`, `Counterparty` |
| Eloquent model (platform-level) | Singular PascalCase | `AdminUser`, `AdminActivityLog`, `Plan`, `StripeWebhookEvent` |
| Table | Plural snake_case | `contracts`, `matters`, `admin_users`, `admin_activity_log` |
| Ledger table | Singular snake_case ending `_log` or `_events` | `admin_activity_log`, `subscription_events` |
| Pivot table | Alphabetical concat | `matter_counterparties` |
| Migration | Verb-shaped, timestamped | `2026_06_24_create_admin_users_table` |
| Filament resource (workspace) | `App\Filament\App\Resources\<Entity>Resource` | `App\Filament\App\Resources\ContractResource` |
| Filament resource (admin) | `App\Filament\Admin\Resources\<Entity>Resource` | `App\Filament\Admin\Resources\AdminUserResource` |
| Policy | `<Entity>Policy` | `ContractPolicy`, `AdminUserPolicy` |
| FormRequest (store) | `Store<Entity>Request` | `StoreContractRequest`, `StoreAdminUserRequest` |
| FormRequest (update) | `Update<Entity>Request` | `UpdateContractRequest`, `UpdateAdminUserRequest` |
| API controller | `Api\V1\<Entity>Controller` | `app/Http/Controllers/Api/V1/ContractController.php` |
| Web controller | `Web\<Entity>Controller` | `app/Http/Controllers/Web/ContractController.php` |
| Webhook controller | `Webhooks\<Source>WebhookController` | `app/Http/Controllers/Webhooks/StripeWebhookController.php` |
| Service | `<Entity\|Domain>Service` | `DocumentService`, `SubscriptionEntitlementService`, `AdminActivityLogger` |
| Middleware (admin-specific) | `app/Http/Middleware/Admin/<Name>.php` | `EnforceIdleTimeout`, `EnforceAbsoluteTimeout` |
| Job | Verb-shaped, `Job` suffix | `ImportDocumentJob`, `SyncStripeSubscriptionJob` |
| Test (API feature) | `tests/Feature/Api/V1/<Entity>ApiTest.php` | — |
| Test (workspace Filament) | `tests/Feature/Filament/<Entity>ResourceTest.php` | — |
| Test (admin Filament) | `tests/Feature/Admin/Resources/<Entity>ResourceTest.php` | — |
| Test (unit service) | `tests/Unit/Services/<Entity\|Domain>ServiceTest.php` | — |
| Test (invariant) | `tests/Feature/<Domain>InvariantTest.php` | `AdminUsersInvariantTest` |
| Localization domain | snake_case filename | `resources/lang/ar/matters.php`, `resources/lang/ar/admin.php` |
| Route name | dotted snake_case | `matters.show`, `documents.export`, `filament.admin.resources.admin-users.index` |
| Stripe event ID column | `stripe_event_id` | (unique, idempotency key) |

---

## 7. Domain Glossary

The product's bounded context. When in doubt about a noun, this is the canonical meaning.

### Tenant-scoped entities (firm-facing)

- **Workspace** — A tenant. Every tenant-scoped entity belongs to exactly one workspace. Users can belong to multiple workspaces.
- **WorkspaceMember** — A user's role within a specific workspace (Owner / Admin / Member). Stored in the pivot.
- **User** — A workspace user. Authenticates via `web` guard (session) or `sanctum` (API). Distinct from `AdminUser`.
- **Role (workspace)** — Owner, Admin, Member. Three values. No more at MVP. (Distinct from admin roles below.)
- **Contact** — A person or organization. Polymorphic. Can be flagged as Client and/or Counterparty.
- **Client** — A Contact with `is_client = true`. Represented by us.
- **Counterparty** — A Contact with `is_counterparty = true`. The other side of a deal.
- **Matter** — A piece of legal work for a Client. Commercial scope only. **Has NO court fields.** Title, Client, Counterparty(s), Status, Stage, Practice Area, Notes.
- **Document** — A working artifact tied to a Matter. Most commonly a contract. Has versions, clauses, optional contract metadata.
- **DocumentVersion** — A snapshot of a Document at a point in time. Immutable once created. Every save creates a new version.
- **DocumentClause** — A structural addressable unit within a DocumentVersion. Extracted on save by `ClauseExtractionService`.
- **ContractMetadata** — One-to-one with Document where `document_type = 'contract'`. Carries value, currency, dates, term, governing law.
- **Obligation** — A dated commitment derived from a contract. Has type, responsible party, due date, status. Drives reminder emails.
- **LibraryClause** — A reusable clause in the workspace's Clause Library. May have AR + EN paired bodies. May be marked as a fallback of another clause (the playbook).
- **AiInteraction** — An audit row for every AI call. Carries prompt, response, model, tokens, cost, accepted/rejected flag.

### Platform-level entities (admin-facing, SURGE-14)

- **AdminUser** — Platform operator. Distinct from `User` (workspace user). Authenticates via `admin` guard. Has one of four roles: `super_admin`, `support`, `finance`, `read_only`. NEVER uses Sanctum tokens. Never workspace-scoped.
- **AdminActivityLog** — Append-only audit ledger of every admin-side action. PDPL Article 13 accountability surface. Read-democratically (all admin roles can view all rows).
- **Plan** — A subscription tier. Three plans at launch: `starter` ($20/seat USD), `pro` ($25/seat USD), `enterprise` ($30/seat USD). Each carries per-seat USD price, caps (seats, storage, matters, contacts), and `features` (JSON flags).
- **Subscription** — A workspace's subscription record. Owned by a workspace but managed by platform admins via the Stripe direct SDK. Five lifecycle states: `trial` (14 days, no card) → `active` → `past_due` (7-day grace) → `suspended` → `cancelled` (14 days total). Mirrored to Stripe via the SDK.
- **SubscriptionEvent** — Append-only entry in the subscription lifecycle ledger. Event types include `trial_started`, `activated`, `payment_failed`, `past_due_entered`, `suspended`, `cancelled`, `plan_changed`, `seats_changed`.
- **StripeWebhookEvent** — Append-only idempotency ledger for Stripe webhooks. Keyed on `stripe_event_id` (unique). Records signature verification result + processing outcome.
- **AdminImpersonationSession** — Append-only record of an admin impersonating a workspace user. Carries start/end timestamps, IP address, audit purpose, terminating event (timeout / explicit / forced).

### Subscription state machine

```
   ┌─────────────┐
   │   trial     │  (14d, no card required)
   └──────┬──────┘
          │ first successful payment
          ▼
   ┌─────────────┐  ◄────────────────────────┐
   │   active    │                           │ payment recovered
   └──────┬──────┘                           │
          │ payment fails                    │
          ▼                                  │
   ┌─────────────┐  ────────────────────────┘
   │  past_due   │  (7-day grace)
   └──────┬──────┘
          │ grace expires
          ▼
   ┌─────────────┐
   │  suspended  │  (read-only via BlockSuspendedWorkspace mw)
   └──────┬──────┘
          │ 7 more days (14 days total in degraded state)
          ▼
   ┌─────────────┐
   │  cancelled  │  ──►  PDPL purge schedule (Wave 14.9)
   └─────────────┘
```

### Words NOT in our domain (off-strategy)

Case (we say Matter), Hearing, Court, Judge, Opponent, Pleading, Discovery, Service of Process, Invoice (Year-2), Trust Account (never), KPI (Year-2), Lead (Year-2), Email-as-an-entity (Year-2).

---

## 8. Localization Rules

- **Default locale is `ar`.** Anonymous visitors see Arabic unless they override via `?lang=en`.
- **Every user-facing string lives in `resources/lang/{ar,en}/<domain>.php`.** No exceptions.
- **No hard-coded user-facing strings.** Every visible string goes through `__('domain.key')`. Enforced by `NoHardcodedStringsTest` per domain folder.
- **Key parity between locales.** `ar/*.php` and `en/*.php` must have identical key structures (same keys in both, same nesting depth, same key names). Enforced by `LocaleKeyParityTest`.
- **`<html dir>` and `<html lang>` are set by `SetLocale` middleware** based on resolved locale. RTL/LTR is automatic.
- **Tailwind RTL utilities** are enabled via the official plugin. Use `ltr:` and `rtl:` variants when direction-specific styling is needed. Prefer logical properties (`ps-*`, `pe-*`, `ms-*`, `me-*`, `start-*`, `end-*`) over directional ones (`pl-*`, `pr-*`).
- **Dates** — Gregorian only at MVP. Hijri is a Year-2 backlog item.
- **Numbers** — Use Western Arabic numerals (0–9) in both locales (per Khaldoun input — Jordan legal/business context). Eastern Arabic numerals (٠–٩) is OUT of scope.
- **Currency** — USD only at MVP. Display `$1,234.56` (prefix, two decimals, comma thousands). User-locale formatting for decimal separator deferred.
- **Plurals** — Use Laravel's `trans_choice()` and `:count` placeholders. Arabic has 6 plural forms; account for all where the message is plural-sensitive.
- **AR translations are NEVER auto-generated.** Either the Product Designer's Content Spec provides them, or a key is flagged `[NEEDS-AR-TRANSLATION-REVIEW]` and routed to the lawyer advisor or a professional translator.
- **Arabic conventions:** Modern Standard Arabic (MSA), not Levantine dialect. Arabic punctuation (`،`, `؟`, `؛`), NOT Latin equivalents. Imperative verbs for action buttons. ":name" interpolation placeholders preserve Laravel syntax.
- **Mixed-direction content in documents** — paragraphs inherit their own direction via `dir="auto"` or per-block `dir` attribute from the editor's JSON state.
- **Password fields are always `dir="ltr"`** regardless of page locale (passwords mix directions and right-alignment creates input ambiguity).

---

## 9. Testing & Quality Gates

Every PR must pass:

1. **Pest 4.x test suite green.** All `tests/Unit/`, `tests/Feature/`, and (for affected Surges) `tests/Browser/`. PHPUnit syntax not acceptable.
2. **Pint clean.** `pint --test` returns no diffs.
3. **Larastan level 6 clean.** `phpstan analyse` returns no errors.
4. **OpenAPI spec valid + in sync.** `spectral lint openapi/spec.yaml` clean. Custom CI check: every new public route has a matching spec entry. (Admin panel routes are exempt — admin panel has no public API.)
5. **Workspace isolation tested.** For any new tenant-scoped entity, at least one test verifies cross-workspace data is invisible.
6. **Guard isolation tested.** For any Wave touching auth, at least one test asserts `web` ↔ `admin` ↔ `sanctum` guard isolation.
7. **Audit log secret leakage tested.** `AuditLogPasswordLeakageTest` runs on every CI build asserting no audit row contains password/secret keys.
8. **Locale key parity tested.** `LocaleKeyParityTest` runs on every CI build asserting `ar/*` and `en/*` lang files match in key structure.
9. **No-hardcoded-strings tested.** `NoHardcodedStringsTest` runs per domain folder.
10. **AR locale smoke test.** For any new UI-bearing Flow, at least one Browser test asserts AR-locale rendering with correct `<html dir="rtl">`.
11. **Invariant tests for critical invariants.** Where rule 14 applies (defense-in-depth), the Pest invariant test is mandatory and runs in CI.
12. **Append-only enforcement.** For ledger tables (rule 11), the Filament resource test asserts NO Create/Edit/Delete actions are registered.
13. **Test coverage gate.** ≥95% line coverage on namespaces affected by the Wave. `php artisan test --coverage --min=95` scoped to those namespaces.

**Test data hygiene:**

- Use factories (`<Entity>Factory`) for all test data. Never insert raw rows in tests.
- For shared setup, use Pest's `beforeEach()` per test file; avoid global state.
- Browser tests use real .docx fixtures from `tests/fixtures/docx/` — commit anonymized real-world contracts, not synthetic ones, for round-trip fidelity tests.
- Admin panel browser tests use snapshot baselines committed to `tests/Browser/__snapshots__/admin/`.

---

## 10. What NOT to Do (off-strategy + schema constraints)

### Schema-level constraints — REJECT any task that would add these

The `matters` table must NEVER contain:

- `judge_name`, `judge_id`
- `court_id`, `court_type`, `court`
- `court_case_number`, `case_number`
- `opponent_name`, `opponent_contact_id`, `opponents_lawyer`
- `representation_type`
- `region` (litigation construct — different from `governing_law` on contract_metadata, which IS allowed)

The codebase must NEVER contain:

- Migrations for `hearings`, `court_reviews`, `service_logs`, `judges`, `courts`
- Filament resources for any litigation entity
- Routes under `/litigation`, `/hearings`, `/court-*`, `/service-logs`
- A native accounting module — no `chart_of_accounts`, `journal_entries`, `trust_accounts`, `invoices` (Year-2), `receipts` (Year-2)
- A native CRM module — no `leads`, `pipelines`, `opportunities`, `deals` table (Year-2)
- A native email client — Emails are out (Year-2 might integrate Outlook/Gmail, not build)
- A native calendar module — deadlines live on Obligations (Year-2 might add calendar export)
- KPI / target tracking — out
- A form-templates / global-config engine — out (hardcoded defaults are fine)
- Workflow / automation engine — out
- Mobile app code — web only

### Append-only ledger constraints (SURGE-14)

For `admin_activity_log`, `subscription_events`, `stripe_webhook_events`, `admin_impersonation_sessions`:

- Do NOT add `updated_at` column
- Do NOT use `SoftDeletes`
- Do NOT register Filament Create / Edit / Delete actions in their Resources
- Do NOT add Policy methods for `update` or `delete`
- Do NOT add Artisan commands or one-off scripts that mutate existing rows
- Corrections happen via a new offsetting row (e.g., `admin.activity_log.correction` event referencing the prior row's id in payload)

### Guard / authentication constraints (SURGE-14)

- Do NOT add the `HasApiTokens` trait to `AdminUser`. Admin auth is session-only.
- Do NOT add Sanctum API endpoints under `/admin/*`. Admin panel is browser-only.
- Do NOT create routes that authenticate via both `web` and `admin` guards. Each route uses exactly one guard.
- Do NOT share session cookies between `web` and `admin` guards. The admin cookie is path-scoped to `/admin` with SameSite=Strict.
- Do NOT log Stripe webhook secrets, Stripe API keys, or any password/credential in any payload (audit log, console log, error report, queue job arguments).

### Operational constraints

- Do not call LLM providers directly from the browser. Always proxy through the backend.
- Do not store LLM API keys anywhere except `.env` (read via `config()`).
- Do not log prompt content unless the `AiInteraction` audit row receives it (intentional, for cost + compliance).
- Do not bypass the `BelongsToWorkspace` scope with `withoutGlobalScopes()` unless explicitly in a platform-admin context (which is rare and must be reviewed).
- Do not commit `.env` files, fixture files containing real client data, or API keys.
- Do not auto-translate Arabic strings via Google Translate / DeepL / LLM. Flag for human review.
- Do not allow the admin seeder (`AdminUserSeeder`) to fall back to hard-coded default credentials in production. Production must supply `ADMIN_SEED_EMAIL`, `ADMIN_SEED_NAME`, `ADMIN_SEED_PASSWORD` env vars or the seeder throws.

### Process constraints

- Do not begin work on a Surge whose upstream gates are not green (per SURGE-00 deliverables + prior Surge sign-off). Use the AODC_Software_Engineer agent's Gate Status Report.
- Do not skip Pest tests "just to ship faster." CI will block.
- Do not modify `CLAUDE.md` (this file) without explicit founder direction. CLAUDE.md updates happen at Surge completion, not during Waves.
- Do not modify any file under `decisions/` without an ADR-style superseding entry. ADRs are append-only.
- Do not bypass the AODC pipeline. Tasks without a Tech Task Package are surfaced back to the AODC_Software_Engineer agent.

---

## 11. AODC Pipeline Reference

This project is built via the AODC (Agent-Orchestrated Development Cycle) methodology. Relevant artifacts live in:

| Location | Contents |
|---|---|
| `docs/README.md` | Index + non-negotiables + naming conventions (canonical) |
| `docs/00_MVP_ROADMAP.md` | Master plan — wedge, scope, sequencing, principles |
| `docs/SURGE-NN-*.md` | Per-Surge plans — Flows, dependencies, acceptance |
| `docs/WAVE-NN.N-*.md` | Per-Wave Wave-Ready Packages |
| `docs/validation/` | SURGE-00 deliverables (PRD, AI test report, interviews) + advisor logs |
| `docs/validation/02_advisor_meeting_log.md` | Khaldoun Khater's documented decisions (source of truth for advisor input) |
| `decisions/D-NN.md` | Architecture decision records |
| `prompts/` | AI prompt templates — each requires `[LEGAL-REVIEW]` sign-off in header |
| `spikes/` | Throwaway research — deleted after the corresponding decision |

### Roles in the AODC pipeline

```
Business request  →  Wave-Ready Package  →  Tech Task Package  →  Claude Code execution
   (Founder)         (Product Designer)     (Engineer agent)      (Claude Code)
```

- **Founder (Abdullah)** — Owns product, scope, business decisions. Sole approver of Surge/Wave content.
- **Product Designer agent** — Produces Wave-Ready Packages with structured Intent, Stories, Wireframes, API Contracts, Content Spec, Edge Cases, Sign-Off.
- **Engineer agent** — Consumes Wave-Ready Packages. Produces Tech Task Packages with migrations, models, FormRequests, Policies, Filament Resources, tests.
- **Claude Code** — Reads Tech Task Packages. Writes the actual code in the repository.

**When asked to do work, expect to be given:** a Tech Task Package (TTP) produced by the Engineer agent. The TTP references the relevant Wave-Ready Package in `docs/` and contains everything you need.

**If a task arrives without a TTP** — for example, the AO pastes a free-form request directly — stop and ask: "Should this be routed through the Engineer agent first to produce a Tech Task Package?" Ad-hoc tasks bypass the gates that exist to prevent rework.

### Advisor precedence

When practitioner input (from Khaldoun Khater, lawyer advisor) conflicts with prior architectural assumptions, **advisor input takes precedence** for legal-domain questions. The advisor meeting log at `docs/validation/02_advisor_meeting_log.md` is the source of truth for these decisions. Examples already lifted to architectural rules:

- Appeal windows are court-level-dependent (10 days for Magistrate Courts, 30 days for First Instance Courts). Malpractice exposure if conflated.
- Hearing session content is gated by `status = 'held'`. Sessions in other states do not expose content.
- Trust accounting trial-ledger adjustments require explicit description (Jordanian Lawyers Act).
- ExpertReport entity has an 8-day objection countdown.
- Western numerals (0–9) in Arabic UI, not Eastern (٠–٩).

---

## 12. When in doubt

Order of precedence for resolving uncertainty:

1. **This file (`CLAUDE.md`).** If something here contradicts other documents, this wins, but flag the contradiction for the Founder.
2. **`docs/00_MVP_ROADMAP.md`.** The strategic source of truth.
3. **The specific Surge plan** for the work in question (`docs/SURGE-NN-*.md`).
4. **The Wave-Ready Package** for the Wave in question (`docs/WAVE-NN.N-*.md`).
5. **The Tech Task Package** for the Flow in question.
6. **The advisor meeting log** (`docs/validation/02_advisor_meeting_log.md`) for legal-domain questions.
7. **The Founder** (via the AO). If 1–6 do not resolve it, escalate.

Do not invent answers. Do not guess at intent. Stop, surface the question, wait for direction.

---

## Changelog

### v7 — 2026-06-24

**Added:**
- Section 4 rules 11–18 (cursor pagination, append-only ledgers, platform-vs-tenant scope split, defense-in-depth invariants, no-secrets-in-audit, guard isolation, locale key parity, Stripe webhook idempotency)
- Section 7 platform-level entity glossary (AdminUser, AdminActivityLog, Plan, Subscription, SubscriptionEvent, StripeWebhookEvent, AdminImpersonationSession) + subscription state machine diagram
- Section 8 hardcoded-string prohibition, key parity enforcement, Arabic conventions (MSA, punctuation, Western numerals, password LTR)
- Section 9 testing gates 6–13 (guard isolation, audit secret leakage, locale parity, no-hardcoded-strings, invariant tests, append-only enforcement, coverage gate)
- Section 10 append-only ledger constraints, guard/auth constraints, admin seeder safety
- Section 11 AODC pipeline roles + advisor precedence
- Section 6 platform-level model naming, admin Filament namespace, webhook controller convention

**Modified:**
- Section 2: Laravel 11.x → 13.x, Filament v3.x → v5.x, Pest 2.x → 4.x, Billing: Cashier → direct Stripe SDK
- Section 4 rule 3 (soft deletes): scoped to tenant-scoped entities only; ledgers and platform-level entities exempt
- Section 5 folder structure: split Filament into Admin/App panels; added `config/admin.php`, `tests/Feature/Admin/`, `tests/Browser/__snapshots__/admin/`; documented `routes/web.php` Stripe webhook route
- Section 5: `planning/` → `docs/` (canonical)
- Section 11: AODC pipeline reference updated to reflect new path

**Unchanged from v6:**
- Product mission, wedge, MVP audience
- Tenant-scoped architectural non-negotiables 1–10
- Off-strategy schema constraints (no litigation, no native accounting, etc.)
- Document editing client-side principle
- Workspace scoping for tenant entities

---

*End of CLAUDE.md v7. Welcome to the project.*
