# SURGE-01 — Auth & Workspace

**Surge ID:** S-01
**Name:** Auth & Workspace
**Type:** BUILD Surge
**Estimated duration:** 5–7 days (with Claude Code); 1.5–2 weeks (manual)
**Depends on:** S-00 complete (all 7 deliverables)
**Enables:** S-02 onward (everything requires workspace + user + locale)

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — this Surge is wedge-agnostic |
| Legal domain | None — pure infrastructure |
| Sign-off | PENDING — Founder + Legal Advisor (from S-00) |

---

## Goal

Establish the foundational tenancy + identity + localization layer that every subsequent Surge depends on. A user can sign in with Google, land in a workspace, switch UI language between Arabic (RTL) and English (LTR), and access an empty Filament admin (for the founder) and an empty customer-facing shell (for end users).

By the end of this Surge:
- Auth works (Google OAuth via Laravel Socialite)
- A workspace exists for every signed-up user
- Three roles exist (Owner / Admin / Member) and are enforced via Policies
- The locale switch works fully (every framework string + every custom string)
- The Filament admin loads (empty of resources for now)
- The customer-facing app shell loads (empty of features for now)
- Pest test suite is green; CI runs Pint + Larastan + tests

This is plumbing. It is not exciting. It must be solid because every later Surge sits on it.

---

## Flows

### F-01.1 — Project bootstrap & infrastructure baseline

**Goal:** Get a Laravel 11 + Filament v3 project running locally and deployed to Cloudways.

**Scope:**
- Initialize Laravel 11 project with PHP 8.3
- Install Filament v3
- Install Pest 2.x for testing
- Install Pint for formatting
- Install Larastan level 6 for static analysis
- Configure MySQL 8 connection
- Configure Redis for cache + queues
- Configure Cloudways deployment per F-00.6 decision
- Set up GitHub Actions CI: Pint check + Larastan + Pest (all must pass on PR)
- Set up `openapi/spec.yaml` skeleton (OpenAPI 3.0 root document)
- Set up `resources/lang/ar/` and `resources/lang/en/` directories
- Configure Vite for asset bundling

**Entities touched:** None.
**API surface:** None.
**UI surface:** None (only frameworks installed).

**Key decisions to make in Wave-Ready Package:**
- Exact PHP, Laravel, Filament versions
- Composer + npm dependency lock file generation
- `.env.example` content
- CI matrix (PHP versions to test against — likely just 8.3)
- Branch protection rules (require CI green + 1 review)

**Acceptance criteria for this Flow:**
- `composer install && php artisan migrate && php artisan serve` works on a fresh clone
- Cloudways staging environment exists at a defined URL
- A "Hello, World" page returns 200 on staging
- CI runs green on a no-op PR
- README documents the dev setup

**Dependencies:** D-01 (Cloudways region) must be resolved.

---

### F-01.2 — User & Workspace entities

**Goal:** Define the foundational identity + tenancy data model.

**Scope:**
- `users` table (Laravel default + adjustments: `preferred_locale` ENUM('ar','en'), `avatar_url` nullable, no email/password fields since OAuth-only — but keep `email` unique)
- `workspaces` table (UUID primary key, `name`, `slug` unique, `default_locale` ENUM('ar','en'), `created_by_user_id`, audit timestamps, soft deletes)
- `workspace_user` pivot table (UUID, `workspace_id`, `user_id`, `role` ENUM('owner','admin','member'), `joined_at`, audit timestamps)
- `User` Eloquent model with `workspaces()` relationship and `currentWorkspace()` helper
- `Workspace` Eloquent model with `members()` relationship and `BelongsToWorkspace` trait scaffold
- A `BelongsToWorkspace` trait (used by every later tenant-scoped model) that adds a global Eloquent scope filtering by current workspace

**Entities touched:** `User` (new fields), `Workspace` (new), `workspace_user` (new pivot).

**API surface:**
- None at this Flow (auth comes in F-01.3, workspace API comes in F-01.5)

**UI surface:**
- None at this Flow

**Key decisions to make in Wave-Ready Package:**
- UUID library: Laravel's `Str::uuid()` vs `ulid()` (lean toward ULID for ordering)
- Pivot vs separate model for `workspace_user` (lean toward separate model `WorkspaceMember` for cleaner Policy bindings)
- Soft-delete behavior for workspaces (block deletion if non-empty? archive flag?)
- Index strategy on `workspace_user(workspace_id, user_id)` — composite unique

**Acceptance criteria:**
- `php artisan migrate:fresh && php artisan db:seed --class=DatabaseSeeder` produces a usable demo workspace with one Owner user
- `BelongsToWorkspace` trait is unit-tested and demonstrably filters queries
- Pest feature test verifies a user cannot read another workspace's data even with direct query

**Dependencies:** F-01.1.

---

### F-01.3 — Google OAuth authentication

**Goal:** Users can sign in with Google. First-time sign-in creates a `User` and a default `Workspace`.

**Scope:**
- Install Laravel Socialite
- Google OAuth credentials in `.env` (documented in `.env.example`)
- Routes: `GET /auth/google/redirect`, `GET /auth/google/callback`
- Controller: `Auth\GoogleOAuthController`
- Service: `AuthService::findOrCreateUserFromGoogle($googleUser)` — finds user by email, creates if missing
- On first-time sign-in: create a `Workspace` named "{User's first name}'s Workspace" with `default_locale = 'ar'`, add user as Owner
- Session-based auth for web (Laravel default)
- Sanctum token issuance for API (deferred to F-01.5 if needed)
- Logout route + flow

**Entities touched:** `User`, `Workspace`, `WorkspaceMember` (writes).

**API surface:**
- `GET /auth/google/redirect` (web route, not API)
- `GET /auth/google/callback` (web route, not API)
- `POST /logout` (web route)

**UI surface:**
- `resources/views/auth/login.blade.php` — single "Sign in with Google" button, fully bilingual
- Localized error states (Google OAuth declined, email already linked to another auth provider, etc.)

**Key decisions to make in Wave-Ready Package:**
- D-08 (Auth): Google only at MVP, confirmed in Roadmap
- Account linking strategy: what if a user's Google email matches an existing user record? (Default: allow link, log it.)
- Redirect target post-login (default: workspace dashboard)
- Cookie / session lifetime
- CSRF handling on OAuth callbacks

**Acceptance criteria:**
- Pest feature test: full OAuth round-trip with mocked Socialite driver creates User + Workspace + WorkspaceMember(Owner) in DB
- Pest feature test: second sign-in for same Google email reuses User, does NOT create new Workspace
- Pest feature test: logged-out user accessing `/dashboard` is redirected to `/auth/google/redirect`
- Login page renders correctly in both AR (RTL) and EN (LTR)

**Dependencies:** F-01.2.

---

### F-01.4 — RBAC: Roles, Policies, Filament gating `[PARTIAL-PENDING-LEGAL-REVIEW]`

**Goal:** Three roles (Owner / Admin / Member) are defined and enforced consistently across Filament admin AND the customer-facing app.

> `[PENDING-LEGAL-REVIEW]` applies to *which permissions go to which role specifically for legal data* — e.g., can a Member edit a Matter? View a contract? This is a legal-workflow question, not an infrastructure one. For S-01, ship the role primitives and a permission matrix STUB; refine the matrix in S-02 once the legal advisor reviews.

**Scope:**
- `Role` enum class in `app/Enums/Role.php` (Owner, Admin, Member)
- Base `Policy` abstract class with `ensureSameWorkspace($user, $model)` helper
- `WorkspacePolicy` (view, update, delete, invite member, remove member)
- Default role permission matrix STUB (refined in S-02):
  - Owner: everything
  - Admin: everything except delete workspace
  - Member: view + edit assigned content; no admin actions
- Filament panel registered with `canAccess()` enforcing Owner OR Admin only
- Customer-facing app accessible by all roles
- `auth:sanctum` middleware setup for future API routes
- Policy registration in `AuthServiceProvider`

**Entities touched:** No new tables; adds policies + middleware.

**API surface:** None directly; sets the policy framework for all subsequent API endpoints.

**UI surface:**
- Filament login redirects to `/admin/login` (Filament default) — but only Owner/Admin can access
- Customer-facing app middleware: must be authenticated + a member of the current workspace

**Key decisions to make in Wave-Ready Package:**
- `[PENDING-LEGAL-REVIEW]` — confirm with lawyer advisor: do Members see all matters in the workspace, or only matters they're assigned to? (Affects Matter Policy in S-02.)
- Filament admin: is it founder-only initially, or do customer-Owners also get Filament access? (Default: customer-Owners get Filament, scoped to their workspace.)
- Workspace switching: can a user belong to multiple workspaces? (Default: yes, per HAQQ Vol. 1 — implemented via `currentWorkspace` session key.)

**Acceptance criteria:**
- Pest feature test: Owner can access Filament; Member gets 403
- Pest feature test: User can switch between two workspaces; data is isolated
- Pest feature test: `Policy@before` short-circuit for Owner role works as expected
- Larastan level 6 passes on all new code

**Dependencies:** F-01.2, F-01.3.

---

### F-01.5 — Locale switching: AR (RTL) ⇄ EN (LTR)

**Goal:** The entire UI — Filament + customer-facing + email + validation messages — switches between Arabic (default, RTL) and English (LTR) cleanly, per user preference and overridable per request.

**Scope:**
- Locale switch UI: a dropdown in the customer app header AND in Filament header
- `app/Http/Middleware/SetLocale.php`: reads `?lang=ar|en` query param → falls back to user's `preferred_locale` → falls back to workspace `default_locale` → falls back to `'ar'`
- Apply middleware to `web` and `api` groups
- Persist user's choice to `users.preferred_locale` when they switch
- All Filament strings: configure Filament's localization
- All custom strings: split into domain files (`auth.php`, `validation.php`, `workspace.php`, `common.php`) in both `resources/lang/ar/` and `resources/lang/en/`
- RTL CSS: Tailwind `dir-rtl` plugin + a `[dir="rtl"]` body class set by the SetLocale middleware
- Filament panel: load RTL stylesheet when locale is AR
- Email templates: localized (Markdown mailables with `__()` keys)
- Validation error messages: full AR translation in `resources/lang/ar/validation.php`

**Entities touched:** `users.preferred_locale` (already added in F-01.2; written here).

**API surface:**
- `POST /api/v1/auth/locale` (Sanctum-auth'd) — accepts `{ "locale": "ar" | "en" }`, updates user, returns 204
- `Accept-Language` header honored in all API responses

**UI surface:**
- Header dropdown (both Filament and customer): العربية / English with current selection highlighted
- Page direction toggles correctly without page reload (Livewire-driven where needed)

**Key decisions to make in Wave-Ready Package:**
- Initial language for an anonymous visitor (default: AR per Strategic Brief)
- Direction inheritance for mixed-direction content (AR with embedded EN — `unicode-bidi: isolate`)
- Date/number formatting: Hijri vs Gregorian (default: Gregorian both locales for now; defer Hijri to Year-2)
- Currency formatting per locale (JOD/USD default; switch decimal separator)

**Acceptance criteria:**
- Pest feature test: locale switch persists across requests for the same user
- Pest feature test: AR user sees `<html dir="rtl" lang="ar">` and EN user sees `<html dir="ltr" lang="en">`
- Pest feature test: validation error message comes back in the user's selected locale
- Visual smoke test (manual screenshot via Pest Browser Plugin): login page renders correctly in both directions
- 100% of strings in Filament + customer UI use `__()` — no hardcoded literals (enforced by a Larastan custom rule or grep CI step)

**Dependencies:** F-01.3 (need auth'd user to read/write preferred_locale).

---

### F-01.6 — Customer App shell + Filament Admin shell

**Goal:** A logged-in user lands on an empty-but-structured customer app dashboard. A logged-in Owner/Admin can also access an empty-but-structured Filament admin.

**Scope:**
- Customer-facing layout: `resources/views/layouts/app.blade.php` with Tailwind shell — top nav (logo + workspace switcher + locale switch + user menu), left sidebar (empty for now, ready for S-02 nav), main content area
- Dashboard page: `resources/views/dashboard.blade.php` — empty state with "Welcome to your workspace" + bilingual greeting
- Filament panel: registered at `/admin`, navigation groups defined (Workspace, Contacts, Matters, Documents — all empty for now)
- Workspace switcher: dropdown listing all workspaces the user belongs to + "Create new workspace" action
- User menu: profile (stub), preferred locale (mirrors header switch), sign out

**Entities touched:** None.

**API surface:**
- `GET /api/v1/me` — returns the authenticated user + current workspace + role
- `POST /api/v1/workspaces/switch` — switches `currentWorkspace` for the session
- `POST /api/v1/workspaces` — creates a new workspace; auto-adds user as Owner

**UI surface (Blade + Livewire):**
- `dashboard.blade.php`
- `workspace/create.blade.php`
- `profile.blade.php` (basic)
- All bilingual; all RTL-correct

**Key decisions to make in Wave-Ready Package:**
- Sidebar layout grammar — left in LTR, right in RTL (flip with `dir`)
- Color palette + brand decisions (defer to a separate design Wave; use Tailwind defaults at S-01)
- Filament navigation group naming (locale-aware labels)
- "Create new workspace" UX — modal vs separate page (lean toward modal)

**Acceptance criteria:**
- A user signs in → lands on `/dashboard` showing their workspace name and an empty state
- Owner clicks "Admin" → lands on `/admin` Filament panel
- Member tries `/admin` → 403
- Workspace switcher lists all workspaces; switching changes data scope on dashboard
- All shell strings localized in AR/EN

**Dependencies:** F-01.2 through F-01.5.

---

## Surge acceptance criteria (must all pass to mark S-01 done)

- [ ] F-01.1: Project boots locally + on Cloudways staging; CI green on a no-op PR
- [ ] F-01.2: `User`, `Workspace`, `WorkspaceMember` migrated; `BelongsToWorkspace` trait works
- [ ] F-01.3: Google OAuth round-trip creates User + Workspace + Owner membership
- [ ] F-01.4: Role enforcement works in Filament + customer app; Member denied Filament
- [ ] F-01.5: Locale switch works end-to-end; 100% of strings localized
- [ ] F-01.6: Customer shell + Filament shell load; workspace switcher works
- [ ] All Pest tests green
- [ ] Larastan level 6 clean
- [ ] Pint check clean
- [ ] `openapi/spec.yaml` updated with auth + workspace endpoints
- [ ] Founder + Legal Advisor sign-off recorded

---

## Out of scope for this Surge (deferred or never)

- Email/password auth (D-08 default = Google only)
- Two-factor auth (Year-2)
- Account deletion / GDPR data export (Year-2 — write to backlog)
- Email/Magic-link invites to add members to a workspace (deferred to S-02 — needed when there's something to invite people to)
- SSO/SAML (Year-2 upmarket)
- Audit log UI (logged in DB; UI deferred to S-06)
- Password recovery (N/A — Google handles auth)
- Workspace deletion UX (admin-only DB action for MVP; UI deferred to S-06)

---

## What the Software Engineer agent should produce from this Surge

For each Flow (F-01.1 through F-01.6), produce a tech-task document containing:

1. **Migration sequence:** every `php artisan make:migration ...` command in order, with full schema, indexes, foreign keys
2. **Eloquent models:** full class definitions including casts, relationships, scopes, traits used
3. **Filament resources (where applicable):** resource class skeleton with form schema, table columns, policy bindings
4. **Routes:** exact `routes/web.php` and `routes/api.php` entries
5. **Controllers:** class skeleton with method signatures and `__()` localization keys referenced
6. **FormRequests:** validation rules for every write endpoint
7. **Policies:** method-by-method authorization rules
8. **Pest test file inventory:** list every test file to create with one-line description per test
9. **`openapi/spec.yaml` diff:** YAML diff to apply
10. **Localization keys added:** flat list of `__()` keys with AR + EN values
11. **Larastan exceptions (if any):** with justification

The Software Engineer agent should refuse to produce these tech tasks until S-00 is fully signed off.
