# CLAUDE.md — Project Memory for Claude Code

> This file is loaded by Claude Code on every session against this repository.
> Read it first. It encodes everything you need to know before touching the codebase.
> Last meaningful update: 2026-06-17.

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
| Backend framework | Laravel 11.x | Single monolith for MVP |
| Admin panel | Filament v3.x | Founder/Owner/Admin only |
| Backend language | PHP 8.3 | Use modern syntax (readonly, enums, etc.) |
| Frontend (web) | Blade + Tailwind + Livewire 3 | No SPA. Server-rendered first. |
| Editor (in-document) | TipTap or CKEditor (decided in `decisions/D-02.md`) | Lives client-side, called via Livewire |
| Database | MySQL 8.x InnoDB | UTF8MB4 charset throughout |
| ORM | Eloquent | Soft deletes everywhere |
| Cache / queues | Redis | Cloudways managed |
| Object storage | S3-compatible | For .docx blobs, document exports |
| Cloud | Cloudways | Region per `decisions/D-01.md` |
| CI/CD | GitHub Actions | `.github/workflows/` |
| Auth (web) | Google OAuth via Socialite | Session cookies |
| Auth (API) | Laravel Sanctum | Bearer tokens |
| API style | REST + OpenAPI 3.0 | Source of truth: `openapi/spec.yaml` |
| LLM provider | Anthropic Claude (default) or per `decisions/D-03.md` | Abstracted behind `LlmProvider` interface |
| Billing | Stripe via Laravel Cashier | Per `decisions/D-06.md` |
| Tests | Pest 2.x | `tests/Unit/`, `tests/Feature/`, `tests/Browser/` |
| E2E tests | Pest Browser Plugin (Playwright) | For editor + .docx round-trip especially |
| Static analysis | Larastan level 6 | `phpstan.neon` |
| Code style | Laravel Pint | `pint.json` |
| Localization | Laravel localization | `resources/lang/{ar,en}/*.php` |
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
./vendor/bin/pest                       # unit + feature
./vendor/bin/pest --filter=Document     # subset
./vendor/bin/pest tests/Browser         # E2E (slow)

# Code quality
./vendor/bin/pint                       # auto-format
./vendor/bin/pint --test                # check only (CI)
./vendor/bin/phpstan analyse             # static analysis (level 6)

# Migrations
php artisan make:migration create_<table>_table --create=<table>
php artisan make:migration add_<col>_to_<table>_table --table=<table>
php artisan migrate
php artisan migrate:rollback --step=1
php artisan migrate:fresh --seed         # local only — never in shared envs

# Filament
php artisan make:filament-resource <Entity>
php artisan filament:upgrade

# Seeders
php artisan db:seed
php artisan db:seed --class=DemoWorkspaceSeeder

# OpenAPI
./vendor/bin/spectral lint openapi/spec.yaml

# Queue
php artisan queue:listen --queue=default,contracts,exports
php artisan horizon                      # if Horizon installed (TBD)
```

**Local services required:**

- MySQL 8.x on `localhost:3306`
- Redis on `localhost:6379`
- (Optional) MinIO for S3-compatible storage on `localhost:9000`

---

## 4. Architectural Non-Negotiables

These are constraints, not preferences. **Do not violate them.** If a task seems to require violating one, stop and surface the conflict.

1. **Workspace scoping is mandatory.** Every tenant-scoped model uses the `BelongsToWorkspace` trait. Every query is automatically scoped via a global Eloquent scope. Cross-workspace data is a P0 bug class.
2. **Policy + FormRequest on every write endpoint.** No exceptions. No inline validation. No `if ($user->role === ...)` checks in controllers.
3. **Soft deletes everywhere.** Every tenant-scoped model uses `SoftDeletes`. Hard delete only via explicit admin tooling.
4. **Audit columns everywhere.** `created_at`, `updated_at`, `created_by_id`, `updated_by_id` on every tenant entity.
5. **Optimistic locking on concurrent edits.** Compare `updated_at` on update; reject 409 if mismatch.
6. **OpenAPI spec sync.** Every API endpoint added/changed updates `openapi/spec.yaml` in the same PR. CI enforces this.
7. **Bilingual via Laravel localization only.** Never hardcode user-facing strings. Always `__('domain.key')`.
8. **No N+1.** Lists use eager loading. Larastan rule enforces (`larastan/larastan` baseline).
9. **No raw SQL in app code** unless wrapped in a Repository with a corresponding integration test.
10. **Document editing is client-side.** Server stores editor JSON state + .docx blob; the editor library runs in the browser. AI calls go through the backend, never browser → LLM provider directly.

---

## 5. Folder Structure

```
app/
  Models/                    Eloquent models — singular PascalCase
  Concerns/                  Shared model traits (e.g., BelongsToWorkspace)
  Enums/                     PHP 8.1+ enums (Role, MatterStatus, etc.)
  Http/
    Controllers/
      Api/V1/                Versioned API controllers
      Web/                   Server-rendered web controllers
    Requests/                FormRequest classes — Store<X>Request, Update<X>Request
    Resources/               API resources (transformers)
    Middleware/              Custom middleware (e.g., SetLocale)
  Filament/
    Resources/               Filament resources — <Entity>Resource
  Services/                  Domain services (e.g., DocumentService)
  Policies/                  Authorization policies — <Entity>Policy
  Jobs/                      Queue jobs — verb-shaped names
  Mail/                      Mailable classes
  Console/
    Kernel.php               Scheduled tasks
  Providers/                 Service providers

database/
  migrations/                Timestamped migrations
  seeders/                   Seeders
  factories/                 Model factories (for tests + seeds)

resources/
  views/                     Blade templates
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
  Browser/                   E2E via Pest Browser Plugin
  fixtures/                  Test files (.docx samples, etc.)

openapi/
  spec.yaml                  REST API specification — single source of truth

decisions/
  D-01.md … D-08.md          Architecture decision records

prompts/                     AI prompt templates (must have legal-review header)
spikes/                      Throwaway research code (deleted post-decision)
validation/                  SURGE-00 deliverables (PRD, interviews, AI test, etc.)
planning/                    Surge/Flow .md files (read-only references)
```

---

## 6. Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| Eloquent model | Singular PascalCase | `Contract`, `Matter`, `Counterparty` |
| Table | Plural snake_case | `contracts`, `matters`, `counterparties` |
| Pivot table | Alphabetical concat | `matter_counterparties` |
| Migration | Verb-shaped, timestamped | `2026_06_17_create_contracts_table` |
| Filament resource | `<Entity>Resource` | `ContractResource` |
| Policy | `<Entity>Policy` | `ContractPolicy` |
| FormRequest (store) | `Store<Entity>Request` | `StoreContractRequest` |
| FormRequest (update) | `Update<Entity>Request` | `UpdateContractRequest` |
| API controller | `Api\V1\<Entity>Controller` | `app/Http/Controllers/Api/V1/ContractController.php` |
| Web controller | `Web\<Entity>Controller` | `app/Http/Controllers/Web/ContractController.php` |
| Service | `<Entity|Domain>Service` | `DocumentService`, `AiOrchestrationService` |
| Job | Verb-shaped, `Job` suffix | `ImportDocumentJob`, `SendRenewalReminderJob` |
| Test (API feature) | `tests/Feature/Api/V1/<Entity>ApiTest.php` | — |
| Test (Filament) | `tests/Feature/Filament/<Entity>ResourceTest.php` | — |
| Test (unit service) | `tests/Unit/Services/<Entity|Domain>ServiceTest.php` | — |
| Localization domain | snake_case filename | `resources/lang/ar/matters.php` |
| Route name | dotted snake_case | `matters.show`, `documents.export` |

---

## 7. Domain Glossary

The product's bounded context. When in doubt about a noun, this is the canonical meaning.

- **Workspace** — A tenant. Every other entity belongs to exactly one workspace. Users can belong to multiple workspaces.
- **WorkspaceMember** — A user's role within a specific workspace (Owner / Admin / Member). Stored in the pivot.
- **Role** — Owner, Admin, Member. Three values. No more at MVP.
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
- **Subscription** — Stripe-managed (via Cashier). Workspace-level, per-seat pricing.

**Words NOT in our domain (off-strategy):** Case (we say Matter), Hearing, Court, Judge, Opponent, Pleading, Discovery, Service of Process, Invoice (Year-2), Trust Account (never), KPI (Year-2), Lead (Year-2), Email-as-an-entity (Year-2).

---

## 8. Localization Rules

- **Default locale is `ar`.** Anonymous visitors see Arabic unless they override via `?lang=en`.
- **Every user-facing string lives in `resources/lang/{ar,en}/<domain>.php`.** No exceptions.
- **`<html dir>` and `<html lang>` are set by `SetLocale` middleware** based on resolved locale. RTL/LTR is automatic.
- **Tailwind RTL utilities** are enabled via the official plugin. Use `ltr:` and `rtl:` variants when direction-specific styling is needed.
- **Dates** — Gregorian only at MVP. Hijri is a Year-2 backlog item.
- **Numbers** — Use Latin digits (1234) in both locales for now. Eastern Arabic numerals (١٢٣٤) is a Year-2 backlog item.
- **Currency** — Display ISO code + amount (e.g., `USD 50,000.00`). User-locale formatting for decimal separator.
- **Plurals** — Use Laravel's `trans_choice()` and `:count` placeholders. Arabic has 6 plural forms; account for all where the message is plural-sensitive.
- **AR translations are NEVER auto-generated.** Either the Product Designer's Content Spec provides them, or a key is flagged `[NEEDS-AR-TRANSLATION-REVIEW]` and routed to the lawyer advisor or a professional translator.
- **Mixed-direction content in documents** — paragraphs inherit their own direction via `dir="auto"` or per-block `dir` attribute from the editor's JSON state.

---

## 9. Testing & Quality Gates

Every PR must pass:

1. **Pest test suite green.** All `tests/Unit/`, `tests/Feature/`, and (for affected Surges) `tests/Browser/`.
2. **Pint clean.** `pint --test` returns no diffs.
3. **Larastan level 6 clean.** `phpstan analyse` returns no errors.
4. **OpenAPI spec valid + in sync.** `spectral lint openapi/spec.yaml` clean. Custom CI check: every new route has a matching spec entry.
5. **Workspace isolation tested.** For any new tenant-scoped entity, at least one test verifies cross-workspace data is invisible.
6. **AR locale smoke test.** For any new UI-bearing Flow, at least one Browser test asserts AR-locale rendering with correct `<html dir="rtl">`.

**Test data hygiene:**

- Use factories (`<Entity>Factory`) for all test data. Never insert raw rows in tests.
- For shared setup, use Pest's `beforeEach()` per test file; avoid global state.
- Browser tests use real .docx fixtures from `tests/fixtures/docx/` — commit anonymized real-world contracts, not synthetic ones, for round-trip fidelity tests.

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

### Operational constraints

- Do not call LLM providers directly from the browser. Always proxy through the backend.
- Do not store LLM API keys anywhere except `.env` (read via `config()`).
- Do not log prompt content unless the `AiInteraction` audit row receives it (intentional, for cost + compliance).
- Do not bypass the `BelongsToWorkspace` scope with `withoutGlobalScopes()` unless explicitly in a system-admin context (which is rare and must be reviewed).
- Do not commit `.env` files, fixture files containing real client data, or API keys.
- Do not auto-translate Arabic strings via Google Translate / DeepL / LLM. Flag for human review.

### Process constraints

- Do not begin work on a Surge whose upstream gates are not green (per SURGE-00 deliverables + prior Surge sign-off). Use the AODC_Software_Engineer agent's Gate Status Report.
- Do not skip Pest tests "just to ship faster." CI will block.
- Do not modify `CLAUDE.md` (this file) without explicit founder direction.
- Do not modify any file under `decisions/` without an ADR-style superseding entry. ADRs are append-only.

---

## 11. AODC Pipeline Reference

This project is built via the AODC (Agent-Orchestrated Development Cycle) methodology. Relevant artifacts live in:

| Location | Contents |
|---|---|
| `planning/00_MVP_ROADMAP.md` | Master plan — wedge, scope, sequencing, principles |
| `planning/README.md` | Index + non-negotiables + naming conventions (canonical) |
| `planning/SURGE-NN-*.md` | Per-Surge plans — Flows, dependencies, acceptance |
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
2. **`planning/00_MVP_ROADMAP.md`.** The strategic source of truth.
3. **The specific Surge plan** for the work in question.
4. **The Tech Task Package** for the Flow in question.
5. **The Founder** (via the AO). If 1–4 do not resolve it, escalate.

Do not invent answers. Do not guess at intent. Stop, surface the question, wait for direction.

---

*End of CLAUDE.md. Welcome to the project.*
