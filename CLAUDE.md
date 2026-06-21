# CLAUDE.md — Project Memory for Claude Code

> This file is loaded by Claude Code on every session against this repository.
> Read it first. It encodes everything you need to know before touching the codebase.
> **Last meaningful update: 2026-06-21** (post-SURGE-01 stack reconciliation + Filament-everywhere pivot)

---

## Update log

- **2026-06-17** — Initial draft, pre-build.
- **2026-06-21** — Reconciled with actual installed stack after SURGE-01 F-01.1–F-01.5. Laravel 11 → 13, Filament v3 → v5, Pest 2 → 4, UUIDs → ULIDs, Tailwind v4 logical properties. Added Filament-everywhere architectural pivot.

---

## 1. Project

A **bilingual (Arabic/English), AI-native commercial-contracts workspace** for small Levant law firms (2–10 lawyers). The product makes the contract document itself the workspace — editable, versioned, clause-aware, with AI inline — and explicitly avoids the litigation, accounting, and CRM breadth of competitors (HAQQ.ai, Clio).

**Single hardest test we must pass:** a lawyer imports a real `.docx` contract, edits clauses (mixed AR/EN, RTL/LTR), exports back to `.docx`, and opens the file in Microsoft Word with formatting intact. If round-trip fidelity breaks, the wedge breaks.

**MVP audience:** small commercial-law firms in Jordan, Lebanon, Palestine, Iraq.
**Wedge (PROVISIONAL until Arabic AI test against HAQQ completes):** either Arabic-legal depth or integrated single-surface UX. Build is wedge-agnostic at the structural level.

**Operating mode (under `validation/00_FOUNDER_WAIVER.md`):** SURGE-00 gates are deferred. Build proceeds with founder-decided placeholders for legal-domain enums (marked `[PROVISIONAL-FOUNDER-DECIDED]`). Lawyer/advisor signoff is a hard stop before paid launch — not before build.

---

## 2. Tech Stack (actual installed versions)

| Layer | Value | Notes |
|---|---|---|
| Backend framework | **Laravel 13.16.x** | (Upgraded from planned v11 — security advisories) |
| Admin/Customer panel | **Filament v5.6.x** | (Upgraded from planned v3 — v3 incompatible with v13) |
| Backend language | PHP 8.3 (CI pin) / PHP 8.5 (local OK) | Production targets 8.3 |
| Frontend (Filament-driven UI) | Filament's Livewire 3 + Tailwind v4 stack | RTL via Tailwind v4 **logical properties** — no RTL plugin |
| Frontend (document editor only) | Custom Livewire 3 + Blade + TipTap/ProseMirror | Per SURGE-03 F-03.1 spike output |
| Database | MySQL 8.x InnoDB | UTF8MB4 charset throughout |
| ORM | Eloquent (Laravel 13) | Soft deletes everywhere; ULID primary keys |
| Primary key strategy | **ULID** (via `HasUlids` trait) | (Upgraded from planned UUID — ordering benefits) |
| Cache / queues | Redis | Cloudways-managed |
| Object storage | S3-compatible | For `.docx` blobs, document exports |
| Cloud | Cloudways | Region: DigitalOcean Frankfurt FRA1 (per founder decision under waiver) |
| CI/CD | GitHub Actions | `.github/workflows/ci.yml` |
| Auth (web) | Google OAuth via Socialite v5 | Session cookies, 7-day lifetime |
| Auth (API) | Laravel Sanctum | Bearer tokens (added in F-01.6) |
| API style | REST + OpenAPI 3.0 | Source of truth: `openapi/spec.yaml` |
| LLM provider | Anthropic Claude (default) or per `decisions/D-03.md` | Abstracted behind `LlmProvider` interface |
| Billing | Stripe via Laravel Cashier | Per `decisions/D-06.md` |
| Tests | **Pest 4.7.x** | `tests/Unit/`, `tests/Feature/`, `tests/Browser/` |
| E2E tests | Pest Browser Plugin (Playwright) | For editor + .docx round-trip especially |
| Static analysis | **Larastan 3.10 / PHPStan 2.2** at level 6 | Set `phpVersion: 80300` in `phpstan.neon` (PHPStan silent on PHP 8.5 — known issue) |
| Code style | Laravel Pint | `pint.json` — laravel preset + ordered imports |
| Localization (framework strings AR) | **`laravel-lang/common`** | Comprehensive community-maintained AR translations |
| Localization (project strings) | `resources/lang/{ar,en}/*.php` | Domain files: `common`, `auth`, `dashboard`, `workspace`, `roles`, `locale`, `contacts`, `matters`, `documents`, etc. |
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
./vendor/bin/pest                                # unit + feature
./vendor/bin/pest --filter=Document              # subset
./vendor/bin/pest tests/Browser                  # E2E (slow)
./vendor/bin/pest --parallel                     # faster locally

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

# Filament v5 (note: v5 commands; v3 syntax does NOT work)
php artisan make:filament-resource <Entity>      # creates Resource + Pages
php artisan filament:upgrade
php artisan filament:install --panels            # if panel registration needs refresh

# Seeders
php artisan db:seed
php artisan db:seed --class=DemoWorkspaceSeeder

# OpenAPI
./vendor/bin/spectral lint openapi/spec.yaml

# Queue
php artisan queue:listen --queue=default,contracts,exports
```

**Local services required:**

- MySQL 8.x on `localhost:3306`
- Redis on `localhost:6379`
- (Optional) MinIO for S3-compatible storage on `localhost:9000`

---

## 4. Architectural Non-Negotiables

These are constraints, not preferences. **Do not violate them.** If a task seems to require violating one, stop and surface the conflict.

1. **Workspace scoping is mandatory.** Every tenant-scoped model uses the `BelongsToWorkspace` trait (lives in `app/Concerns/`). Every query is automatically scoped via a global Eloquent scope. Cross-workspace data is a P0 bug class.
2. **Policy + FormRequest on every write endpoint AND every Filament resource.** No exceptions. No inline validation. No `if ($user->role === ...)` checks in controllers or Filament pages. Policy method enforces; Filament respects policy methods by default in v5.
3. **Soft deletes everywhere.** Every tenant-scoped model uses `SoftDeletes`. Hard delete only via explicit admin tooling.
4. **Audit columns everywhere.** `created_at`, `updated_at`, `created_by_user_id`, `updated_by_user_id` on every tenant entity.
5. **Optimistic locking on concurrent edits.** Compare `updated_at` on update; reject 409 if mismatch.
6. **OpenAPI spec sync.** Every API endpoint added/changed updates `openapi/spec.yaml` in the same PR. CI enforces via `spectral lint`.
7. **Bilingual via Laravel localization only.** Never hardcode user-facing strings. Always `__('domain.key')` or `trans('domain.key')`. Filament resource labels go through `static::getNavigationLabel()` / `static::getModelLabel()` and resolve `__()` keys.
8. **No N+1.** Lists use eager loading. Larastan rule enforces.
9. **No raw SQL** in app code unless wrapped in a Repository with a corresponding integration test.
10. **Document editing is client-side** (SURGE-03 only). Server stores editor JSON state + `.docx` blob; the TipTap/ProseMirror editor runs in the browser inside a custom Livewire+Blade view. AI calls go through the backend, never browser → LLM provider directly.
11. **Filament is the primary UI for ALL roles.** See §4a below.
12. **ULIDs as primary keys.** Use `HasUlids` trait on every tenant-scoped model. Foreign-key columns are `string(26)`. Never `bigIncrements`.

### §4a. Filament-everywhere — the UI architecture

**Decision (2026-06-21, supersedes the SURGE-01 "Filament Owner+Admin only" decision):**

- **Filament v5 is the primary UI for all authenticated users**, regardless of role (Owner / Admin / Member). The `/admin/{workspace_slug}` path serves the application UI for every role.
- **Granular access is enforced via Policy methods on each Resource**, NOT via panel-level gating. Members see fewer actions on each resource because the policy denies them, not because the panel denies access.
- **`canAccessPanel()` returns true for any workspace member.** (Replaces the prior Owner+Admin restriction.)
- **The ONLY exception** is the SURGE-03 **document editor surface**, which is custom Livewire+Blade because TipTap/ProseMirror cannot live inside Filament's form schema. The editor route pattern is `/matters/{matter_id}/documents/{document_id}`, served outside the Filament panel but with shared session + workspace context.
- **Potential future exceptions** (decide when arrived): Stripe Checkout redirect flow (SURGE-06 F-06.2), public legal-doc acceptance pages (SURGE-06 F-06.4), document share token endpoints (SURGE-03 F-03.7). These are routes outside the Filament panel by necessity.

**Why:** Filament v5 multi-tenancy (already wired via `->tenant(Workspace::class)`), built-in form validation, table filters, bulk actions, RTL-correct rendering, locale-aware labels — gives us the majority of the customer-facing UI surface for free. Building parallel Blade pages would be wasted velocity on an MVP. Pivot to Blade only when the surface (document editor) genuinely cannot be expressed in Filament's schema.

**Implication for existing code from SURGE-01:**
- `dashboard.blade.php` becomes a Filament page (or remains as a thin redirect to the panel) — TBD in F-01.6 cleanup.
- `auth/login.blade.php` stays Blade (must be accessible pre-authentication).
- `welcome.blade.php` stays (Laravel default; unused).
- Filament's `canAccessPanel()` to be updated from `Role::canAccessFilament()` → `true` for any member of the current workspace.

**Implication for SURGE-02 onward:**
- Every entity (Contact, Matter, Counterparty, Clause Library, Obligation) is a Filament v5 resource.
- Skip Figma for all of these. Filament's design system IS the design system.
- Custom Livewire+Blade only for the document editor in SURGE-03.

---

## 5. Folder Structure

```
app/
  Concerns/                  Shared model traits (e.g., BelongsToWorkspace)
  Enums/                     PHP 8.1+ enums (Role, MatterStatus, etc.)
  Filament/
    Resources/               Filament v5 resources — <Entity>Resource (primary UI for everyone)
    Pages/                   Filament pages (dashboards, custom views inside panel)
    Widgets/                 Filament dashboard widgets (used in SURGE-05 F-05.5)
  Http/
    Controllers/
      Api/V1/                Versioned API controllers (Sanctum-authed)
      Web/                   Server-rendered web controllers (non-Filament routes only — auth, document editor, share tokens)
    Requests/                FormRequest classes — Store<X>Request, Update<X>Request
    Resources/               API resources (transformers)
    Middleware/              Custom middleware (SetLocale, EnsureWorkspaceSelected)
  Livewire/                  Custom Livewire components (document editor, share modal, AI panel)
  Models/                    Eloquent models — singular PascalCase, HasUlids
  Services/                  Domain services (DocumentService, AiOrchestrationService)
  Policies/                  Authorization policies — <Entity>Policy (Filament respects these)
  Jobs/                      Queue jobs — verb-shaped names
  Mail/                      Mailable classes (bilingual Markdown mailables)
  Console/
    Kernel.php               Scheduled tasks (renewal reminders, obligation overdue scan)
  Providers/                 Service providers

database/
  migrations/                Timestamped migrations
  seeders/                   Seeders
  factories/                 Model factories (for tests + seeds)

resources/
  views/
    auth/                    Login (Blade — pre-auth)
    documents/               Document editor surface (custom Livewire+Blade — SURGE-03)
    filament/                Filament render hooks (locale switcher, etc.)
  lang/ar/                   Arabic translations
  lang/en/                   English translations
  css/, js/                  Frontend assets (Vite-bundled)

routes/
  web.php                    Web routes (session auth)
  api.php                    API routes (Sanctum auth)
  console.php                Artisan commands

tests/
  Unit/                      Pure unit tests
  Feature/                   HTTP + Filament integration tests
    Api/V1/                  Mirror controller structure
    Filament/                Filament resource tests
    Models/                  Eloquent model tests
    Policies/                Policy tests (per-role + per-method)
    Services/                Service tests
    Concerns/                Trait tests (BelongsToWorkspace, etc.)
    Locale/                  Locale-switching and AR/RTL rendering tests
    Middleware/              Middleware tests
  Browser/                   E2E via Pest Browser Plugin
  fixtures/                  Test files (.docx samples, etc.)

openapi/
  spec.yaml                  REST API specification — single source of truth

decisions/
  D-01.md … D-NN.md          Architecture decision records

prompts/                     AI prompt templates (must have legal-review header)
spikes/                      Throwaway research code (deleted post-decision)
validation/                  SURGE-00 deliverables + FOUNDER_WAIVER
planning/                    Surge/Flow .md files (read-only references)
```

---

## 6. Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| Primary key | **ULID** via `HasUlids` trait | `protected $keyType = 'string';` — Laravel auto-detects |
| Foreign key column | `string(26)`, snake_case ending in `_id` or `_user_id` | `workspace_id`, `created_by_user_id` |
| Eloquent model | Singular PascalCase | `Contact`, `Matter`, `Counterparty` |
| Table | Plural snake_case | `contacts`, `matters`, `counterparties` |
| Pivot/junction with model | Singular PascalCase (when modeled separately) | `WorkspaceMember` for `workspace_members` |
| Migration | Verb-shaped, timestamped | `2026_06_21_140000_create_contacts_table` |
| Filament resource | `<Entity>Resource` | `app/Filament/Resources/ContactResource.php` |
| Policy | `<Entity>Policy` | `ContactPolicy` |
| FormRequest (store) | `Store<Entity>Request` | `StoreContactRequest` |
| FormRequest (update) | `Update<Entity>Request` | `UpdateContactRequest` |
| API controller | `Api\V1\<Entity>Controller` | `app/Http/Controllers/Api/V1/ContactController.php` |
| Web controller (non-Filament) | `Web\<Entity>Controller` | `app/Http/Controllers/Web/DocumentEditorController.php` |
| Livewire component | `App\Livewire\<Domain>\<Component>` | `App\Livewire\Documents\Editor` |
| Service | `<Entity\|Domain>Service` | `DocumentService`, `AiOrchestrationService` |
| Job | Verb-shaped, `Job` suffix | `ImportDocumentJob`, `SendRenewalReminderJob` |
| Test (API feature) | `tests/Feature/Api/V1/<Entity>ApiTest.php` | — |
| Test (Filament) | `tests/Feature/Filament/<Entity>ResourceTest.php` | — |
| Test (unit service) | `tests/Unit/Services/<Entity\|Domain>ServiceTest.php` | — |
| Localization domain | snake_case filename | `resources/lang/ar/matters.php` |
| Route name | dotted snake_case | `matters.show`, `documents.export` |

---

## 7. Domain Glossary

The product's bounded context. When in doubt about a noun, this is the canonical meaning.

- **Workspace** — A tenant. Every other entity belongs to exactly one workspace. Users can belong to multiple workspaces. Filament v5 tenant scope is wired here.
- **WorkspaceMember** — A user's role within a specific workspace (Owner / Admin / Member). Modeled as a separate Eloquent model (not just a pivot table) so it can carry its own policies.
- **Role** — Owner, Admin, Member. Three values. No more at MVP. Enum at `app/Enums/Role.php`.
- **Contact** — A person or organization. Polymorphic via a `type` enum. Can be flagged as Client and/or Counterparty.
- **Client** — A Contact with `is_client = true`. Represented by us.
- **Counterparty** — A Contact with `is_counterparty = true`. The other side of a deal. Becomes first-class on Matter (with role + position) in SURGE-05.
- **Matter** — A piece of legal work for a Client. Commercial scope only. **Has NO court fields.** Title, Client, Counterparty(s), Status, Stage, Practice Area, Notes.
- **Document** — A working artifact tied to a Matter. Most commonly a contract. Has versions, clauses, optional contract metadata.
- **DocumentVersion** — A snapshot of a Document at a point in time. Immutable once created. Every save creates a new version.
- **DocumentClause** — A structural addressable unit within a DocumentVersion. Extracted on save by `ClauseExtractionService`.
- **ContractMetadata** — One-to-one with Document where `document_type = 'contract'`. Carries value, currency, dates, term, governing law.
- **Obligation** — A dated commitment derived from a contract. Has type, responsible party, due date, status. Drives reminder emails.
- **LibraryClause** — A reusable clause in the workspace's Clause Library. May have AR + EN paired bodies. May be marked as a fallback of another clause (the playbook).
- **AiInteraction** — An audit row for every AI call. Carries prompt, response, model, tokens, cost, accepted/rejected flag.
- **Subscription** — Stripe-managed (via Cashier). Workspace-level, per-seat pricing.

**Words NOT in our domain (off-strategy):** Case (we say Matter), Hearing, Court, Judge, Opponent, Pleading, Discovery, Service of Process, Invoice (Year-2), Trust Account (never), KPI (Year-2), Lead (Year-2), Email-as-an-entity (Year-2).

---

## 8. Localization Rules

- **Default locale is `ar`.** Anonymous visitors see Arabic unless they override via `?lang=en`.
- **Every user-facing string lives in `resources/lang/{ar,en}/<domain>.php`.** No exceptions.
- **Framework strings (validation, auth, pagination, passwords, actions, HTTP statuses) come from `laravel-lang/common`** — already installed. Do NOT duplicate these in custom files.
- **Filament strings** for AR come from `laravel-lang/common`'s `ar.json` (~230 keys). Filament v5 picks these up automatically when locale is `ar`.
- **`<html dir>` and `<html lang>`** are set by `SetLocale` middleware based on resolved locale (5-step resolution chain — see existing middleware).
- **Tailwind v4 logical properties** handle RTL automatically (`ms-4` not `ml-4`; `pe-2` not `pr-2`). No `tailwindcss-rtl` plugin needed. Direction-specific styling uses `ltr:` / `rtl:` variants or `[dir="rtl"]` selectors.
- **Dates** — Gregorian only at MVP. Hijri is a Year-2 backlog item.
- **Numbers** — Latin digits (1234) in both locales for now.
- **Currency** — Display ISO code + amount (e.g., `USD 50,000.00`).
- **Plurals** — Use Laravel's `trans_choice()` and `:count` placeholders. Arabic has 6 plural forms; account for all where the message is plural-sensitive.
- **AR translations are NEVER auto-generated.** Either the Product Designer's Content Spec provides them, or a key is flagged `[NEEDS-AR-TRANSLATION-REVIEW]` and routed to the lawyer advisor or a professional translator.
- **Mixed-direction content in documents** — paragraphs inherit their own direction via `dir="auto"` or per-block `dir` attribute from the editor's JSON state.

---

## 9. Testing & Quality Gates

Every PR must pass:

1. **Pest test suite green.** All `tests/Unit/`, `tests/Feature/`, and (for affected Surges) `tests/Browser/`.
2. **Pint clean.** `pint --test` returns no diffs.
3. **Larastan level 6 clean.** `phpstan analyse` returns no errors **on CI** (PHP 8.3). Local PHP 8.5 produces no output — known PHPStan issue, ignore locally.
4. **OpenAPI spec valid + in sync.** `spectral lint openapi/spec.yaml` clean. Every new route has a matching spec entry.
5. **Workspace isolation tested.** For any new tenant-scoped entity, at least one test verifies cross-workspace data is invisible.
6. **AR locale smoke test.** For any new UI-bearing Flow, at least one test asserts AR-locale rendering with correct `<html dir="rtl">`.
7. **Filament resource tests.** Any new Filament resource gets a `<Entity>ResourceTest.php` in `tests/Feature/Filament/` covering: list page renders, create works, edit works, role-based access enforced via policy, AR-locale label rendering.

**Test data hygiene:**

- Use factories (`<Entity>Factory`) for all test data. Never insert raw rows in tests.
- For shared setup, use Pest's `beforeEach()` per test file; avoid global state.
- Browser tests use real `.docx` fixtures from `tests/fixtures/docx/` — commit anonymized real-world contracts for SURGE-03 round-trip fidelity tests.

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
- A native email client — Year-2 might integrate Outlook/Gmail, not build
- A native calendar module — deadlines live on Obligations (Year-2 might add calendar export)
- KPI / target tracking — out
- A form-templates / global-config engine — out (hardcoded defaults are fine)
- Workflow / automation engine — out
- Mobile app code — web only

### UI architecture constraints

- Do not build parallel Blade pages for entities that have Filament resources. Filament is the UI. Custom Blade is only for: pre-auth pages (login), the document editor (SURGE-03), Stripe redirect flows (SURGE-06), public share tokens (SURGE-03), public legal-doc pages (SURGE-06).
- Do not block Filament panel access by role. Use policies for granular method-level access. `canAccessPanel()` returns true for any workspace member.

### Operational constraints

- Do not call LLM providers directly from the browser. Always proxy through the backend.
- Do not store LLM API keys anywhere except `.env` (read via `config()`).
- Do not log prompt content unless the `AiInteraction` audit row receives it (intentional, for cost + compliance).
- Do not bypass the `BelongsToWorkspace` scope with `withoutGlobalScopes()` unless explicitly in a system-admin context (which is rare and must be reviewed).
- Do not commit `.env` files, fixture files containing real client data, or API keys.
- Do not auto-translate Arabic strings via Google Translate / DeepL / LLM. Flag for human review.

### Process constraints

- Do not begin work on a Surge whose upstream gates are not green per `validation/00_FOUNDER_WAIVER.md`. Use the AODC_Software_Engineer agent's Gate Status Report.
- Do not skip Pest tests "just to ship faster." CI will block.
- Do not modify `CLAUDE.md` (this file) without explicit founder direction. Updates are append-only via the §Update log at top.
- Do not modify any file under `decisions/` without an ADR-style superseding entry. ADRs are append-only.

---

## 11. AODC Pipeline Reference

This project is built via the AODC (Agent-Orchestrated Development Cycle) methodology. Relevant artifacts live in:

| Location | Contents |
|---|---|
| `planning/00_MVP_ROADMAP.md` | Master plan — wedge, scope, sequencing, principles |
| `planning/README.md` | Index + non-negotiables + naming conventions (cross-referenced here) |
| `planning/SURGE-NN-*.md` | Per-Surge plans — Flows, dependencies, acceptance |
| `validation/00_FOUNDER_WAIVER.md` | **Active operating waiver** — defines which SURGE-00 gates are deferred and which hard stops remain |
| `validation/` | SURGE-00 deliverables (PRD, AI test report, interviews) |
| `decisions/D-NN.md` | Architecture decision records |
| `prompts/` | AI prompt templates — each requires `[LEGAL-REVIEW]` sign-off in header |
| `spikes/` | Throwaway research — deleted after the corresponding decision |

**When asked to do work, expect to be given:** a Tech Task Package (TTP) produced by the AODC_Software_Engineer agent. The TTP references the relevant Surge/Flow in `planning/` and contains everything you need.

**If a task arrives without a TTP** — for example, the AO pastes a free-form request directly — stop and ask: "Should this be routed through the AODC_Software_Engineer agent first to produce a Tech Task Package?" Ad-hoc tasks bypass the gates that exist to prevent rework.

---

## 12. When in doubt

Order of precedence for resolving uncertainty:

1. **This file (`CLAUDE.md`).** If something here contradicts other documents, this wins, but flag the contradiction for the Founder.
2. **`validation/00_FOUNDER_WAIVER.md`.** Defines current operating mode (which gates are open/closed).
3. **`planning/00_MVP_ROADMAP.md`.** The strategic source of truth.
4. **The specific Surge plan** for the work in question.
5. **The Tech Task Package** for the Flow in question.
6. **The Founder** (via the AO). If 1–5 do not resolve it, escalate.

Do not invent answers. Do not guess at intent. Stop, surface the question, wait for direction.

---

*End of CLAUDE.md. Welcome to the project.*
