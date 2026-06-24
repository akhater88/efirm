# WAVE-READY PACKAGE: SURGE-14.1 — Super-Admin Panel Foundation

**Version:** 1.0
**Product Designer:** Abdullah Mohammed (via AODC Product Designer agent)
**Date:** 2026-06-24
**Status:** Draft — Awaiting Product Owner Sign-Off
**Surge:** SURGE-14 (Super-Admin Panel & Subscription Management)
**Wave dependencies:** SURGE-01 (Auth + Workspace) only
**Wave dependents:** Waves 14.2 through 14.9 (all of SURGE-14 blocks on 14.1)
**Estimated execution:** 2–3 AO hours; 45–60 min with Claude Code given spec density
**External advisor input required:** None for 14.1 (Khaldoun deferred to Waves 14.3 + 14.9)

---

## Scope boundary (explicit — to prevent scope creep)

**IN SCOPE for Wave 14.1:**

- Separate Filament panel at `/admin`
- `admin` auth guard with session-cookie auth
- `AdminUser` model with 4 roles (super_admin, support, finance, read_only)
- Login flow + logout + session timeout (60min idle / 12h absolute)
- Dashboard skeleton with 4 stat cards showing zero-state values
- Navigation structure for current + future resources
- Admin user CRUD (list, create, edit, disable, re-enable, reset password)
- Append-only `admin_activity_log` table + read-only Filament resource
- Seeder for first super_admin
- Locale switching (Arabic RTL / English LTR) with per-admin stored preference
- Cross-context isolation between `admin` and `web` guards

**OUT OF SCOPE for Wave 14.1 (deferred to listed Wave):**

- Plans CRUD → Wave 14.2
- Workspace management resource → Wave 14.3
- Impersonation → Wave 14.3
- Subscriptions, lifecycle, events → Wave 14.4
- Stripe integration → Waves 14.5, 14.6
- Entitlement service, suspended-read-only middleware → Wave 14.7
- Usage tracking, cap enforcement → Wave 14.8
- PDPL cancellation, retention, purge → Wave 14.9
- Cross-border consent back-port to signup → Wave 14.1.5 (suggested)

---

# 1. INTENT DEFINITION

## Problem

Today the platform has no administrative surface for managing law firm customers. Every operational task — provisioning a new firm, changing a subscription, suspending a delinquent account, investigating a support ticket — requires direct MySQL access via SSH and either Tinker or raw SQL. This blocks beta launch on three counts:

1. **Operationally non-viable** — onboarding the first 10 paying firms cannot happen via Tinker; each support action would require an engineer in the loop.
2. **No audit trail** — direct DB writes leave no record of who changed what or why, violating PDPL Article 13 (data subject right to know who processed their data) and making fraud investigations impossible.
3. **Security boundary missing** — operations staff currently need full production credentials to do their job, which fails the principle of least privilege and is a Stripe PCI-DSS adjacency risk.

Wave 14.1 is the foundation Wave that delivers the panel chrome, authentication, and admin user management. It owns no business logic for plans, workspaces, subscriptions, or Stripe — those are subsequent Waves built within this panel.

## Target User

Internal SaaS platform operators employed by the company (not law firm users). At launch this is Abdullah plus up to 4 founding-team members. The `AdminUser` model carries four roles with distinct access levels:

| Role | Description | Initial Headcount (estimate) |
|---|---|---|
| `super_admin` | Full access to all resources + ability to create/edit/delete other admin users | 1–2 (founders) |
| `support` | Read all workspaces; can suspend/unsuspend; cannot edit plans or billing | 1–2 (customer success) |
| `finance` | Read all workspaces; can view + edit subscription billing data; cannot impersonate or suspend | 1 (when hired) |
| `read_only` | Read-only access to all resources for auditors, investors, or external advisors during diligence | 0–2 (situational) |

Admin users are **distinct entities** from workspace users (`users` table). An admin cannot be a firm user simultaneously; the two authentication contexts are isolated by Laravel guard (`admin` vs `web`).

## Outcome

When Wave 14.1 ships, the following end states are true:

1. A second Filament v5.6 panel is registered at `/admin`, fully isolated from the firm-facing workspace panel at `/app` (different guard, different middleware stack, different layout, different navigation).
2. A seeded `super_admin` account exists with credentials in `.env` for the first deploy; the seeder is idempotent and refuses to overwrite existing admin users.
3. Admin users authenticate via email + password against the `admin` guard with session cookies scoped to the `/admin` path.
4. Authenticated admins land on a dashboard with four placeholder stat cards (Total Workspaces, Active Subscriptions, MRR USD, Trials Expiring This Week) — all showing `0` or `—` until subsequent Waves populate them. The dashboard is not empty; it is structurally complete.
5. Super-admins can list, create, edit, and deactivate other admin users via a Filament resource. Deactivation is soft (sets `disabled_at` timestamp) — admin records are never hard-deleted to preserve the audit trail.
6. Every admin login, logout, role change, and deactivation is recorded in an append-only `admin_activity_log` table per CLAUDE.md ledger rules.
7. The panel renders correctly in both Arabic (RTL) and English (LTR); locale is per-admin-user preference stored on the `admin_users` table.

## Success Metrics

| # | Metric | Target | Measurement Method |
|---|---|---|---|
| M-1 | Time to provision a new admin user | ≤ 90 seconds from `/admin/users/create` click to confirmation toast | Manual stopwatch test by Abdullah during acceptance |
| M-2 | Admin login flow page count | ≤ 3 page loads from `/admin/login` to dashboard render | Network tab count in Chrome DevTools |
| M-3 | Dashboard initial render time (TTFB to interactive) | ≤ 800 ms p95 from Amman → Cloudways FRA1 | Browser Performance API, measured across 20 sequential loads |
| M-4 | Admin action audit coverage | 100% of login, logout, create, update, disable events written to `admin_activity_log` within the same request | Pest feature test: `admin_activity_log_records_every_action_test.php` asserts row count after each operation |
| M-5 | Cross-context isolation | 0 successful authentications using `users` table credentials against `/admin/login`, and 0 successful authentications using `admin_users` credentials against `/app/login` | Pest feature test: `admin_and_workspace_guards_are_isolated_test.php` |
| M-6 | Pest test coverage for Wave 14.1 code paths | ≥ 95% line coverage of `App\Filament\Admin\*`, `App\Models\AdminUser`, `App\Models\AdminActivityLog` | `php artisan test --coverage --min=95` scoped to the Wave's namespaces |
| M-7 | RTL parity | Every admin screen renders with `dir="rtl"` when Arabic locale is active; no horizontal scroll on 1280px viewport | Manual visual QA + Pest Browser test screenshot diff on 4 key screens |

## Business Value

1. **Unblocks SURGE-14.2 through 14.9.** No subsequent admin Wave can ship without this panel; everything from Plans CRUD to Stripe webhook handling lives inside this Filament panel. Wave 14.1 is the critical path for the entire Surge.
2. **Operational independence from engineering.** After this ships, support, billing inquiries, and routine account changes no longer require an engineer with production database access. Engineering time freed for product work.
3. **PDPL Article 13 compliance foundation.** The append-only `admin_activity_log` table establishes the audit trail mechanism that satisfies the data subject's right to know who accessed and processed their personal data. This is a hard regulatory requirement and a prerequisite for handling the first paying customer.
4. **Reduces production credential blast radius.** Currently zero non-engineer staff can perform any operational action; after Wave 14.1, four named admin accounts replace shared SSH access to MySQL. This is a baseline security posture required before exposing the product to law firms whose data is subject to attorney-client privilege.
5. **Pre-condition for Stripe go-live.** Stripe will not allow production webhook handling at scale without an internal tool for incident response (refunds, dispute review, manual subscription correction). Wave 14.1 is the shell that houses that capability in Wave 14.4–14.6.

---

# 2. USER STORIES

Wave 14.1 contains 7 user stories. Each sized for a single AO Wave segment (~20–35 min Claude Code execution).

## US-001: Admin Login

**As a** seeded super_admin user with credentials in my password manager,
**I want to** authenticate at `/admin/login` using email and password,
**so that** I can access the administrative panel without using the firm-facing workspace login.

**Acceptance Criteria:**

- **GIVEN** I am unauthenticated and navigate to `/admin` **WHEN** the route resolves **THEN** I am redirected to `/admin/login` with HTTP 302.
- **GIVEN** I am on `/admin/login` **WHEN** the page renders **THEN** I see a centered card containing the platform logo, the heading "Admin Sign In" (EN) / "تسجيل دخول المسؤول" (AR), an email input, a password input, a "Sign In" button, and no link to register, sign up, or "forgot password" (self-recovery is out of scope for Wave 14.1).
- **GIVEN** I submit valid `admin_users` credentials for an active (non-disabled) admin **WHEN** the form posts **THEN** I am authenticated against the `admin` guard, a session cookie scoped to path `/admin` is set, my `admin_users.last_login_at` is updated to `now()`, a row is written to `admin_activity_log` with `event_type = 'admin.login.success'`, and I am redirected to `/admin/dashboard`.
- **GIVEN** I submit credentials matching a `users` table record (not `admin_users`) **WHEN** the form posts **THEN** authentication fails with the same generic message as wrong-password (to prevent user enumeration), no session is created against the `admin` guard, and an `admin.login.failed` event is logged with `reason = 'no_admin_record'`.
- **GIVEN** I submit credentials for an admin where `disabled_at IS NOT NULL` **WHEN** the form posts **THEN** authentication fails with the message "This account has been deactivated. Contact a super-admin." (EN) / "تم تعطيل هذا الحساب. يُرجى التواصل مع المسؤول الأعلى." (AR), and an `admin.login.blocked_disabled` event is logged.
- **GIVEN** I submit invalid credentials 5 times within 60 seconds from the same IP **WHEN** the 6th attempt is made **THEN** Laravel's `RateLimiter::for('admin-login')` returns HTTP 429 with retry-after header, and an `admin.login.rate_limited` event is logged.
- **GIVEN** I am authenticated as an admin and visit `/app/login` **WHEN** the workspace login page renders **THEN** I am NOT auto-authenticated into the workspace context (the `web` guard sees no user).

**Edge Cases:**
- **Error (network):** Form submission times out → button shows "Signing in…" then reverts after 10s with toast "Network error. Try again." (EN) / "خطأ في الشبكة. يُرجى المحاولة مرة أخرى." (AR). Password field is preserved (not cleared).
- **Empty (no admins seeded):** First-deploy edge case where `admin_users` table is empty → login form still renders normally and rejects all attempts. Seeder must run before first login; deployment runbook documents this. No special UI for the empty-table state — this is intentional (UI hint would leak schema info).
- **Loading:** Submit button disabled with inline spinner; form inputs remain visible but `aria-busy="true"` set on the form element.
- **Offline:** Native browser offline error; no custom handling — admin panel is internal-use only, offline is not a supported workflow.
- **Boundary:** Email max 255 chars, must pass `email:rfc,dns` validation; password min 12 chars (admin policy stricter than firm users' 8). Reject any longer email at FormRequest layer before DB lookup.
- **RTL:** Form fields use `dir="auto"` on email/password inputs so Latin characters render LTR even when page locale is Arabic; submit button text mirrors per locale; "Sign In" button anchored to the inline-end of the form (right in LTR, left in RTL).

## US-002: Admin Dashboard

**As an** authenticated admin (any role),
**I want to** see a dashboard summarizing platform state immediately after login,
**so that** I have a single entry point that orients me without hunting through navigation.

**Acceptance Criteria:**

- **GIVEN** I am authenticated as any admin role **WHEN** I navigate to `/admin/dashboard` **THEN** the page renders with the platform header, sidebar navigation, and a main content area containing exactly 4 stat cards in a responsive grid (4-up on desktop ≥1280px, 2-up on tablet 768–1279px, 1-up on mobile <768px).
- **GIVEN** the dashboard renders **WHEN** I inspect the cards **THEN** I see in this fixed order: (1) "Total Workspaces" / "إجمالي مساحات العمل" — displays `0`; (2) "Active Subscriptions" / "الاشتراكات النشطة" — displays `0`; (3) "MRR (USD)" / "الإيرادات الشهرية المتكررة (دولار أمريكي)" — displays `$0.00`; (4) "Trials Expiring This Week" / "التجارب المنتهية هذا الأسبوع" — displays `0`.
- **GIVEN** the dashboard renders **WHEN** I view each stat card **THEN** each card shows: the metric label, the value in large type (`text-3xl font-semibold`), a subtle icon in the inline-start corner, and the text "Updated when subscription data is available" / "يتم التحديث عند توفر بيانات الاشتراك" in small muted text below the value (because Wave 14.1 has no Subscription model yet — this is the honest placeholder, not a fake number).
- **GIVEN** I am authenticated as `read_only` role **WHEN** the dashboard renders **THEN** all 4 cards are visible and identical to other roles — read-only restriction applies to mutations, not to dashboard visibility.
- **GIVEN** I refresh the dashboard **WHEN** the page reloads **THEN** the entire render completes within 800ms p95 from Amman → FRA1 (success metric M-3).

**Edge Cases:**
- **Error:** Dashboard widget query fails → card shows "—" in the value position with tooltip "Unable to load. Contact engineering." (EN) / "تعذّر التحميل. يُرجى التواصل مع فريق الهندسة." (AR). Other cards continue to render independently (one failure does not blank the dashboard).
- **Empty:** Default state for Wave 14.1 (no Subscription data exists) — cards show `0` or `$0.00` as documented above; this is the empty state.
- **Loading:** Cards render with skeleton placeholders (animated `bg-gray-200` blocks the same dimensions as final values) for any card whose data source takes > 100ms.
- **Offline:** Filament panel does not support offline; the request fails at the network layer and the user sees the browser's offline page.
- **Boundary:** MRR display formats with two decimals, comma thousands separator (`$1,234.56`); when MRR exceeds `$999,999.99` (unlikely in beta), display switches to `$1.2M` shorthand. This boundary will not trigger in Wave 14.1 but the format function must handle it.
- **RTL:** Stat cards' label and value remain left-aligned within their card in LTR, right-aligned in RTL; icon position flips from inline-start (left in LTR, right in RTL); the grid order is unchanged across locales (Total Workspaces remains the first card visually in both reading directions, mirrored by the browser's `dir` attribute on the parent).

## US-003: Create Admin User (super_admin only)

**As a** super_admin,
**I want to** create new admin user accounts with email, name, role, and initial password,
**so that** I can onboard support, finance, and read-only staff without going through engineering.

**Acceptance Criteria:**

- **GIVEN** I am authenticated as `super_admin` **WHEN** I navigate to `/admin/users` **THEN** I see a Filament resource list page with a "New Admin User" button anchored inline-end of the header.
- **GIVEN** I am authenticated as `support`, `finance`, or `read_only` **WHEN** I navigate to `/admin/users` **THEN** I see HTTP 403 with the Filament default forbidden page (the navigation item itself is hidden via `canViewAny()` policy for non-super-admins).
- **GIVEN** I click "New Admin User" as super_admin **WHEN** the create form renders **THEN** I see fields: Full Name (required, max 100), Email (required, unique on `admin_users.email`, max 255, RFC+DNS validated), Role (required, select with 4 options: Super Admin, Support, Finance, Read Only), Initial Password (required, min 12 chars, must match Laravel's `Password::min(12)->letters()->mixedCase()->numbers()->symbols()`), Confirm Password (required, must match), Locale (required, select: العربية / English, default العربية).
- **GIVEN** I submit a valid form **WHEN** the request processes **THEN** a row is inserted into `admin_users` with `disabled_at = NULL`, an entry is written to `admin_activity_log` with `event_type = 'admin.user.created'` and `payload` containing the new admin's ID + role (but NOT the password), and I am redirected to the new admin's edit page with success toast "Admin user created." / "تم إنشاء حساب المسؤول."
- **GIVEN** I submit a form where email already exists in `admin_users` **WHEN** the request processes **THEN** validation fails with field-level error on the Email input: "An admin with this email already exists." / "يوجد مسؤول بهذا البريد الإلكتروني بالفعل." — no row is written, no event logged.
- **GIVEN** I submit a form where email exists in the `users` table (firm users) but NOT in `admin_users` **WHEN** the request processes **THEN** the create succeeds — this is intentional: admin and firm user identities are deliberately allowed to share an email since they are separate authentication contexts. A note appears in the success toast: "Note: this email is also registered as a workspace user." / "ملاحظة: هذا البريد مسجل أيضاً كمستخدم لمساحة عمل."
- **GIVEN** I attempt to create an admin with role `super_admin` while I am `super_admin` **WHEN** the request processes **THEN** creation succeeds (super_admins can create peers).

**Edge Cases:**
- **Error:** Database write fails (e.g., MySQL connection drops) → form re-renders with all field values preserved (except passwords) and top-of-form alert "Could not create admin. Try again or contact engineering." / "تعذّر إنشاء المسؤول. يُرجى المحاولة مرة أخرى أو التواصل مع فريق الهندسة."
- **Empty:** First admin creation in fresh deploy — no special state; seeder creates the first super_admin, and the first manually-created admin is via this flow.
- **Loading:** Submit button shows inline spinner; form disabled with `aria-busy="true"`.
- **Offline:** Browser-native handling, no custom UI.
- **Boundary:** Name max 100 chars; email max 255 chars; password 12–128 chars (upper bound prevents bcrypt cost-bombing); locale must be exactly `ar` or `en` (no third value).
- **RTL:** Form labels and helper text flip to right-aligned in Arabic; the Password and Confirm Password fields use `dir="ltr"` regardless of locale because passwords contain mixed-direction characters and right-aligning them creates input ambiguity; the show/hide password toggle remains in the inline-end position.

## US-004: List, Edit, Disable Admin Users (super_admin only)

**As a** super_admin,
**I want to** see all admin users, edit their name/role/locale, reset their password, and deactivate them,
**so that** I can manage the team without engineering involvement and revoke access immediately when someone leaves.

**Acceptance Criteria:**

- **GIVEN** I am authenticated as `super_admin` **WHEN** I view `/admin/users` **THEN** I see a Filament table with columns: Name (sortable), Email (sortable), Role (filterable by enum), Last Login (sortable, relative time format e.g. "2 hours ago"), Status (badge: "Active" green / "Disabled" gray), Created (sortable, date format).
- **GIVEN** the table renders **WHEN** there are more than 25 rows **THEN** Filament cursor pagination is applied per CLAUDE.md (no offset pagination).
- **GIVEN** I click a row's edit action **WHEN** the edit page renders **THEN** I see the same fields as create (US-003) EXCEPT the Initial Password fields are replaced by a "Reset Password" button (separate action — see acceptance criteria below).
- **GIVEN** I edit any field and save **WHEN** the request processes **THEN** the row is updated, `admin_activity_log` records `event_type = 'admin.user.updated'` with `payload` containing a JSON diff of changed fields (excluding password fields), and a success toast appears.
- **GIVEN** I attempt to change my OWN role from `super_admin` to a lower role **WHEN** I save **THEN** validation fails with "You cannot demote yourself. Ask another super-admin." / "لا يمكنك تخفيض دورك. يُرجى الطلب من مسؤول أعلى آخر." — this prevents the lockout scenario where the last super_admin demotes themselves.
- **GIVEN** I am the only super_admin **WHEN** I attempt to change my role OR disable myself **THEN** the operation is blocked at the FormRequest validation layer with "At least one active super-admin must exist at all times." / "يجب أن يتوفر مسؤول أعلى نشط واحد على الأقل في جميع الأوقات."
- **GIVEN** I click "Reset Password" on another admin's edit page **WHEN** I confirm in the modal **THEN** a new random 16-character password is generated, displayed once in a copyable field with the warning "This password will not be shown again. Copy it now." / "لن يتم عرض كلمة المرور هذه مرة أخرى. انسخها الآن." — and `admin.user.password_reset` is logged.
- **GIVEN** I click "Disable" on another admin's row **WHEN** I confirm in the modal **THEN** `disabled_at` is set to `now()`, all active sessions for that admin are invalidated (via `auth('admin')->invalidateSession()` or DB-level session purge), `admin.user.disabled` is logged, and the row's Status badge updates to "Disabled".
- **GIVEN** I click "Re-enable" on a disabled admin's row **WHEN** I confirm **THEN** `disabled_at` is set to `NULL`, `admin.user.reenabled` is logged.

**Edge Cases:**
- **Error:** Concurrent edit (admin A and admin B both edit user X's row simultaneously) → optimistic locking via `updated_at` check; the second save fails with HTTP 409 and inline alert "This record was modified while you were editing. Reload to see changes." / "تم تعديل هذا السجل أثناء قيامك بالتحرير. يُرجى إعادة التحميل لمشاهدة التغييرات." with a "Reload" button.
- **Empty:** Zero admin users → impossible state after seeder runs; if encountered (e.g. all admins manually deleted via tinker — bug), the list shows the empty state "No admin users found. Contact engineering — this should not happen." / "لا يوجد مسؤولون. يُرجى التواصل مع فريق الهندسة — يجب ألا تحدث هذه الحالة." with no Create button (because un-authenticatable state).
- **Loading:** Table shows skeleton rows during initial load; row-level actions show button spinner during their async operation.
- **Offline:** Standard browser handling.
- **Boundary:** Same as US-003 for field constraints.
- **RTL:** Table column order flips visually (Name becomes the right-most column in Arabic per RTL reading order); action menus (three-dot kebab) anchor to the inline-end of each row; status badges render with logical-property padding so the colored dot sits at the inline-start of the badge regardless of locale.

## US-005: Logout & Session Timeout

**As an** authenticated admin,
**I want** logout to be one click from any page and session timeout to be enforced,
**so that** I can end my session deliberately and the platform protects against forgotten-laptop scenarios.

**Acceptance Criteria:**

- **GIVEN** I am authenticated **WHEN** I click my avatar in the panel top-right corner **THEN** a dropdown appears containing my display name, my email, my role badge, a "Switch Language" item, and a "Sign Out" item.
- **GIVEN** I click "Sign Out" **WHEN** the request processes **THEN** my `admin` guard session is invalidated, my session cookie is cleared, `admin.logout` is logged, and I am redirected to `/admin/login` with toast "Signed out." / "تم تسجيل الخروج."
- **GIVEN** my admin session has been idle for 60 minutes **WHEN** I make any authenticated request **THEN** the request returns HTTP 401, I am redirected to `/admin/login`, `admin.session.expired` is logged, and the login page shows "Your session expired. Sign in again." / "انتهت صلاحية جلستك. يُرجى تسجيل الدخول مرة أخرى."
- **GIVEN** I am actively using the panel (any request within the last 60 minutes) **WHEN** the idle timer is checked **THEN** my session is extended; idle is measured from last request, not from login time.
- **GIVEN** my admin session has been alive for 12 hours of continuous activity **WHEN** I make a request **THEN** the request returns HTTP 401 and I must re-authenticate; this is a hard absolute cap (`admin_users.session_started_at + 12h`) independent of idle activity.

**Edge Cases:**
- **Error:** Logout fails on the server (rare) → client clears local session cookie defensively and redirects to login; on next page load the admin re-authenticates.
- **Empty:** N/A.
- **Loading:** "Sign Out" button shows a spinner for up to 2 seconds; after 2s, force-clear local state and redirect regardless of server response.
- **Offline:** Logout button click clears local cookies and redirects to a static "/admin/login" page; the server-side session is invalidated on next reachability.
- **Boundary:** Idle timeout = 60 minutes (`config('admin.session.idle_minutes')`); absolute timeout = 12 hours (`config('admin.session.absolute_hours')`). Both configurable.
- **RTL:** Avatar dropdown opens from the inline-end of the header; dropdown items right-align text and flip icons to the inline-end position in Arabic.

## US-006: Locale Switching

**As an** admin user with a stored locale preference,
**I want** the panel to render in my preferred language and allow me to switch on the fly,
**so that** my reading direction and language match my preference without re-authentication.

**Acceptance Criteria:**

- **GIVEN** a new admin user is created with `locale = 'ar'` **WHEN** they log in **THEN** the panel renders with `<html lang="ar" dir="rtl">` and all UI strings come from `resources/lang/ar/admin.php`.
- **GIVEN** a new admin user is created with `locale = 'en'` **WHEN** they log in **THEN** the panel renders with `<html lang="en" dir="ltr">` and strings come from `resources/lang/en/admin.php`.
- **GIVEN** I am authenticated **WHEN** I click "Switch Language" in my avatar dropdown **THEN** my `admin_users.locale` is updated to the other value, the current page reloads in the new locale, and `admin.locale.changed` is logged.
- **GIVEN** the panel is loaded in Arabic **WHEN** I inspect any screen **THEN** every visible string has a corresponding `__('admin.*')` lookup — no hard-coded English strings appear in templates (validated by a Pest test that scans Blade and PHP files for hard-coded user-facing strings).
- **GIVEN** a translation key is missing in the active locale's lang file **WHEN** the page renders **THEN** the key falls back to the other locale (Arabic → English fallback or vice versa); if both are missing, the raw key is displayed with a `data-missing-translation` attribute for developer debugging.

**Edge Cases:**
- **Error:** Locale switch DB write fails → page reloads in the originally-stored locale; toast "Could not change language. Try again." / "تعذّر تغيير اللغة. يُرجى المحاولة مرة أخرى."
- **Empty:** N/A (locale is required on creation).
- **Loading:** Brief full-page reload, no special state.
- **Offline:** Switch attempt fails silently; locale unchanged.
- **Boundary:** Locale enum is strictly `ar` or `en`; any other value (database tampering) defaults to `ar` at runtime with a warning in the log.
- **RTL:** This story IS the RTL story — every other story inherits behavior from US-006.

## US-007: Audit Log Visibility (read-only across all admin roles)

**As any** admin (including read_only),
**I want to** view the `admin_activity_log` for accountability investigations,
**so that** I can answer "who did what when" without engineering access to the database.

**Acceptance Criteria:**

- **GIVEN** I am authenticated as any role **WHEN** I navigate to `/admin/activity-log` **THEN** I see a Filament resource list page with columns: Timestamp (sortable desc by default), Admin (name + email), Event Type (filterable enum), Target (polymorphic — e.g., "Admin User: jane@example.com" or "Self"), IP Address, Payload (truncated to 120 chars with "View full" expand).
- **GIVEN** I am authenticated as any role **WHEN** I view the list **THEN** there is NO create action, NO edit action, NO delete action — only view (because the log is append-only per CLAUDE.md ledger rules).
- **GIVEN** I click a row **WHEN** the detail view renders **THEN** I see the full payload in a formatted JSON viewer, the admin's user agent string, and a "Related Events" panel showing the 10 events immediately before and after this one for the same admin (for context during investigations).
- **GIVEN** I am `read_only` role **WHEN** I view the log **THEN** I have identical access to other roles (audit visibility is intentionally democratic — any admin can review any other admin's actions).
- **GIVEN** the log contains more than 25 rows **WHEN** the page renders **THEN** cursor pagination is applied (no offset).
- **GIVEN** I apply a filter for Event Type = `admin.login.failed` AND a date range **WHEN** the table refreshes **THEN** rows are filtered server-side and the filter state is preserved in the URL query string for shareability.

**Edge Cases:**
- **Error:** Query timeout (large log, complex filter) → table shows "Query took too long. Narrow your filters." / "استغرق الاستعلام وقتاً طويلاً. يُرجى تضييق نطاق التصفية." with the existing filter UI preserved.
- **Empty:** Fresh deploy, no events yet → empty state "No activity logged yet. Events will appear as admins use the panel." / "لم يتم تسجيل أي نشاط بعد. ستظهر الأحداث عند استخدام المسؤولين للوحة."
- **Loading:** Table skeleton during initial load and during filter application.
- **Offline:** Standard browser handling.
- **Boundary:** Payload field is `JSON` type in MySQL 8 — max ~1GB but practically should be ≤4KB per row; if a payload exceeds 64KB (unusual), the table view truncates to 200 chars with a "Payload too large — view full" link; the detail view loads the full payload via a lazy fetch.
- **RTL:** JSON payload remains LTR regardless of panel locale (JSON syntax is left-to-right by convention); timestamps render in the locale's preferred format (`d M Y H:i` for Arabic, `M d Y H:i` for English, both with 24-hour time).

## Cross-Story Architectural Observations

1. **Event type taxonomy for `admin_activity_log` (Wave 14.1):** Define in `app/Enums/AdminActivityEventType.php` — `admin.login.success`, `admin.login.failed`, `admin.login.blocked_disabled`, `admin.login.rate_limited`, `admin.logout`, `admin.session.expired`, `admin.user.created`, `admin.user.updated`, `admin.user.password_reset`, `admin.user.disabled`, `admin.user.reenabled`, `admin.locale.changed`. 12 event types in Wave 14.1; more added in subsequent Waves.

2. **Sessions are cookie-based, not Sanctum-token-based, for the admin panel.** Sanctum is for the public REST API (firm-facing). The `admin` guard uses standard Laravel session driver. This is intentional — the admin panel is browser-only.

3. **"At least one super_admin must exist" invariant (US-004)** is enforced at THREE layers: FormRequest validation, Policy method, and a database-level Pest test. Defense in depth because lockout is an unrecoverable failure mode without engineering intervention.

---

# 3. WIREFRAMES DOCUMENTATION

## Wireframe approach: Path A (spec-first, no Figma)

Wave 14.1 is internal-only. Filament v5.6 generates ~90% of admin UI from resource configuration; Figma wireframes for a Filament-built admin add limited value. **This section is the source of truth.** No Figma link exists for Wave 14.1.

Figma is reserved for firm-facing surfaces (SURGE-03 Document Workspace etc.) where layout is free-form.

## 3.1 Screen Inventory

8 screens total. Mapping to Filament classes:

| # | Screen Name | Type | Path | User Story | Filament Class |
|---|---|---|---|---|---|
| 1 | Admin Login | Custom Livewire page | `/admin/login` | US-001 | `App\Filament\Admin\Pages\Auth\Login` |
| 2 | Admin Dashboard | Filament Dashboard page | `/admin` or `/admin/dashboard` | US-002 | `App\Filament\Admin\Pages\Dashboard` (default) |
| 3 | Admin Users — List | Filament Resource ListRecords page | `/admin/admin-users` | US-004 | `App\Filament\Admin\Resources\AdminUserResource\Pages\ListAdminUsers` |
| 4 | Admin Users — Create | Filament Resource CreateRecord page | `/admin/admin-users/create` | US-003 | `App\Filament\Admin\Resources\AdminUserResource\Pages\CreateAdminUser` |
| 5 | Admin Users — Edit | Filament Resource EditRecord page | `/admin/admin-users/{record}/edit` | US-004 | `App\Filament\Admin\Resources\AdminUserResource\Pages\EditAdminUser` |
| 6 | Activity Log — List | Filament Resource ListRecords page | `/admin/activity-log` | US-007 | `App\Filament\Admin\Resources\AdminActivityLogResource\Pages\ListAdminActivityLogs` |
| 7 | Activity Log — View | Filament Resource ViewRecord page | `/admin/activity-log/{record}` | US-007 | `App\Filament\Admin\Resources\AdminActivityLogResource\Pages\ViewAdminActivityLog` |
| 8 | User Menu Dropdown | Filament topbar component (render hook) | overlay on all authenticated pages | US-005, US-006 | `App\Filament\Admin\Components\UserMenu` |

## 3.2 Navigation Flow

### Unauthenticated Flow

```
Browser arrives at /admin/*   ──►  Middleware redirects to /admin/login
                                              │
                                              ▼
                                   [Screen 1: Admin Login]
                                              │
                              ┌───────────────┴───────────────┐
                              │                               │
                       Valid credentials              Invalid credentials
                              │                               │
                              ▼                               ▼
                       [Screen 2: Dashboard]         Re-render Login with error
```

### Authenticated Flow

```
[Screen 2: Dashboard] ◄────── (default landing after login)
        │
        ├── Sidebar Navigation
        │   ├── "Dashboard"       ──►  [Screen 2]
        │   ├── "Admin Users"     ──►  [Screen 3]    (super_admin only — hidden otherwise)
        │   ├── "Activity Log"    ──►  [Screen 6]    (all roles)
        │   └── [future Waves]    ──►  Plans, Workspaces, Subscriptions
        │
        └── User Menu (top-right avatar)
            ├── (display: name, email, role badge)
            ├── "Switch Language"  ──►  reload current page in other locale
            └── "Sign Out"         ──►  [Screen 1: Admin Login]

[Screen 3] → [Screen 4 (Create)] OR [Screen 5 (Edit)] → row actions: Reset Password / Disable / Re-enable
[Screen 6] → [Screen 7 (View)]
```

### Session Expiry Flow (cross-cutting)

```
Any authenticated request after idle > 60min OR session age > 12h
        │
        ▼
Middleware returns 401  ──►  Redirect to /admin/login?reason=expired
        │
        ▼
Login page displays "Your session expired. Sign in again."
```

## 3.3 Screen-by-Screen Layout Specifications

### Screen 1: Admin Login (custom page, NOT Filament default)

**Layout:** Centered card on neutral background. Card max-width 420px, vertical center, top margin minimum 64px on viewports < 600px.

**Components (top to bottom):**

1. **Brand mark** — platform logo, 48px height, centered, 32px bottom margin
2. **Page heading** — `<h1>` with `text-2xl font-semibold`, content `__('admin.auth.signIn.heading')`, 8px bottom margin
3. **Subheading** — small muted text, `__('admin.auth.signIn.subheading')`, 32px bottom margin
4. **Email input** — Filament TextInput equivalent (raw Blade since pre-auth), label above input, `type="email"`, `autocomplete="username"`, `dir="auto"`, full width, 16px bottom margin
5. **Password input** — label above, `type="password"`, `autocomplete="current-password"`, `dir="ltr"` (passwords always LTR), show/hide toggle in inline-end position, full width, 24px bottom margin
6. **Submit button** — primary button, full width, content `__('admin.auth.signIn.submit')`, shows spinner + disabled state during submission
7. **Error region** — appears above the Email input when present, 16px vertical margin when visible, `role="alert"`, `aria-live="polite"`

**Explicitly NOT present:** Sign up link, Forgot password link, "Remember me" checkbox, social login buttons, marketing copy, language switcher.

**Reasoning for no language switcher:** Pre-authentication, no `admin_users.locale` value exists; defaulting to browser locale is intentional to avoid building a server-side locale-toggle for unauthenticated routes.

### Screen 2: Admin Dashboard

**Layout:** Standard Filament panel chrome — sidebar (left in LTR, right in RTL), top header bar, main content area.

**Header (Filament default + customizations):**
- Inline-start: panel title `__('admin.panel.title')` = "Platform Admin" / "إدارة المنصة"
- Inline-end: User menu dropdown (Screen 8)

**Sidebar Navigation:**

```
┌─────────────────────────┐
│  Platform Admin         │
│                         │
│  📊  Dashboard          │  ← Screen 2 (current)
│  👥  Admin Users        │  ← Screen 3 (super_admin only)
│  📋  Activity Log       │  ← Screen 6 (all roles)
│                         │
│  ─── future Waves ───   │
│  💼  Workspaces         │  ← Wave 14.3
│  📦  Plans              │  ← Wave 14.2
│  💳  Subscriptions      │  ← Wave 14.4
└─────────────────────────┘
```

For Wave 14.1, only the first three navigation items are registered.

**Main Content — Dashboard Grid:**

```
┌─────────────────────────────────────────────────────────────────┐
│   ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌────────┐│
│   │ 🏢  Total    │ │ ✅  Active   │ │ 💰  MRR      │ │ ⏳     ││
│   │ Workspaces   │ │ Subs         │ │ (USD)        │ │ Trials ││
│   │     0        │ │     0        │ │   $0.00      │ │   0    ││
│   │              │ │              │ │              │ │        ││
│   │ (sub data    │ │ (sub data    │ │ (sub data    │ │ (sub   ││
│   │  pending)    │ │  pending)    │ │  pending)    │ │  data) ││
│   └──────────────┘ └──────────────┘ └──────────────┘ └────────┘│
└─────────────────────────────────────────────────────────────────┘
```

Implemented as Filament `StatsOverviewWidget` (`app/Filament/Admin/Widgets/PlatformStatsWidget.php`). Each stat is a `Stat::make()` call. In Wave 14.1, each stat's `value()` closure returns the hard-coded zero state per US-002 — replaced in later Waves by real queries.

### Screen 3: Admin Users — List

**Header:**
- Inline-start: Page heading `__('admin.users.list.heading')`
- Inline-end: "New Admin User" button (Filament `CreateAction`, super_admin only via policy `canCreate()`)

**Table Columns:**

| Order | Column Key | Display | Sortable | Searchable | Filterable |
|---|---|---|---|---|---|
| 1 | name | Plain text | ✓ | ✓ | – |
| 2 | email | Plain text | ✓ | ✓ | – |
| 3 | role | Badge (color-coded per role) | – | – | ✓ (multi-select) |
| 4 | last_login_at | Relative time ("2 hours ago" / "منذ ساعتين") | ✓ | – | – |
| 5 | status | Badge (green "Active" / gray "Disabled") | – | – | ✓ |
| 6 | created_at | Date format per locale | ✓ | – | – |

**Row Actions (per-row kebab menu, inline-end column):**

1. "Edit" — navigates to Screen 5
2. "Reset Password" — opens modal
3. "Disable" / "Re-enable" — opens confirmation modal; visible based on row's current status

**Bulk Actions:** None in Wave 14.1.

**Empty State:** Per US-004 edge case — "should not happen" message.

**Pagination:** Filament cursor pagination, 25 rows per page.

### Screen 4: Admin Users — Create

**Form Schema using Filament v5.6 `Schema::make()` API.**

**Three sections (top to bottom):**

1. **Identity** section
   - Full Name (required, max 100)
   - Email (required, unique on `admin_users.email`, max 255, RFC+DNS validated)

2. **Access** section
   - Role (required, Filament Select with 4 options: Super Admin, Support, Finance, Read Only)
   - Locale (required, radio: العربية / English, default العربية)

3. **Initial Password** section
   - Password (required, min 12 chars, complexity rules)
   - Confirm Password (required, must match)

**Actions:** Cancel (back to list) | Create Admin User (primary, inline-end)

### Screen 5: Admin Users — Edit

Identical to Screen 4 EXCEPT:

- "Initial Password" section replaced by "Password" section containing only a "Reset Password" button (opens modal)
- New "Status" section at bottom: current status badge + Disable/Re-enable button per current state
- Current admin cannot disable themselves or demote themselves from super_admin if they are the last super_admin — UI shows button as disabled with tooltip
- Email field editable but triggers re-confirmation modal

**Reset Password Modal — Initial step:**

```
┌─────────────────────────────────────────────────────┐
│  Reset password for Jane Doe                        │
│  ─────────────────────────────────────────────────  │
│  Click "Generate Password" to create a new          │
│  random 16-character password. The current          │
│  password will be invalidated immediately.          │
│                                                     │
│  [ Cancel ]              [ Generate Password ]      │
└─────────────────────────────────────────────────────┘
```

**Reset Password Modal — Result step:**

```
┌─────────────────────────────────────────────────────┐
│  New password generated                             │
│  ─────────────────────────────────────────────────  │
│  ⚠️  This password will not be shown again.         │
│      Copy it now.                                   │
│                                                     │
│  ┌──────────────────────────────────────┐          │
│  │ k9$Lq3@Mn7pZxR2v               [📋]  │          │
│  └──────────────────────────────────────┘          │
│                                                     │
│  Share this with the admin via a secure channel.   │
│                              [ Done ]               │
└─────────────────────────────────────────────────────┘
```

### Screen 6: Activity Log — List

**No "Create" button (append-only).**

**Table Columns:**

| Order | Column Key | Display | Sortable | Filterable |
|---|---|---|---|---|
| 1 | occurred_at | Datetime + relative tooltip ("2 hours ago" → "Jun 24, 2026 14:32") | ✓ (desc default) | ✓ (date range) |
| 2 | admin_user | Composite: name + email (small) | ✓ | ✓ (select admin) |
| 3 | event_type | Badge with semantic color | – | ✓ (multi-select enum) |
| 4 | target | Polymorphic display ("Admin: jane@x.com" or "Self") | – | – |
| 5 | ip_address | Monospace | – | – |
| 6 | payload_preview | Truncated to 120 chars + "View" link to Screen 7 | – | – |

**Row Actions:** Only "View". No edit, no delete.

**Filters above table:** Event Type (multi-select), Date Range, Admin User. All filter state in URL query string.

### Screen 7: Activity Log — View

**Three sections:**

1. **Event Summary** — Event Type (badge), Occurred At, Admin User, Target, IP Address, User Agent (truncated, hover for full)
2. **Payload** — formatted JSON viewer (`<pre><code>` with syntax highlighting); JSON always LTR regardless of panel locale
3. **Related Events** — table showing 10 events before + 10 after this one, scoped to same `admin_user_id`

**No actions** — view is terminal.

### Screen 8: User Menu Dropdown (overlay component)

Filament top-bar render hook injecting custom Blade view. Anchored inline-end of header; opens as popover below avatar.

```
┌────────────────────────────────┐
│  Jane Doe                      │  ← name (semibold)
│  jane@example.com              │  ← email (small, muted)
│  [ Super Admin ]               │  ← role badge
│  ─────────────────────────     │
│  🌐  Switch Language           │  ← US-006
│  🚪  Sign Out                  │  ← US-005
└────────────────────────────────┘
```

Avatar is a circular div with admin's initials (e.g., "JD") on deterministic-color background derived from email hash. No avatar image upload in Wave 14.1.

## 3.4 Responsive Behavior

| Viewport | Behavior |
|---|---|
| **Desktop ≥ 1280px** | Sidebar permanently expanded (240px), dashboard stats 4-column grid, tables all columns, forms single-column 720px centered |
| **Tablet 768–1279px** | Sidebar collapsible (hamburger), dashboard stats 2-column (2x2), tables priority columns + horizontal scroll, forms full-width with 24px gutter |
| **Mobile <768px** | Sidebar hidden by default behind hamburger, dashboard stats 1-column stack, tables collapse to card view, forms full-width single column, modals become bottom sheets |

**Mobile-specific behaviors:**
- Login card becomes full-width with 16px gutter; max-width dropped on viewports < 480px
- User menu dropdown becomes a bottom sheet
- Activity log JSON viewer scrollable in both axes with explicit "expand fullscreen" affordance

**Not optimized for mobile:** Internal admin panel. Mobile is supported (no broken layouts) but not the primary target.

## 3.5 RTL (Arabic) Layout Behavior

Filament v5.6 supports RTL via Tailwind's logical properties (`ps-*`, `pe-*`, `ms-*`, `me-*`, `start-*`, `end-*`).

**Universal RTL rules:**

1. **Layout direction:** `<html dir="rtl">` flips the entire UI; sidebar moves to right edge, content area to left
2. **Text alignment:** all text inherits document direction; `text-start` and `text-end` used instead of `text-left` / `text-right`
3. **Icons:** directional icons (chevrons, arrows, breadcrumb separators, back arrows) flip horizontally via `transform: scaleX(-1)` when `dir="rtl"`; non-directional icons (gear, avatar, magnifier) do NOT flip
4. **Numbers:** Western Arabic numerals (0–9), NOT Eastern (٠–٩) — per Khaldoun input (Jordan legal/business context)
5. **Dates:** Gregorian only; no Hijri toggle in Wave 14.1
6. **Currency:** USD displayed as `$1,234.56` in both locales; `$` always precedes amount
7. **Mixed-direction inputs:** Email `dir="auto"`; Password `dir="ltr"` regardless of page locale
8. **Form submit buttons:** anchored inline-end; "Cancel" anchored inline-start
9. **Tables:** column visual order reverses in RTL; sort indicators do not flip
10. **Modals:** close button (X) inline-end; primary action button inline-end

**Pest Browser Plugin test coverage:** Visual regression screenshots at 1280px viewport in both `dir="rtl"` and `dir="ltr"` for each of the 8 screens (16 screenshots) committed to `tests/Browser/__snapshots__/admin/`. CI diff-checks future renders against these baselines.

---

# 4. API CONTRACTS

## 4.1 Scope statement

**Wave 14.1 introduces zero public REST API endpoints.** This is a deliberate architectural choice:

1. Admin panel is browser-only (Livewire-driven, not REST)
2. No external consumer exists; YAGNI
3. Stripe webhooks are NOT in this Wave (Wave 14.6)
4. Admin authentication is session-based, not token-based

This section documents what the AO actually needs: Livewire request lifecycle, admin route map, OpenAPI spec implications (none), deferred API surfaces (forward-looking), test endpoint coverage, auth/authz, and the database schema (the equivalent contract).

## 4.2 Internal Livewire Endpoint (Filament-managed)

| Property | Value |
|---|---|
| Method | `POST` |
| Path | `/livewire/update` |
| Handler | `Livewire\Mechanisms\HandleRequests\HandleRequests::handleUpdate()` |
| Middleware (admin context) | `web`, `auth:admin`, `admin.session.idle`, `admin.session.absolute` |
| Authentication | Session cookie via `admin` guard |
| CSRF | Required (Laravel `VerifyCsrfToken` middleware enforced) |

**AO must:**
1. Register the Filament admin panel in `app/Providers/Filament/AdminPanelProvider.php` with the `admin` auth guard
2. Add two custom middleware to the admin panel's middleware stack: `App\Http\Middleware\Admin\EnforceIdleTimeout` and `App\Http\Middleware\Admin\EnforceAbsoluteTimeout`
3. Ensure these middleware run AFTER `auth:admin`

## 4.3 Admin Panel Route Map

Routes registered via the Filament panel configuration, not in `routes/web.php` or `routes/api.php`. Effective route table:

| Method | Path | Name | Guard | Middleware | Handler | User Story |
|---|---|---|---|---|---|---|
| GET | `/admin/login` | `filament.admin.auth.login` | `admin` (no-auth) | `web`, `panel:admin` | `App\Filament\Admin\Pages\Auth\Login` | US-001 |
| POST | `/admin/login` | `filament.admin.auth.login` | `admin` (no-auth) | `web`, `panel:admin`, `throttle:admin-login` | `App\Filament\Admin\Pages\Auth\Login@authenticate` (Livewire action) | US-001 |
| POST | `/admin/logout` | `filament.admin.auth.logout` | `admin` | `web`, `auth:admin`, `panel:admin` | Filament default `LogoutController` | US-005 |
| GET | `/admin` | `filament.admin.pages.dashboard` | `admin` | (admin stack) | `App\Filament\Admin\Pages\Dashboard` | US-002 |
| GET | `/admin/admin-users` | `filament.admin.resources.admin-users.index` | `admin` | (admin stack) | `ListAdminUsers` | US-004 |
| GET | `/admin/admin-users/create` | `filament.admin.resources.admin-users.create` | `admin` | (admin stack) | `CreateAdminUser` | US-003 |
| GET | `/admin/admin-users/{record}/edit` | `filament.admin.resources.admin-users.edit` | `admin` | (admin stack) | `EditAdminUser` | US-004 |
| GET | `/admin/activity-log` | `filament.admin.resources.admin-activity-log.index` | `admin` | (admin stack) | `ListAdminActivityLogs` | US-007 |
| GET | `/admin/activity-log/{record}` | `filament.admin.resources.admin-activity-log.view` | `admin` | (admin stack) | `ViewAdminActivityLog` | US-007 |
| POST | `/livewire/update` | `livewire.update` | (context) | `web`, `auth:admin` (when admin-originated), CSRF | Livewire framework | All interactive actions |

**"(admin stack)" expands to:** `web`, `auth:admin`, `panel:admin`, `admin.session.idle`, `admin.session.absolute`

## 4.4 Rate Limiter Configuration

In `app/Providers/AppServiceProvider.php` `boot()`:

```php
RateLimiter::for('admin-login', function (Request $request) {
    return [
        Limit::perMinute(5)->by($request->ip()),
        Limit::perMinute(10)->by($request->input('email') ?? $request->ip()),
    ];
});
```

Implements US-001: 5 failed attempts per 60s per IP triggers HTTP 429.

## 4.5 Middleware Implementation Specifications

**File: `app/Http/Middleware/Admin/EnforceIdleTimeout.php`**

| Aspect | Specification |
|---|---|
| Trigger | Every authenticated admin request |
| Logic | If `now() - session('admin.last_activity_at') > config('admin.session.idle_minutes')` minutes, invalidate session, write `admin.session.expired` event, redirect to `/admin/login?reason=expired` |
| Side effect on pass | Updates `session('admin.last_activity_at')` to `now()` |
| Config key | `config('admin.session.idle_minutes')`, default 60 |
| Test file | `tests/Feature/Admin/Middleware/EnforceIdleTimeoutTest.php` |

**File: `app/Http/Middleware/Admin/EnforceAbsoluteTimeout.php`**

| Aspect | Specification |
|---|---|
| Trigger | Every authenticated admin request |
| Logic | If `now() - session('admin.session_started_at') > config('admin.session.absolute_hours')` hours, invalidate session, write `admin.session.expired` with `reason: 'absolute'`, redirect to `/admin/login?reason=expired` |
| Side effect on pass | None (`session_started_at` is set once at login) |
| Config key | `config('admin.session.absolute_hours')`, default 12 |
| Test file | `tests/Feature/Admin/Middleware/EnforceAbsoluteTimeoutTest.php` |

## 4.6 Configuration File

**File: `config/admin.php`** (NEW — must be created in Wave 14.1)

| Key | Type | Default | Source | Used By |
|---|---|---|---|---|
| `admin.session.idle_minutes` | int | 60 | `env('ADMIN_SESSION_IDLE_MINUTES', 60)` | `EnforceIdleTimeout` |
| `admin.session.absolute_hours` | int | 12 | `env('ADMIN_SESSION_ABSOLUTE_HOURS', 12)` | `EnforceAbsoluteTimeout` |
| `admin.password.min_length` | int | 12 | `env('ADMIN_PASSWORD_MIN_LENGTH', 12)` | FormRequest validation |
| `admin.password.reset.length` | int | 16 | constant | Random password generator |
| `admin.locale.default` | string | `'ar'` | constant | Admin user creation default |
| `admin.locale.supported` | array | `['ar', 'en']` | constant | Locale validation |

## 4.7 OpenAPI Spec Implications

| OpenAPI File | Path | Wave 14.1 Changes |
|---|---|---|
| Public API spec | `openapi/spec.yaml` | **No changes.** Wave 14.1 adds zero public API endpoints |
| Admin internal spec | `openapi/admin.yaml` | **Does not exist and will not be created in Wave 14.1** |

**Explicit instruction to AO:** Do not modify `openapi/spec.yaml` during Wave 14.1. Do not create `openapi/admin.yaml`. A diff to either file during this Wave is a defect.

## 4.8 Deferred API Surfaces (Future Waves)

| Wave | Endpoint | Method | Path | Purpose |
|---|---|---|---|---|
| 14.5 | Provision Stripe customer | (internal job, no public route) | — | Called on workspace activation transition |
| 14.6 | Stripe webhook receiver | `POST` | `/webhooks/stripe` | Stripe → platform; signature-verified, no auth middleware, idempotent |
| 14.7 | Entitlement check (internal service, not REST) | — | — | `SubscriptionEntitlementService` — called from FormRequests in firm-facing API |
| 14.8 | Usage snapshot trigger | `POST` | `/admin/workspaces/{workspace}/recalculate-usage` | Manual recompute; `admin` guard, super_admin only |
| 14.9 | Initiate PDPL purge | `POST` | `/admin/workspaces/{workspace}/purge` | Triggers job; `admin` guard, super_admin only |

**Constraint imposed on Wave 14.1:** Use Filament's standard route registration via Resources rather than ad-hoc `Route::post()` in `routes/web.php`. The webhook (14.6) will live OUTSIDE the admin panel, in `routes/web.php`, because it must not require auth.

## 4.9 Test Endpoint Coverage Matrix

| Test File | Routes Covered | User Stories |
|---|---|---|
| `tests/Feature/Admin/Auth/LoginTest.php` | `GET /admin/login`, `POST /admin/login` | US-001 |
| `tests/Feature/Admin/Auth/LogoutTest.php` | `POST /admin/logout` | US-005 |
| `tests/Feature/Admin/Auth/SessionTimeoutTest.php` | All authenticated routes via middleware | US-005 |
| `tests/Feature/Admin/Auth/GuardIsolationTest.php` | `POST /admin/login` w/ users creds; `POST /app/login` w/ admin_users creds | US-001, M-5 |
| `tests/Feature/Admin/Auth/RateLimitTest.php` | `POST /admin/login` × 6 attempts in 60s | US-001 |
| `tests/Feature/Admin/Pages/DashboardTest.php` | `GET /admin` | US-002 |
| `tests/Feature/Admin/Resources/AdminUserResourceTest.php` | All 3 admin-user routes | US-003, US-004 |
| `tests/Feature/Admin/Resources/AdminActivityLogResourceTest.php` | Both activity-log routes | US-007 |
| `tests/Feature/Admin/LocaleSwitchTest.php` | Locale toggle action via Livewire | US-006 |
| `tests/Feature/Admin/Middleware/EnforceIdleTimeoutTest.php` | Middleware unit-level | US-005 |
| `tests/Feature/Admin/Middleware/EnforceAbsoluteTimeoutTest.php` | Middleware unit-level | US-005 |
| `tests/Browser/Admin/RtlSnapshotTest.php` | All 8 screens × 2 locales | M-7 |

**Pest syntax (per CLAUDE.md):** All tests use Pest 4.7 syntax with `it()` / `test()` / `expect()`. PHPUnit syntax not acceptable.

**Total estimated test count for Wave 14.1:** 60–80 individual tests across these 12 files.

## 4.10 Authentication & Authorization

### Authentication

| Aspect | Specification |
|---|---|
| Guard name | `admin` |
| Provider | `admin_users` (Eloquent) |
| Model | `App\Models\AdminUser` |
| Driver | `session` (cookie-based) |
| Password hashing | `bcrypt` (Laravel default, cost 12) |
| Session cookie name | `platform_admin_session` |
| Session cookie path | `/admin` |
| Session cookie SameSite | `Strict` |
| Session cookie Secure | `true` in production, `false` in local |
| Session driver | `database` (sessions table; admin sessions distinguishable by `guard` column — add migration) |

### Authorization Policies

| Policy Class | Path | Methods |
|---|---|---|
| `AdminUserPolicy` | `app/Policies/AdminUserPolicy.php` | `viewAny`, `view`, `create`, `update`, `disable`, `resetPassword` |
| `AdminActivityLogPolicy` | `app/Policies/AdminActivityLogPolicy.php` | `viewAny`, `view` (no create/update/delete by design) |

### Policy Behavior Matrix

| Action | super_admin | support | finance | read_only |
|---|---|---|---|---|
| `AdminUser@viewAny` | ✓ | ✗ | ✗ | ✗ |
| `AdminUser@view` | ✓ | ✗ | ✗ | ✗ |
| `AdminUser@create` | ✓ | ✗ | ✗ | ✗ |
| `AdminUser@update` | ✓ (w/ self-demotion + last-super-admin guards) | ✗ | ✗ | ✗ |
| `AdminUser@disable` | ✓ (cannot disable self if last super_admin) | ✗ | ✗ | ✗ |
| `AdminUser@resetPassword` | ✓ | ✗ | ✗ | ✗ |
| `AdminActivityLog@viewAny` | ✓ | ✓ | ✓ | ✓ |
| `AdminActivityLog@view` | ✓ | ✓ | ✓ | ✓ |
| Dashboard view | ✓ | ✓ | ✓ | ✓ |
| Locale switch (own) | ✓ | ✓ | ✓ | ✓ |

### Filament Resource Policy Bindings

In `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    \App\Models\AdminUser::class         => \App\Policies\AdminUserPolicy::class,
    \App\Models\AdminActivityLog::class  => \App\Policies\AdminActivityLogPolicy::class,
];
```

The Resource classes must call corresponding policy methods via `canViewAny()`, `canCreate()`, etc.

## 4.11 Data Model — Schema Diff for Wave 14.1

### New Table: `admin_users`

Migration: `php artisan make:migration create_admin_users_table`
File: `database/migrations/2026_06_24_000001_create_admin_users_table.php`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | `bigIncrements` | PK | |
| `name` | `string(100)` | not null | Display name |
| `email` | `string(255)` | not null, unique | RFC + DNS validated at FormRequest level |
| `email_verified_at` | `timestamp` | nullable | Currently unused; reserved for future invite-flow Wave |
| `password` | `string(255)` | not null | bcrypt hash |
| `role` | `string(20)` | not null | Enum: `super_admin`, `support`, `finance`, `read_only` — enforced via PHP enum + DB CHECK |
| `locale` | `string(2)` | not null, default `'ar'` | Enum: `ar`, `en` |
| `last_login_at` | `timestamp` | nullable | Updated on each successful login |
| `disabled_at` | `timestamp` | nullable | Soft-disable; admin cannot log in if not null |
| `remember_token` | `string(100)` | nullable | Laravel default |
| `created_at` | `timestamp` | nullable | |
| `updated_at` | `timestamp` | nullable | |

Indexes: `unique(email)`, `index(disabled_at)` for queries excluding disabled users.

### New Table: `admin_activity_log`

Migration: `php artisan make:migration create_admin_activity_log_table`
File: `database/migrations/2026_06_24_000002_create_admin_activity_log_table.php`

**APPEND-ONLY per CLAUDE.md ledger rules. No `updated_at`. No soft deletes. Corrections via new offsetting events only.**

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | `bigIncrements` | PK | |
| `admin_user_id` | `unsignedBigInteger` | nullable, FK to `admin_users.id` | Nullable because failed-login events have no authenticated admin yet |
| `attempted_email` | `string(255)` | nullable | Recorded for failed login events; null otherwise |
| `event_type` | `string(60)` | not null | Enum from `App\Enums\AdminActivityEventType` |
| `target_type` | `string(100)` | nullable | Polymorphic morph type |
| `target_id` | `unsignedBigInteger` | nullable | Polymorphic morph id |
| `payload` | `json` | not null, default `'{}'` | Event-specific data; passwords never included |
| `ip_address` | `string(45)` | not null | IPv4 or IPv6 |
| `user_agent` | `text` | nullable | Browser UA string |
| `occurred_at` | `timestamp` | not null | Application-set, not DB-set |
| `created_at` | `timestamp` | not null | DB-set ingestion time |

Indexes: `index(admin_user_id, occurred_at)`, `index(event_type, occurred_at)`, `index(occurred_at)`. No `updated_at` index because column does not exist.

### Modification to existing `sessions` table

Migration: `php artisan make:migration add_guard_to_sessions_table`
File: `database/migrations/2026_06_24_000003_add_guard_to_sessions_table.php`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `guard` | `string(20)` | nullable, default `null` | Backfill existing rows with `'web'`; new admin sessions set to `'admin'` |

If `sessions` table does not yet exist (file driver active), migration sequence is: enable database session driver → run Laravel's session migration → add this guard column. The migration's `down()` method must drop the column safely.

### Seeders

**File: `database/seeders/AdminUserSeeder.php`**

Idempotent seeder that creates the first super_admin only if zero rows exist in `admin_users`. Reads credentials from `.env`:

| Env Var | Purpose |
|---|---|
| `ADMIN_SEED_EMAIL` | First super_admin email |
| `ADMIN_SEED_NAME` | First super_admin display name |
| `ADMIN_SEED_PASSWORD` | First super_admin password (min 12 chars validated by seeder) |

If env vars missing in production environment, seeder throws `RuntimeException` with message "ADMIN_SEED_* env vars required for first deploy. Refusing to seed insecure default." The seeder must NEVER fall back to a hard-coded default password.

In local/testing environment, env vars default to:
- `ADMIN_SEED_EMAIL=admin@local.test`
- `ADMIN_SEED_NAME=Local Admin`
- `ADMIN_SEED_PASSWORD=<generated random and printed once to console on first seed>`

Seeder is registered in `database/seeders/DatabaseSeeder.php` after any other foundational seeders.

## 4.12 Hard requirements (non-negotiable)

1. **`admin_activity_log` is append-only.** No `updated_at`. No `deleted_at`. Any "correction" must be a new event row with `event_type = 'admin.activity_log.correction'` referencing the prior row's id in payload.

2. **No Sanctum tokens for admins.** Admin authentication is session-only. The AO must not call `createToken()` on AdminUser. AdminUser must NOT use the `HasApiTokens` trait.

3. **Distinct session cookie.** `platform_admin_session` is separate from any cookie used by the workspace (`web`) guard.

4. **No password in audit payload.** Ever. Pest test required: `AuditLogPasswordLeakageTest.php`.

5. **Seeder refuses insecure defaults in production.** If `app()->environment('production')` and any of `ADMIN_SEED_*` is missing, seeder throws.


---

# 5. CONTENT SPECIFICATION

## 5.1 Localization File Structure

| Locale | File Path | Encoding | Direction |
|---|---|---|---|
| Arabic (default) | `resources/lang/ar/admin.php` | UTF-8 (no BOM) | RTL |
| English (secondary) | `resources/lang/en/admin.php` | UTF-8 (no BOM) | LTR |

**Lookup pattern:** `__('admin.section.key')` everywhere in admin panel Blade/PHP. Hard-coded strings are defects (validated by `tests/Feature/Admin/NoHardcodedStringsTest.php`).

**Key structure:**

```
admin.panel.*           — panel chrome, page titles
admin.auth.signIn.*     — login screen (US-001)
admin.auth.signOut.*    — logout (US-005)
admin.auth.session.*    — session expiry messages
admin.dashboard.*       — dashboard (US-002)
admin.users.*           — admin user CRUD (US-003, US-004)
admin.users.passwordReset.* — reset password modal
admin.users.status.*    — status badges and actions
admin.activityLog.*     — activity log (US-007)
admin.activityLog.events.* — event type display labels
admin.userMenu.*        — user menu dropdown (US-005, US-006)
admin.locale.*          — language switch (US-006)
admin.common.*          — shared strings (Cancel, Save, etc.)
admin.errors.*          — error toasts and inline errors
```

## 5.2 Screen 1: Admin Login

**Page metadata:**

| Key | Arabic (default) | English |
|---|---|---|
| Page title (browser tab) | تسجيل دخول المسؤول — منصة eFirm | Admin Sign In — eFirm Platform |
| `<html dir>` | `rtl` | `ltr` |
| `<html lang>` | `ar` | `en` |

**Layout elements:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Heading | `admin.auth.signIn.heading` | تسجيل دخول المسؤول | Admin Sign In |
| Subheading | `admin.auth.signIn.subheading` | للوصول الداخلي إلى المنصة فقط | Internal platform access only |
| Email label | `admin.auth.signIn.emailLabel` | البريد الإلكتروني | Email |
| Email placeholder | `admin.auth.signIn.emailPlaceholder` | name@example.com | name@example.com |
| Password label | `admin.auth.signIn.passwordLabel` | كلمة المرور | Password |
| Password placeholder | `admin.auth.signIn.passwordPlaceholder` | (empty) | (empty) |
| Show password toggle (aria) | `admin.auth.signIn.showPasswordAria` | إظهار كلمة المرور | Show password |
| Hide password toggle (aria) | `admin.auth.signIn.hidePasswordAria` | إخفاء كلمة المرور | Hide password |
| Submit button (default) | `admin.auth.signIn.submit` | تسجيل الدخول | Sign In |
| Submit button (loading) | `admin.auth.signIn.submitting` | جارٍ تسجيل الدخول… | Signing in… |

**Validation messages:**

| Scenario | Localization Key | Arabic | English |
|---|---|---|---|
| Email empty | `admin.auth.signIn.errors.emailRequired` | البريد الإلكتروني مطلوب. | Email is required. |
| Email invalid format | `admin.auth.signIn.errors.emailInvalid` | يُرجى إدخال بريد إلكتروني صالح. | Enter a valid email address. |
| Password empty | `admin.auth.signIn.errors.passwordRequired` | كلمة المرور مطلوبة. | Password is required. |
| Wrong credentials (generic) | `admin.auth.signIn.errors.invalidCredentials` | البريد الإلكتروني أو كلمة المرور غير صحيحة. | Email or password is incorrect. |
| Account disabled | `admin.auth.signIn.errors.accountDisabled` | تم تعطيل هذا الحساب. يُرجى التواصل مع مسؤول أعلى. | This account has been deactivated. Contact a super-admin. |
| Rate-limited | `admin.auth.signIn.errors.rateLimited` | عدد كبير جداً من المحاولات. حاول مرة أخرى بعد :seconds ثانية. | Too many attempts. Try again in :seconds seconds. |
| Network failure | `admin.auth.signIn.errors.networkError` | خطأ في الشبكة. حاول مرة أخرى. | Network error. Try again. |
| Session expired (URL query reason=expired) | `admin.auth.session.expired` | انتهت صلاحية جلستك. يُرجى تسجيل الدخول مرة أخرى. | Your session expired. Sign in again. |

## 5.3 Screen 2: Admin Dashboard

**Page metadata:**

| Key | Arabic | English |
|---|---|---|
| Page title (browser tab) | لوحة التحكم — إدارة المنصة | Dashboard — Platform Admin |

**Panel chrome:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Panel name | `admin.panel.name` | إدارة المنصة | Platform Admin |
| Panel tagline | `admin.panel.tagline` | الإدارة الداخلية لمنصة eFirm | Internal eFirm platform administration |

**Sidebar navigation:**

| Item | Localization Key | Arabic | English | Visible in 14.1? |
|---|---|---|---|---|
| Dashboard | `admin.nav.dashboard` | لوحة التحكم | Dashboard | ✓ |
| Admin Users | `admin.nav.adminUsers` | المسؤولون | Admin Users | ✓ (super_admin only) |
| Activity Log | `admin.nav.activityLog` | سجل النشاط | Activity Log | ✓ |
| Workspaces | `admin.nav.workspaces` | مساحات العمل | Workspaces | Wave 14.3 |
| Plans | `admin.nav.plans` | الخطط | Plans | Wave 14.2 |
| Subscriptions | `admin.nav.subscriptions` | الاشتراكات | Subscriptions | Wave 14.4 |

**Navigation group labels:**

| Group | Localization Key | Arabic | English |
|---|---|---|---|
| Customers (Wave 14.3+) | `admin.nav.groups.customers` | العملاء | Customers |
| Billing (Wave 14.2+) | `admin.nav.groups.billing` | الفوترة | Billing |

**Dashboard stat cards:**

| Card | Localization Key | Arabic | English |
|---|---|---|---|
| 1 — Label | `admin.dashboard.stats.totalWorkspaces.label` | إجمالي مساحات العمل | Total Workspaces |
| 1 — Helper | `admin.dashboard.stats.totalWorkspaces.helper` | يتم التحديث عند توفر بيانات الاشتراك | Updated when subscription data is available |
| 2 — Label | `admin.dashboard.stats.activeSubscriptions.label` | الاشتراكات النشطة | Active Subscriptions |
| 2 — Helper | `admin.dashboard.stats.activeSubscriptions.helper` | يتم التحديث عند توفر بيانات الاشتراك | Updated when subscription data is available |
| 3 — Label | `admin.dashboard.stats.mrr.label` | الإيرادات الشهرية المتكررة (دولار أمريكي) | MRR (USD) |
| 3 — Helper | `admin.dashboard.stats.mrr.helper` | يتم التحديث عند توفر بيانات الاشتراك | Updated when subscription data is available |
| 3 — Value format | (USD always prefix) | $0.00 | $0.00 |
| 4 — Label | `admin.dashboard.stats.trialsExpiringWeek.label` | التجارب المنتهية هذا الأسبوع | Trials Expiring This Week |
| 4 — Helper | `admin.dashboard.stats.trialsExpiringWeek.helper` | يتم التحديث عند توفر بيانات الاشتراك | Updated when subscription data is available |

**Card error state:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Error value | `admin.dashboard.stats.errorValue` | — | — |
| Error tooltip | `admin.dashboard.stats.errorTooltip` | تعذّر التحميل. يُرجى التواصل مع فريق الهندسة. | Unable to load. Contact engineering. |

## 5.4 Screen 3: Admin Users — List

**Page metadata:**

| Key | Arabic | English |
|---|---|---|
| Page title | المسؤولون — إدارة المنصة | Admin Users — Platform Admin |
| Page heading | `admin.users.list.heading` → المسؤولون | Admin Users |
| Page subheading | `admin.users.list.subheading` → إدارة وصول الفريق إلى لوحة الإدارة | Manage team access to the admin panel |
| Breadcrumb | لوحة التحكم ‹ المسؤولون | Dashboard › Admin Users |

**Table column headers:**

| Column | Localization Key | Arabic | English |
|---|---|---|---|
| Name | `admin.users.columns.name` | الاسم | Name |
| Email | `admin.users.columns.email` | البريد الإلكتروني | Email |
| Role | `admin.users.columns.role` | الدور | Role |
| Last Login | `admin.users.columns.lastLogin` | آخر تسجيل دخول | Last Login |
| Status | `admin.users.columns.status` | الحالة | Status |
| Created | `admin.users.columns.created` | تاريخ الإنشاء | Created |

**Role badge labels:**

| Role | Localization Key | Arabic | English | Badge Color |
|---|---|---|---|---|
| super_admin | `admin.users.roles.superAdmin` | مسؤول أعلى | Super Admin | Purple |
| support | `admin.users.roles.support` | الدعم | Support | Blue |
| finance | `admin.users.roles.finance` | المالية | Finance | Green |
| read_only | `admin.users.roles.readOnly` | قراءة فقط | Read Only | Gray |

**Status badges:**

| Status | Localization Key | Arabic | English | Badge Color |
|---|---|---|---|---|
| Active | `admin.users.status.active` | نشط | Active | Green |
| Disabled | `admin.users.status.disabled` | معطّل | Disabled | Gray |

**"Last Login" relative time format:**

Carbon `diffForHumans()` with locale-aware output. "Never" key: `admin.users.lastLogin.never` → لم يسجّل الدخول بعد / Never

**Table actions:**

| Action | Localization Key | Arabic | English |
|---|---|---|---|
| Header — New button | `admin.users.actions.create` | مسؤول جديد | New Admin User |
| Row — Edit | `admin.users.actions.edit` | تعديل | Edit |
| Row — Reset Password | `admin.users.actions.resetPassword` | إعادة تعيين كلمة المرور | Reset Password |
| Row — Disable | `admin.users.actions.disable` | تعطيل | Disable |
| Row — Re-enable | `admin.users.actions.reEnable` | إعادة التفعيل | Re-enable |
| Row — Actions menu (aria) | `admin.users.actions.menuAria` | إجراءات | Actions |

**Filter labels:**

| Filter | Localization Key | Arabic | English |
|---|---|---|---|
| Role filter | `admin.users.filters.role` | الدور | Role |
| Status filter | `admin.users.filters.status` | الحالة | Status |
| Search placeholder | `admin.users.filters.searchPlaceholder` | البحث بالاسم أو البريد الإلكتروني | Search by name or email |

**Empty state (US-004 "should not happen"):**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Heading | `admin.users.empty.heading` | لا يوجد مسؤولون | No admin users found |
| Description | `admin.users.empty.description` | يُرجى التواصل مع فريق الهندسة — يجب ألا تحدث هذه الحالة. | Contact engineering — this should not happen. |

## 5.5 Screen 4: Admin Users — Create

**Form section headings:**

| Section | Localization Key | Arabic | English |
|---|---|---|---|
| Identity | `admin.users.form.sections.identity` | الهوية | Identity |
| Access | `admin.users.form.sections.access` | الوصول | Access |
| Initial Password | `admin.users.form.sections.initialPassword` | كلمة المرور الأولية | Initial Password |

**Identity fields:**

| Field | Localization Key | Arabic | English |
|---|---|---|---|
| Name label | `admin.users.form.name.label` | الاسم الكامل | Full Name |
| Name placeholder | `admin.users.form.name.placeholder` | مثال: أحمد محمد | e.g. Jane Doe |
| Name helper | `admin.users.form.name.helper` | مطلوب • ١٠٠ حرف كحد أقصى | Required • Max 100 characters |
| Name error (required) | `admin.users.form.name.errors.required` | الاسم مطلوب. | Name is required. |
| Name error (max) | `admin.users.form.name.errors.max` | يجب ألا يتجاوز الاسم ١٠٠ حرف. | Name must be 100 characters or fewer. |
| Email label | `admin.users.form.email.label` | البريد الإلكتروني | Email |
| Email placeholder | `admin.users.form.email.placeholder` | name@example.com | name@example.com |
| Email helper | `admin.users.form.email.helper` | مطلوب • يجب أن يكون فريداً بين المسؤولين | Required • Must be unique among admin users |
| Email error (required) | `admin.users.form.email.errors.required` | البريد الإلكتروني مطلوب. | Email is required. |
| Email error (invalid) | `admin.users.form.email.errors.invalid` | يُرجى إدخال بريد إلكتروني صالح. | Enter a valid email address. |
| Email error (duplicate) | `admin.users.form.email.errors.duplicate` | يوجد مسؤول بهذا البريد الإلكتروني بالفعل. | An admin with this email already exists. |
| Email also-in-users notice | `admin.users.form.email.alsoInUsers` | ملاحظة: هذا البريد مسجل أيضاً كمستخدم لمساحة عمل. | Note: this email is also registered as a workspace user. |

**Access fields:**

| Field | Localization Key | Arabic | English |
|---|---|---|---|
| Role label | `admin.users.form.role.label` | الدور | Role |
| Role placeholder | `admin.users.form.role.placeholder` | اختر دوراً | Select a role |
| Role helper | `admin.users.form.role.helper` | يحدد ما يمكن لهذا المسؤول رؤيته وتعديله | Determines what this admin can see and edit |
| Role error (required) | `admin.users.form.role.errors.required` | الدور مطلوب. | Role is required. |
| Locale label | `admin.users.form.locale.label` | اللغة | Locale |
| Locale helper | `admin.users.form.locale.helper` | يحدد لغة واتجاه قراءة لوحة هذا المسؤول | Determines the language and reading direction of this admin's panel |
| Locale option — Arabic | `admin.locale.ar` | العربية | العربية |
| Locale option — English | `admin.locale.en` | English | English |

**Note on locale option labels:** Both display in their native language regardless of current page locale.

**Initial Password fields:**

| Field | Localization Key | Arabic | English |
|---|---|---|---|
| Password label | `admin.users.form.password.label` | كلمة المرور | Password |
| Password helper | `admin.users.form.password.helper` | الحد الأدنى ١٢ حرفاً • أحرف كبيرة وصغيرة + رقم + رمز | Min 12 chars • upper + lower + number + symbol |
| Password error (required) | `admin.users.form.password.errors.required` | كلمة المرور مطلوبة. | Password is required. |
| Password error (min) | `admin.users.form.password.errors.min` | يجب أن تتكون كلمة المرور من ١٢ حرفاً على الأقل. | Password must be at least 12 characters. |
| Password error (complexity) | `admin.users.form.password.errors.complexity` | يجب أن تحتوي كلمة المرور على أحرف كبيرة وصغيرة ورقم ورمز. | Password must include uppercase, lowercase, a number, and a symbol. |
| Password error (max) | `admin.users.form.password.errors.max` | يجب ألا تتجاوز كلمة المرور ١٢٨ حرفاً. | Password must be 128 characters or fewer. |
| Confirm Password label | `admin.users.form.passwordConfirmation.label` | تأكيد كلمة المرور | Confirm Password |
| Confirm Password helper | `admin.users.form.passwordConfirmation.helper` | يجب أن تطابق كلمة المرور أعلاه | Must match the password above |
| Confirm Password error (mismatch) | `admin.users.form.passwordConfirmation.errors.mismatch` | تأكيد كلمة المرور لا يطابق. | Password confirmation does not match. |

**Form actions:**

| Action | Localization Key | Arabic | English |
|---|---|---|---|
| Cancel | `admin.common.cancel` | إلغاء | Cancel |
| Create | `admin.users.form.actions.create` | إنشاء المسؤول | Create Admin User |
| Create (loading) | `admin.users.form.actions.creating` | جارٍ الإنشاء… | Creating… |

**Success toast:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Toast title | `admin.users.create.success.title` | تم إنشاء حساب المسؤول | Admin user created |
| Toast body | `admin.users.create.success.body` | يمكنك الآن مشاركة كلمة المرور مع المسؤول عبر قناة آمنة. | Share the password with the admin via a secure channel. |

**Error toast (DB write failure):**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Toast title | `admin.users.create.failure.title` | تعذّر الإنشاء | Could not create |
| Toast body | `admin.users.create.failure.body` | تعذّر إنشاء المسؤول. يُرجى المحاولة مرة أخرى أو التواصل مع فريق الهندسة. | Could not create admin. Try again or contact engineering. |

## 5.6 Screen 5: Admin Users — Edit

Strings inherit from Screen 4. Edit-specific additions:

**Page metadata:**

| Key | Arabic | English |
|---|---|---|
| Page title | `:name` — المسؤولون | `:name` — Admin Users |
| Page heading | `admin.users.edit.heading` → تعديل المسؤول | Edit Admin User |

**Password section (replaces "Initial Password"):**

| Field | Localization Key | Arabic | English |
|---|---|---|---|
| Section heading | `admin.users.form.sections.password` | كلمة المرور | Password |
| Reset Password button | `admin.users.passwordReset.openModal` | إعادة تعيين كلمة المرور | Reset Password |
| Helper text | `admin.users.passwordReset.helperText` | إنشاء كلمة مرور عشوائية جديدة. ستُعرض مرة واحدة فقط. | Generate a new random password. Shown only once. |

**Status section:**

| Field | Localization Key | Arabic | English |
|---|---|---|---|
| Section heading | `admin.users.form.sections.status` | الحالة | Status |
| Currently active label | `admin.users.status.currentlyActive` | الحساب نشط حالياً | Account is currently active |
| Currently disabled label | `admin.users.status.currentlyDisabled` | الحساب معطّل منذ :date | Account has been disabled since :date |
| Disable button | `admin.users.actions.disable` | تعطيل الحساب | Disable Account |
| Re-enable button | `admin.users.actions.reEnable` | إعادة تفعيل الحساب | Re-enable Account |

**Self-protection messages:**

| Scenario | Localization Key | Arabic | English |
|---|---|---|---|
| Cannot demote self | `admin.users.errors.cannotDemoteSelf` | لا يمكنك تخفيض دورك. يُرجى الطلب من مسؤول أعلى آخر. | You cannot demote yourself. Ask another super-admin. |
| Cannot disable self | `admin.users.errors.cannotDisableSelf` | لا يمكنك تعطيل حسابك الخاص. | You cannot disable your own account. |
| Last super_admin invariant | `admin.users.errors.lastSuperAdmin` | يجب أن يتوفر مسؤول أعلى نشط واحد على الأقل في جميع الأوقات. | At least one active super-admin must exist at all times. |
| Self-demotion tooltip (disabled button) | `admin.users.errors.selfDemoteTooltip` | لا يمكنك تعديل دورك الخاص. | You cannot change your own role. |

**Email change re-confirmation modal:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Modal heading | `admin.users.emailChange.heading` | تأكيد تغيير البريد الإلكتروني | Confirm Email Change |
| Modal body | `admin.users.emailChange.body` | تغيير البريد الإلكتروني سيتطلب من المسؤول استخدام العنوان الجديد عند تسجيل الدخول التالي. هل تريد المتابعة؟ | Changing email will require this admin to use the new address at next login. Continue? |
| Confirm button | `admin.users.emailChange.confirm` | متابعة | Continue |

**Concurrent edit conflict:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Alert title | `admin.users.errors.concurrentEdit.title` | تم تعديل هذا السجل | Record was modified |
| Alert body | `admin.users.errors.concurrentEdit.body` | تم تعديل هذا السجل أثناء قيامك بالتحرير. يُرجى إعادة التحميل لمشاهدة التغييرات. | This record was modified while you were editing. Reload to see changes. |
| Reload button | `admin.users.errors.concurrentEdit.reload` | إعادة التحميل | Reload |

**Form save success:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Toast title | `admin.users.edit.success.title` | تم الحفظ | Saved |
| Toast body | `admin.users.edit.success.body` | تم تحديث بيانات المسؤول. | Admin user updated. |

**Disable confirmation modal:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Heading | `admin.users.disable.heading` | تعطيل :name؟ | Disable :name? |
| Body | `admin.users.disable.body` | لن يتمكن هذا المسؤول من تسجيل الدخول حتى يتم إعادة التفعيل. ستُلغى أي جلسات نشطة فوراً. | This admin will not be able to sign in until re-enabled. Active sessions will be invalidated immediately. |
| Confirm | `admin.users.disable.confirm` | تعطيل | Disable |
| Success toast title | `admin.users.disable.success.title` | تم التعطيل | Disabled |
| Success toast body | `admin.users.disable.success.body` | تم تعطيل :name وإلغاء جلساتهم. | :name has been disabled and their sessions invalidated. |

**Re-enable confirmation modal:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Heading | `admin.users.reEnable.heading` | إعادة تفعيل :name؟ | Re-enable :name? |
| Body | `admin.users.reEnable.body` | سيتمكن هذا المسؤول من تسجيل الدخول مرة أخرى باستخدام بيانات الاعتماد الحالية. | This admin will be able to sign in again with their current credentials. |
| Confirm | `admin.users.reEnable.confirm` | إعادة التفعيل | Re-enable |
| Success toast title | `admin.users.reEnable.success.title` | تم إعادة التفعيل | Re-enabled |
| Success toast body | `admin.users.reEnable.success.body` | تم إعادة تفعيل :name. | :name has been re-enabled. |

## 5.7 Reset Password Modal

**Initial confirmation step:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Heading | `admin.users.passwordReset.confirm.heading` | إعادة تعيين كلمة المرور لـ :name | Reset password for :name |
| Body | `admin.users.passwordReset.confirm.body` | انقر "إنشاء كلمة المرور" لإنشاء كلمة مرور عشوائية جديدة من ١٦ حرفاً. سيتم إلغاء صلاحية كلمة المرور الحالية فوراً. | Click "Generate Password" to create a new random 16-character password. The current password will be invalidated immediately. |
| Generate | `admin.users.passwordReset.confirm.generate` | إنشاء كلمة المرور | Generate Password |

**Result step:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Heading | `admin.users.passwordReset.result.heading` | تم إنشاء كلمة المرور الجديدة | New password generated |
| Warning | `admin.users.passwordReset.result.warning` | لن يتم عرض كلمة المرور هذه مرة أخرى. انسخها الآن. | This password will not be shown again. Copy it now. |
| Hint | `admin.users.passwordReset.result.hint` | شارك هذه مع المسؤول عبر قناة آمنة. | Share this with the admin via a secure channel. |
| Copy button (aria) | `admin.users.passwordReset.result.copyAria` | نسخ كلمة المرور | Copy password |
| Copied toast | `admin.users.passwordReset.result.copied` | تم النسخ إلى الحافظة | Copied to clipboard |
| Done button | `admin.users.passwordReset.result.done` | تم | Done |

**Notes:** Password value always LTR (`dir="ltr"` forced); monospace font; readonly + text-select-all on click.

## 5.8 Screen 6: Activity Log — List

**Page metadata:**

| Key | Arabic | English |
|---|---|---|
| Page title | سجل النشاط — إدارة المنصة | Activity Log — Platform Admin |
| Page heading | `admin.activityLog.list.heading` | سجل النشاط | Activity Log |
| Page subheading | `admin.activityLog.list.subheading` | السجل الكامل لجميع إجراءات المسؤولين. للقراءة فقط. | Complete record of all admin actions. Read-only. |

**Table columns:**

| Column | Localization Key | Arabic | English |
|---|---|---|---|
| Timestamp | `admin.activityLog.columns.occurredAt` | التوقيت | When |
| Admin | `admin.activityLog.columns.adminUser` | المسؤول | Admin |
| Event | `admin.activityLog.columns.eventType` | الحدث | Event |
| Target | `admin.activityLog.columns.target` | الهدف | Target |
| IP Address | `admin.activityLog.columns.ipAddress` | عنوان IP | IP Address |
| Payload | `admin.activityLog.columns.payload` | البيانات | Payload |

**Event type display labels (12 events in Wave 14.1):**

| Event Type Code | Localization Key | Arabic | English | Badge Color |
|---|---|---|---|---|
| `admin.login.success` | `admin.activityLog.events.login.success` | تسجيل دخول ناجح | Sign-in success | Green |
| `admin.login.failed` | `admin.activityLog.events.login.failed` | محاولة تسجيل دخول فاشلة | Sign-in failed | Red |
| `admin.login.blocked_disabled` | `admin.activityLog.events.login.blockedDisabled` | تسجيل دخول محظور (معطّل) | Sign-in blocked (disabled) | Red |
| `admin.login.rate_limited` | `admin.activityLog.events.login.rateLimited` | تسجيل دخول محدود (معدّل) | Sign-in rate-limited | Yellow |
| `admin.logout` | `admin.activityLog.events.logout` | تسجيل خروج | Sign-out | Neutral |
| `admin.session.expired` | `admin.activityLog.events.session.expired` | انتهاء الجلسة | Session expired | Neutral |
| `admin.user.created` | `admin.activityLog.events.user.created` | إنشاء مسؤول | Admin created | Blue |
| `admin.user.updated` | `admin.activityLog.events.user.updated` | تحديث مسؤول | Admin updated | Blue |
| `admin.user.password_reset` | `admin.activityLog.events.user.passwordReset` | إعادة تعيين كلمة المرور | Password reset | Yellow |
| `admin.user.disabled` | `admin.activityLog.events.user.disabled` | تعطيل مسؤول | Admin disabled | Red |
| `admin.user.reenabled` | `admin.activityLog.events.user.reenabled` | إعادة تفعيل مسؤول | Admin re-enabled | Green |
| `admin.locale.changed` | `admin.activityLog.events.locale.changed` | تغيير اللغة | Locale changed | Neutral |

**Target display formats:**

| Target Type | Arabic template | English template |
|---|---|---|
| `App\Models\AdminUser` (other) | مسؤول: :email | Admin: :email |
| `App\Models\AdminUser` (self) | الذات | Self |
| No target | — | — |

**Timestamp display:**

| Field | Arabic Format | English Format |
|---|---|---|
| Default cell | منذ :time | :time ago |
| Tooltip on hover | ٢٤ يونيو ٢٠٢٦ ١٤:٣٢ | Jun 24 2026 14:32 |

**Note:** Despite Arabic locale, numerals remain Western (0–9). Month name uses calendar transliteration ("يونيو") not Levantine ("حزيران") per Khaldoun input.

**Filters:**

| Filter | Localization Key | Arabic | English |
|---|---|---|---|
| Event Type filter | `admin.activityLog.filters.eventType` | نوع الحدث | Event Type |
| Date range — from | `admin.activityLog.filters.dateFrom` | من تاريخ | From |
| Date range — to | `admin.activityLog.filters.dateTo` | إلى تاريخ | To |
| Admin User filter | `admin.activityLog.filters.adminUser` | المسؤول | Admin User |
| Reset filters | `admin.activityLog.filters.reset` | مسح المرشحات | Reset Filters |

**Empty state:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Heading | `admin.activityLog.empty.heading` | لم يتم تسجيل أي نشاط بعد | No activity logged yet |
| Description | `admin.activityLog.empty.description` | ستظهر الأحداث عند استخدام المسؤولين للوحة. | Events will appear as admins use the panel. |

**Query timeout edge case:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Alert | `admin.activityLog.errors.queryTimeout` | استغرق الاستعلام وقتاً طويلاً. يُرجى تضييق نطاق التصفية. | Query took too long. Narrow your filters. |

## 5.9 Screen 7: Activity Log — View

**Page metadata:**

| Key | Arabic | English |
|---|---|---|
| Page title | تفاصيل الحدث — سجل النشاط | Event Details — Activity Log |
| Page heading | `admin.activityLog.view.heading` | تفاصيل الحدث | Event Details |

**Section headings:**

| Section | Localization Key | Arabic | English |
|---|---|---|---|
| Event summary | `admin.activityLog.view.sections.summary` | ملخص الحدث | Event Summary |
| Payload | `admin.activityLog.view.sections.payload` | البيانات | Payload |
| Related events | `admin.activityLog.view.sections.related` | الأحداث ذات الصلة | Related Events |

**Summary field labels:**

| Field | Localization Key | Arabic | English |
|---|---|---|---|
| Event type | `admin.activityLog.view.fields.eventType` | نوع الحدث | Event Type |
| Occurred at | `admin.activityLog.view.fields.occurredAt` | حدث في | Occurred At |
| Admin user | `admin.activityLog.view.fields.adminUser` | المسؤول | Admin User |
| Target | `admin.activityLog.view.fields.target` | الهدف | Target |
| IP address | `admin.activityLog.view.fields.ipAddress` | عنوان IP | IP Address |
| User agent | `admin.activityLog.view.fields.userAgent` | وكيل المستخدم | User Agent |

**Related events panel:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Caption | `admin.activityLog.view.related.caption` | عشرة أحداث قبل وعشرة بعد لنفس المسؤول | Ten events before and ten after for the same admin |
| Empty state | `admin.activityLog.view.related.empty` | لا توجد أحداث أخرى لهذا المسؤول. | No other events for this admin. |
| Current event marker | `admin.activityLog.view.related.thisEvent` | (الحدث الحالي) | (this event) |

## 5.10 Screen 8: User Menu Dropdown

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Avatar aria | `admin.userMenu.avatarAria` | قائمة المستخدم | User menu |
| Switch Language item | `admin.userMenu.switchLanguage` | تبديل اللغة | Switch Language |
| Switch Language to AR (when EN active) | `admin.userMenu.switchToArabic` | التبديل إلى العربية | Switch to العربية |
| Switch Language to EN (when AR active) | `admin.userMenu.switchToEnglish` | Switch to English | Switch to English |
| Sign Out item | `admin.userMenu.signOut` | تسجيل الخروج | Sign Out |
| Sign Out (in progress) | `admin.userMenu.signingOut` | جارٍ تسجيل الخروج… | Signing out… |

**Sign-out success toast:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Toast | `admin.auth.signOut.success` | تم تسجيل الخروج | Signed out |

**Locale change result:**

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Success | (no toast — page reloads) | — | — |
| Error toast title | `admin.locale.errors.changeFailedTitle` | تعذّر تغيير اللغة | Could not change language |
| Error toast body | `admin.locale.errors.changeFailedBody` | تعذّر تغيير اللغة. حاول مرة أخرى. | Could not change language. Try again. |

## 5.11 Common / Shared Strings

| Element | Localization Key | Arabic | English |
|---|---|---|---|
| Cancel | `admin.common.cancel` | إلغاء | Cancel |
| Save | `admin.common.save` | حفظ | Save |
| Save changes | `admin.common.saveChanges` | حفظ التغييرات | Save Changes |
| Saving | `admin.common.saving` | جارٍ الحفظ… | Saving… |
| Delete | `admin.common.delete` | حذف | Delete |
| Confirm | `admin.common.confirm` | تأكيد | Confirm |
| Back | `admin.common.back` | رجوع | Back |
| Loading | `admin.common.loading` | جارٍ التحميل… | Loading… |
| Yes | `admin.common.yes` | نعم | Yes |
| No | `admin.common.no` | لا | No |
| OK | `admin.common.ok` | حسناً | OK |
| Close | `admin.common.close` | إغلاق | Close |
| View | `admin.common.view` | عرض | View |
| Search | `admin.common.search` | بحث | Search |
| Filters | `admin.common.filters` | المرشحات | Filters |
| No results | `admin.common.noResults` | لا توجد نتائج | No results |
| Required indicator (aria) | `admin.common.requiredFieldAria` | حقل مطلوب | required field |

## 5.12 Character Limits & Input Constraints

| Field | Min | Max | Validation Rule (Laravel) |
|---|---|---|---|
| `admin_users.name` | 1 | 100 | `'required|string|min:1|max:100'` |
| `admin_users.email` | 3 | 255 | `'required|email:rfc,dns|max:255|unique:admin_users,email'` |
| `admin_users.password` (create) | 12 | 128 | `[Password::min(12)->letters()->mixedCase()->numbers()->symbols(), 'max:128']` |
| `admin_users.role` | — | — | `['required', Rule::enum(AdminRole::class)]` |
| `admin_users.locale` | 2 | 2 | `['required', 'in:ar,en']` |
| Activity log filter — search | 0 | 100 | `'nullable|string|max:100'` |

## 5.13 Arabic Translation Conventions

1. **Modern Standard Arabic (MSA)**, not Levantine dialect
2. **Arabic punctuation:** `،` (comma), `؟` (question mark), `؛` (semicolon) — NOT Latin equivalents
3. **Western numerals (0–9)**, not Eastern Arabic (٠–٩) — per Khaldoun input
4. **No automatic feminization** for system messages; generic masculine default
5. **Imperative verbs for action buttons** (e.g., "إنشاء" not "الإنشاء")
6. **":name" placeholders** preserve Laravel localization syntax; NOT translated

**Flagged for Khaldoun (non-blocking):** "Super Admin" → "مسؤول أعلى" vs alternative "مدير عام". Defaulting to "مسؤول أعلى". Change in future is a single-key update.

## 5.14 Hard requirements for the AO

1. **No hard-coded user-facing strings.** Every visible string in admin panel Blade/PHP must come from `__('admin.*')`. Validated by `tests/Feature/Admin/NoHardcodedStringsTest.php`.

2. **Arabic file is source of truth for default locale.** When adding new strings in future Waves, add to AR first then EN.

3. **Translation keys versioned with code.** Both `ar/admin.php` and `en/admin.php` must have identical key structures. Asserted by `tests/Feature/Admin/LocaleKeyParityTest.php`.

4. **":name" interpolation placeholders are reserved.** Use Laravel's `__('key', ['name' => $value])` substitution. No string concatenation.


---

# 6. EDGE CASES & ERROR HANDLING

82 edge cases across 8 categories. 14 are Quality Gate blockers (Section 6.11). 8 are explicitly deferred (Section 6.12).

## 6.1 Authentication & Session Edge Cases

| # | Scenario | Trigger | Expected Behavior | UI Response | Audit Event | Test File |
|---|---|---|---|---|---|---|
| AUTH-01 | Wrong password for existing admin | Valid email + wrong password | Reject with generic "invalid credentials" (no enumeration); increment rate-limit | Inline error: `admin.auth.signIn.errors.invalidCredentials`; password cleared; email preserved | `admin.login.failed` w/ `payload.reason='invalid_password'`, `attempted_email` populated | `LoginTest.php::it_rejects_wrong_password_with_generic_message` |
| AUTH-02 | Email matches `users` not `admin_users` | Firm-user credentials submitted | Reject identically to AUTH-01; do not reveal account exists elsewhere | Same as AUTH-01 | `admin.login.failed` w/ `payload.reason='no_admin_record'` | `LoginTest.php::it_rejects_workspace_user_credentials_without_enumeration` |
| AUTH-03 | Disabled admin attempts login | Valid creds for admin w/ `disabled_at IS NOT NULL` | Reject with explicit disabled message | Inline error: `admin.auth.signIn.errors.accountDisabled` | `admin.login.blocked_disabled` | `LoginTest.php::it_blocks_disabled_admin_with_explicit_message` |
| AUTH-04 | Rate limit triggered | 5 failed attempts in 60s, then 6th | HTTP 429 from `RateLimiter::for('admin-login')`; retry-after header | Inline error: `admin.auth.signIn.errors.rateLimited` w/ `:seconds` | `admin.login.rate_limited` written once per window | `RateLimitTest.php::it_returns_429_on_sixth_attempt_in_60_seconds` |
| AUTH-05 | Concurrent rate-limit by email AND IP | Same email different IPs / different emails same IP | Both limiters apply (5/min IP + 10/min email); first to trigger wins | Same as AUTH-04 | `admin.login.rate_limited` w/ `payload.limited_by` = `'ip'` or `'email'` | `RateLimitTest.php::it_applies_both_ip_and_email_limiters` |
| AUTH-06 | Idle timeout reached mid-session | Last activity > 60min, then any request | `EnforceIdleTimeout` invalidates session before handler runs; HTTP 401 then redirect | Login page: `admin.auth.session.expired`; intended URL preserved in `?intended=` | `admin.session.expired` w/ `payload.reason='idle'`, `payload.intended_url` | `EnforceIdleTimeoutTest.php::it_invalidates_session_after_60_minute_idle` |
| AUTH-07 | Absolute timeout reached | `session_started_at` > 12h regardless of activity | `EnforceAbsoluteTimeout` invalidates session; HTTP 401 redirect | Same as AUTH-06 | `admin.session.expired` w/ `payload.reason='absolute'` | `EnforceAbsoluteTimeoutTest.php::it_invalidates_session_after_12_hours` |
| AUTH-08 | Admin disabled while session active | Super-admin disables Admin B while B has active session | Next request from B's session returns HTTP 401; session forcibly invalidated as part of disable action | B sees login w/ `admin.auth.signIn.errors.accountDisabled` if re-tries | `admin.session.terminated` w/ `payload.reason='admin_disabled'`, `payload.terminated_by_admin_user_id` | `AdminUserResourceTest.php::it_terminates_disabled_admin_active_sessions` |
| AUTH-09 | Logout server-side failure | Logout request fails (session driver unavailable) | Client clears local session cookie defensively; redirects to login | Same as successful logout; no error toast — graceful degradation | None client-side | `LogoutTest.php::it_clears_local_state_on_server_failure` |
| AUTH-10 | Cross-guard at workspace login | Admin tries `/app/login` with `admin_users` creds | `web` guard rejects (no matching `users` row); standard workspace login error | Standard workspace login error from SURGE-01 | None in `admin_activity_log` | `GuardIsolationTest.php::it_rejects_admin_credentials_at_workspace_login` |
| AUTH-11 | Cross-guard at admin login | Firm user tries `/admin/login` with `users` creds | Same as AUTH-02 | Same as AUTH-02 | `admin.login.failed` w/ `payload.reason='no_admin_record'` | `GuardIsolationTest.php::it_rejects_workspace_credentials_at_admin_login` |
| AUTH-12 | Session cookie tampering | Modified session cookie sent to `/admin/*` | Laravel detects invalid signature; treats as unauthenticated; redirect | Standard redirect to login | (not written for unauthenticated requests) | `SessionCookieTest.php::it_rejects_tampered_session_cookies` |
| AUTH-13 | Empty `admin_users` table | First deploy before seeder runs | Login form renders normally; all attempts fail | Standard invalid-credentials error | `admin.login.failed` w/ `payload.reason='no_admin_record'` | `LoginTest.php::it_handles_empty_admin_users_table` |
| AUTH-14 | Authenticated admin navigates to `/admin/login` | Already-logged-in admin visits login URL | Redirect to `/admin/dashboard` | No login form shown | None | `LoginTest.php::it_redirects_authenticated_admin_to_dashboard` |

## 6.2 Authorization & Policy Edge Cases

| # | Scenario | Trigger | Expected Behavior | UI Response | Audit Event | Test File |
|---|---|---|---|---|---|---|
| AUTHZ-01 | `support` accesses `/admin/admin-users` | Non-super_admin navigates to URL directly | `AdminUserPolicy::viewAny()` returns false; HTTP 403 | Filament default 403; sidebar item hidden via `shouldRegisterNavigation()` | None | `AdminUserResourceTest.php::it_returns_403_for_support_role` |
| AUTHZ-02 | `finance`/`read_only` accesses admin users CRUD | Direct URL access | Same as AUTHZ-01 | Same as AUTHZ-01 | None | `AdminUserResourceTest.php::it_returns_403_for_finance_and_readonly_roles` |
| AUTHZ-03 | Sidebar nav correctness per role | Login as each role | Sidebar shows only items role can access | super_admin: Dashboard, Admin Users, Activity Log. Others: Dashboard, Activity Log only | None | `NavigationVisibilityTest.php::it_hides_admin_users_nav_for_non_super_admin` |
| AUTHZ-04 | Policy method exists but model binding missing | Future Wave Resource added but `AuthServiceProvider::$policies` not updated | `Gate::policy()` returns null; default-deny via Filament's resolution | 403 | (forward-looking) | (Not in 14.1; future Waves) |
| AUTHZ-05 | Locale switch via direct URL manipulation | POST with locale outside `['ar','en']` | FormRequest validation rejects with 422 | Toast: `admin.locale.errors.changeFailedBody` | None | `LocaleSwitchTest.php::it_rejects_unsupported_locale_values` |

## 6.3 Admin User CRUD Edge Cases

| # | Scenario | Trigger | Expected Behavior | UI Response | Audit Event | Test File |
|---|---|---|---|---|---|---|
| CRUD-01 | Duplicate email on create | Submit create with existing email | FormRequest `unique:admin_users,email` fails; 422 | Inline error: `admin.users.form.email.errors.duplicate` | None | `AdminUserResourceTest.php::it_rejects_duplicate_email_on_create` |
| CRUD-02 | Email exists in `users` (firm) | Submit create w/ email matching firm user (not admin) | Create succeeds; success toast includes `admin.users.form.email.alsoInUsers` notice | Toast w/ workspace-user notice | `admin.user.created` w/ `payload.email_also_in_users=true` | `AdminUserResourceTest.php::it_allows_create_when_email_exists_in_users_table_with_notice` |
| CRUD-03 | Self-demotion attempt by lone super_admin | Only super_admin changes own role to support | FormRequest rejects w/ `admin.users.errors.lastSuperAdmin` | Inline error at form top; role field reverts | None | `AdminUserResourceTest.php::it_blocks_last_super_admin_self_demotion` |
| CRUD-04 | Self-demotion when other super_admins exist | A super_admin demotes self while another exists | Validation rejects w/ `admin.users.errors.cannotDemoteSelf` (regardless of count) | Inline error; role reverts | None | `AdminUserResourceTest.php::it_blocks_self_demotion_even_when_other_super_admins_exist` |
| CRUD-05 | Self-disable attempt | Any admin tries to disable own account | FormRequest rejects w/ `admin.users.errors.cannotDisableSelf`; button also hidden in UI | Button disabled w/ tooltip; backend reject if URL manipulated | None | `AdminUserResourceTest.php::it_blocks_self_disable` |
| CRUD-06 | Last super_admin disable attempt | Lone super_admin reaches disable endpoint | FormRequest rejects w/ `admin.users.errors.lastSuperAdmin` | Same as CRUD-03 | None | `AdminUserResourceTest.php::it_blocks_disable_of_last_super_admin` |
| CRUD-07 | Demotion of last super_admin by another super_admin | Direct DB/tinker write | Defense-in-depth: Pest test asserts DB has ≥1 active super_admin at all times | N/A | None at app layer | `AdminUsersInvariantTest.php::database_always_has_at_least_one_active_super_admin` |
| CRUD-08 | Password reset for own account | Super_admin resets own password | Allowed but with warning "You will be signed out after the next request"; on confirm, password resets, current session invalidated | Modal w/ additional warning; redirect to login | `admin.user.password_reset` w/ `payload.self_reset=true` | `PasswordResetTest.php::it_invalidates_own_session_on_self_password_reset` |
| CRUD-09 | Password complexity rejection | 12-char password missing symbol | Laravel `Password` rule fails; 422 | Inline error: `admin.users.form.password.errors.complexity` | None | `AdminUserResourceTest.php::it_rejects_password_missing_complexity_requirement` |
| CRUD-10 | Password length boundary (12, 128 exactly) | Submit at limits | Both accepted; <12 or >128 rejected | Field-level error if rejected | `admin.user.created` on success | `AdminUserResourceTest.php::it_accepts_password_at_boundary_lengths` |
| CRUD-11 | Email change re-confirmation | Edit admin and change email | Re-confirmation modal appears; on cancel revert; on confirm save proceeds | Modal per Section 5.6 spec | `admin.user.updated` w/ `payload.changes.email` (post-confirmation only) | `AdminUserResourceTest.php::it_requires_confirmation_on_email_change` |
| CRUD-12 | Email change bypassing modal (URL manipulation) | Direct POST skipping modal | Modal is UX only; save proceeds | If user manipulates form, change still applies | `admin.user.updated` w/ `payload.changes.email` | (Covered by CRUD-11 happy path) |
| CRUD-13 | Reset Password ambiguous characters | Generator must exclude `0`/`O`, `1`/`l` etc. | Use charset `abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%^&*` | Display unaffected | `admin.user.password_reset` (no password in payload) | `PasswordResetTest.php::it_generates_passwords_excluding_ambiguous_characters` |
| CRUD-14 | Reset Password modal closed before generation | User clicks Cancel before Generate | No password generated; no state change | No toast | None | `PasswordResetTest.php::it_does_not_generate_on_cancel` |
| CRUD-15 | Reset Password modal abandoned after generation | User generates but closes without copy | Password already written to DB; old password no longer works | No special UX | `admin.user.password_reset` already written | `PasswordResetTest.php::it_writes_password_on_generate_regardless_of_modal_close` |
| CRUD-16 | Bulk action attempted via crafted URL | Submit bulk-delete | Wave 14.1 registers no bulk actions; 404 or 403 | No UI affordance | None | `AdminUserResourceTest.php::it_does_not_expose_bulk_actions` |
| CRUD-17 | Filament Delete button absence | Confirm no Delete action exists | `canDelete()` returns false; AdminUser never hard-deleted | "Delete" button absent; direct URL 403s | None | `AdminUserResourceTest.php::it_does_not_allow_deletion_of_admin_users` |

## 6.4 Data Integrity & Concurrency Edge Cases

| # | Scenario | Trigger | Expected Behavior | UI Response | Audit Event | Test File |
|---|---|---|---|---|---|---|
| DATA-01 | Concurrent edit of same admin | A and B both open edit for User X; A saves first; B saves second | Optimistic locking via `updated_at`: B's save rejected with HTTP 409 | Alert: `admin.users.errors.concurrentEdit.*` + Reload button | `admin.user.update_conflict` w/ `payload.attempted_by_admin_user_id`, `payload.stale_updated_at` | `AdminUserResourceTest.php::it_rejects_stale_updates_with_409` |
| DATA-02 | DB connection drop during create | MySQL times out during INSERT | `QueryException`; transaction rollback; no partial row | Top-of-form alert: `admin.users.create.failure.body`; form values preserved (passwords cleared) | None | `AdminUserResourceTest.php::it_recovers_form_state_on_db_failure` |
| DATA-03 | DB connection drop during audit log write | Audit log fails after primary action succeeds | Action proceeds; audit log failure logged to Laravel error log but does NOT roll back the action | User sees normal success; engineering monitor catches log failure | Laravel `error` log: "Audit log write failed for action: X" | `AuditLogResilienceTest.php::it_completes_action_on_audit_log_write_failure` |
| DATA-04 | Race: two super_admins disable each other | Last two super_admins both submit disable simultaneously | DB-level check (CRUD-07 invariant) catches; first commit succeeds; second fails | Loser sees `admin.users.errors.lastSuperAdmin` | Winner: `admin.user.disabled`; Loser: `admin.user.disable_blocked_invariant` | `AdminUsersInvariantTest.php::it_handles_concurrent_disable_attempts` |
| DATA-05 | `admin_activity_log` row w/ `admin_user_id = NULL` | Failed-login events have no admin context | Schema allows nullable `admin_user_id`; `attempted_email` populated | Activity log renders "—" or "(unauthenticated)" in Admin column | (these events generate the row) | `ActivityLogResourceTest.php::it_renders_unauthenticated_events_correctly` |
| DATA-06 | Soft-deleted admin in queries | Admin disabled (NOT deleted) — `disabled_at` not `deleted_at` | List page applies no auto filter; both active and disabled appear; Status column distinguishes | Default view shows both; Status filter defaults to "Active" so disabled hidden unless filtered | None | `AdminUserResourceTest.php::it_shows_disabled_admins_when_filter_includes_them` |
| DATA-07 | Migration rollback for `sessions.guard` | `down()` drops column when other code depends | Destructive by design; AO must warn in migration docblock | N/A | None | (Documented; not in CI) |

## 6.5 Activity Log Edge Cases

| # | Scenario | Trigger | Expected Behavior | UI Response | Audit Event | Test File |
|---|---|---|---|---|---|---|
| LOG-01 | Activity log query timeout | Filter combination exceeds `max_execution_time` (5s) | Query interrupted; `QueryException`; Filament catches & shows graceful message | Alert: `admin.activityLog.errors.queryTimeout`; filter UI preserved | None | `ActivityLogResourceTest.php::it_shows_timeout_message_on_long_queries` |
| LOG-02 | Filtered to zero results | Filter matches nothing | Standard Filament "no results" empty state | `admin.common.noResults` | None | `ActivityLogResourceTest.php::it_shows_no_results_for_unmatched_filters` |
| LOG-03 | Payload > 64KB | Future Wave generates huge payload | List view truncates to 200 chars; detail loads full via lazy fetch | "Payload too large — view full" link | None | (Deferred — not a 14.1 concern) |
| LOG-04 | Related Events on isolated event | Admin has < 10 events before and < 10 after | Panel shows only existing events; no padding | Renders available events w/ "(this event)" marker | None | `ActivityLogResourceTest.php::it_handles_related_events_with_sparse_history` |
| LOG-05 | Related Events for unauthenticated events | Event w/ `admin_user_id IS NULL` | Panel renders empty (no admin context to scope by) | `admin.activityLog.view.related.empty` | None | `ActivityLogResourceTest.php::it_shows_empty_related_panel_for_null_admin_events` |
| LOG-06 | Create/edit/delete via crafted URL | POST/PUT/DELETE to activity log endpoint | Resource has no Create/Edit/Delete page registered; 404 | No UI affordance | None | `ActivityLogResourceTest.php::it_does_not_expose_write_actions` |
| LOG-07 | Filter state w/ invalid enum values | URL `?event_type[]=nonexistent.event` | Silently drops invalid; valid values still applied | Filter chips show only valid selections | None | `ActivityLogResourceTest.php::it_ignores_invalid_filter_values_gracefully` |
| LOG-08 | Timestamp ties | Two events in same millisecond | Secondary sort by `id DESC` for determinism | Deterministic row order | None | `ActivityLogResourceTest.php::it_uses_id_as_tiebreaker_for_identical_timestamps` |

## 6.6 Locale & RTL Edge Cases

| # | Scenario | Trigger | Expected Behavior | UI Response | Audit Event | Test File |
|---|---|---|---|---|---|---|
| LOC-01 | Missing translation key | `__('admin.foo.bar')` exists in `en` but missing in `ar` | Laravel falls back per `config('app.fallback_locale')`; if both missing, raw key returned | Key string visible; CI fails build via parity test | None | `LocaleKeyParityTest.php::it_asserts_identical_key_structures_between_locales` |
| LOC-02 | Locale switch DB write failure | User clicks Switch Language; DB update fails | Page reload against existing locale (no change); error toast | Toast: `admin.locale.errors.changeFailedBody` | None | `LocaleSwitchTest.php::it_shows_error_toast_on_db_failure` |
| LOC-03 | Database-corrupted locale | Direct DB tampering: `admin_users.locale = 'fr'` | Validate against `config('admin.locale.supported')`; fall back to `'ar'`; log warning | Panel renders in Arabic; Laravel log warning | None | `LocaleSwitchTest.php::it_falls_back_to_arabic_for_invalid_stored_locale` |
| LOC-04 | RTL text overflow in name | Arabic 100-char name in narrow column | CSS `text-overflow: ellipsis` truncates inline-end; full visible on hover | Tooltip shows full text | None | (Visual regression) `tests/Browser/Admin/RtlTableOverflowTest.php` |
| LOC-05 | Mixed-direction admin name | Name contains both scripts (e.g. "Khaldoun خلدون") | Browser's `dir="auto"` handles per cell; chars stay in native direction | Rendered per browser bidi algorithm | None | (Visual regression) `tests/Browser/Admin/RtlMixedDirectionTest.php` |
| LOC-06 | Western numerals in Arabic locale | Render any number in Arabic page | Numerals are Western (0–9) per Khaldoun input | Consistent Western numerals | None | `LocaleSwitchTest.php::it_renders_western_numerals_in_arabic_locale` |
| LOC-07 | Locale switch with dirty form | User has unsaved edits; clicks Switch Language | Browser `beforeunload` warning fires; if confirmed, switch + lose unsaved | Browser-native confirmation prompt | `admin.locale.changed` only after confirm | `LocaleSwitchTest.php::it_warns_on_dirty_form_before_locale_change` |
| LOC-08 | `pl-4` vs `ps-4` (LTR-only padding) | Wrong directional class used | Visual regression catches RTL layout breakage; CI fails | Layout misaligned in Arabic | (Detected at PR review) | `tests/Browser/Admin/RtlSnapshotTest.php` |

## 6.7 Infrastructure & Operational Edge Cases

| # | Scenario | Trigger | Expected Behavior | UI Response | Audit Event | Test File |
|---|---|---|---|---|---|---|
| INFRA-01 | Seeder run when admins exist | Re-run `db:seed --class=AdminUserSeeder` | Detects existing rows; logs "Seeder skipped"; exits success | N/A (CLI) | None | `AdminUserSeederTest.php::it_is_idempotent_on_repeat_runs` |
| INFRA-02 | Seeder in production w/o env vars | `APP_ENV=production` and `ADMIN_SEED_EMAIL` missing | `RuntimeException` w/ message about missing env vars; non-zero exit | Build/deploy halts | None | `AdminUserSeederTest.php::it_refuses_to_seed_production_without_env_vars` |
| INFRA-03 | Seeder in local w/o env vars | `APP_ENV=local` w/ missing env vars | Generate random 16-char password; print once to console; use defaults | Console shows password once | None | `AdminUserSeederTest.php::it_generates_password_in_local_without_env_vars` |
| INFRA-04 | Migration on existing DB w/o `sessions.guard` | First-time deploy from older app version | Migration adds column nullable default null; backfill existing rows to `guard='web'`; no data loss | N/A | None | `tests/Feature/Migrations/SessionsGuardColumnTest.php` |
| INFRA-05 | Redis (session driver) unavailable | Redis connection refused | Session driver fallback per Laravel config; default is `database`, so Redis outage doesn't affect sessions | If `database` driver fails: redirect to login | None | (Documented; not in 14.1 tests) |
| INFRA-06 | Filament asset publish missed on deploy | `php artisan filament:assets` not run | Panel renders without CSS/JS; broken UI | Broken visual experience | None | (Runbook concern; deploy checklist) |
| INFRA-07 | Admin from rapidly-changing IPs | Multiple logins w/ different IPs | Allowed (no IP-stickiness in 14.1); each event logs IP at moment | Functional | Multiple `admin.login.success` w/ different `ip_address` | (Future Wave may add) |
| INFRA-08 | Server clock skew | NTP not running on Cloudways | Session timing off by seconds; minimal impact at 60-min/12h granularity | Functional | None | (Operational concern) |
| INFRA-09 | Filament asset cache invalidation | Frontend changes deployed but browser cached old | Vite build fingerprints assets; cache-busting automatic | Functional after cache clear | None | (Operational) |

## 6.8 Security & Abuse Edge Cases

| # | Scenario | Trigger | Expected Behavior | UI Response | Audit Event | Test File |
|---|---|---|---|---|---|---|
| SEC-01 | Password in audit log payload | Any code path that logs during create/reset/login | Sanitization strips `password`, `password_confirmation`, `new_password`, `current_password` keys; CI test asserts no row contains these keys | N/A | N/A | `AuditLogPasswordLeakageTest.php::it_never_writes_password_to_audit_payload` |
| SEC-02 | CSRF token missing on POST | Direct API call without CSRF token | Laravel `VerifyCsrfToken` returns 419 | 419 page (Filament default) | None | `CsrfProtectionTest.php::it_returns_419_for_admin_post_without_csrf` |
| SEC-03 | Session fixation via login | Old session ID reused after login | `Auth::login()` regenerates session ID; old invalidated | Functional | `admin.login.success` (no separate event for regen) | `LoginTest.php::it_regenerates_session_id_on_successful_login` |
| SEC-04 | Concurrent sessions per admin | Same admin logs in from two browsers | Both sessions valid; Wave 14.1 does NOT enforce single-session (deferred) | Both browsers function | Two `admin.login.success` w/ different `session_id` in payload | `LoginTest.php::it_allows_concurrent_sessions_for_same_admin` |
| SEC-05 | Timing attack for user enumeration | Attacker times responses for known vs unknown emails | Both paths execute equivalent work (DB lookup + bcrypt verify even when no record); responses indistinguishable | Identical UI per AUTH-01/AUTH-02 | `admin.login.failed` w/ different `payload.reason` for both | `LoginTest.php::it_executes_bcrypt_even_for_missing_email_to_resist_timing_attack` |
| SEC-06 | XSS in admin name | Super_admin enters `<script>alert(1)</script>` as name | Laravel Blade default escaping renders as literal text; Filament's text columns also escape | Name displayed verbatim as `<script>...` | `admin.user.created` (input just stored) | `XssDefenseTest.php::it_escapes_html_in_admin_name_display` |
| SEC-07 | SQL injection in activity log filter | Filter input contains `' OR 1=1 --` | Eloquent parameterizes; literal string used in `LIKE` | Search produces no results | None | `SqlInjectionDefenseTest.php::it_resists_sql_injection_in_search_filter` |
| SEC-08 | Open redirect via `?intended=` | URL `?intended=https://evil.com` on login | Validate session-stored intended URL is relative; absolute URLs to external domains rejected | After login, lands on dashboard, not evil.com | None | `LoginTest.php::it_rejects_external_intended_redirects` |
| SEC-09 | Session cookie scope leakage | Admin cookie sent to `/app/*` | Cookie path `/admin` prevents browser from sending it to `/app/*` | Functional isolation | None | `SessionCookieTest.php::it_scopes_admin_cookie_to_admin_path_only` |
| SEC-10 | Avatar initials reveal email to all roles | Activity log shows admin email visible to all admin roles | Intentional — all admins see all admins' emails for audit accountability | Functional | None | (Design decision documented) |
| SEC-11 | One-time password in browser history | After Reset Password, user navigates away | Password rendered in DOM but not in URL; browser back-button can re-render. AO must clear password from JS state on modal close | Modal close handler nulls password variable | None at app layer | `PasswordResetTest.php::it_clears_password_from_dom_on_modal_close` |
| SEC-12 | Admin password not rotated long-term | Admin not logged in for 90+ days | No automatic expiry in 14.1 (deferred); super_admin can manually reset | Functional | None | (Future Wave) |
| SEC-13 | Audit log tampering | Admin w/ DB access modifies `admin_activity_log` | Out of scope — 14.1 does not provide tamper-evident audit (no hash chain). Documented limitation | N/A | N/A | (Documented; not blocking 14.1) |
| SEC-14 | Common-password ("Password123!") | Submit password meeting complexity but widely known | 14.1 enforces complexity but does NOT check haveibeenpwned | Password accepted | (Future Wave) | (Documented gap) |

## 6.9 Summary Statistics

| Category | Count |
|---|---|
| Authentication & Session | 14 |
| Authorization & Policy | 5 |
| Admin User CRUD | 17 |
| Data Integrity & Concurrency | 7 |
| Activity Log | 8 |
| Locale & RTL | 8 |
| Infrastructure & Operational | 9 |
| Security & Abuse | 14 |
| **Total** | **82** |

Pest test files introduced: **18**. Edge cases requiring dedicated test: **~62**. Acknowledged out-of-scope: **8**.

## 6.10 Critical Edge Cases (Quality Gate Blockers — must be green before merge)

| # | Edge Case | Why Blocking |
|---|---|---|
| AUTH-01, AUTH-02, AUTH-11 | User enumeration prevention | Direct security regression risk; standard PDPL requirement |
| AUTH-08 | Disabled admin session termination | Disabled admin must lose access immediately, not at next login |
| AUTH-10, AUTH-11 | Cross-guard isolation | M-5 success metric; guard confusion is a privilege escalation surface |
| CRUD-03, CRUD-06, DATA-04 | Last super_admin invariant (three layers) | Unrecoverable lockout if violated |
| CRUD-08, SEC-11 | Password reset session & state hygiene | Password leakage |
| DATA-01 | Optimistic locking | Data integrity for concurrent admin operations |
| SEC-01 | No password in audit payload | Audit log is read-democratically — passwords there are catastrophic |
| SEC-05 | Timing-attack resistance | Standard security posture |
| INFRA-02 | Production seeder safety | Insecure-default-password disaster prevention |

## 6.11 Edge Cases Explicitly Out of Wave 14.1 Scope

| Gap | Deferred To | Rationale |
|---|---|---|
| Tamper-evident audit log (hash chain) | Post-launch hardening Wave | Adds complexity; not required for PDPL minimum |
| Common-password blocklist | Future security Wave | Not standard for internal admin tools |
| Password rotation policy | Future security Wave | Modern NIST advises against scheduled rotation |
| 2FA / TOTP for admins | Future security Wave | High value; `two_factor_secret` column preserved |
| IP anomaly detection | Future security Wave | Logs collected in 14.1; analysis layer separate |
| Single-session enforcement per admin | Future Wave if needed | UX cost outweighs benefit at current scale |
| Email verification on admin invite | Future Wave (when invite flow exists) | Current flow is direct create with password |
| Admin avatar image upload | Out of scope permanently (probably) | Initials-on-color sufficient for internal panel |

## 6.12 Hard requirements for the AO

1. **The 14 critical edge cases in Section 6.10 are Quality Gate blockers.** A PR without green Pest tests for all 14 will be rejected at QG.
2. **SEC-01 is non-negotiable.** A failing `AuditLogPasswordLeakageTest` is a deploy-blocking defect.
3. **CRUD-07 / DATA-04 last super_admin invariant has three enforcement layers** (FormRequest, Policy, Pest test asserting database invariant). All three must exist.
4. **AUTH-10 and AUTH-11 (guard isolation) tests directly assert M-5.** Must run as part of every CI build.
5. **The 8 out-of-scope items in Section 6.11 are NOT defects.** Do not "fix" them in Wave 14.1.

---

# 7. SIGN-OFF LOG

## 7.1 Stakeholder Sign-Off Log

| # | Stakeholder | Role | Approval Scope | Date | Method | Status | Notes |
|---|---|---|---|---|---|---|---|
| 1 | Abdullah Mohammed | Product Owner / Founder | Full package | (pending) | Direct package review | **Pending** | Sole product authority for internal-only admin panel |
| 2 | Designated AO | Engineering Lead | Technical feasibility within Filament v5.6, Laravel 13.16 | (pending) | Pre-Wave technical review (15 min) | **Pending** | Confirm Filament render hook capability, sessions.guard migration safety, two-panel registration |
| 3 | Khaldoun Khater | Lawyer Advisor | N/A for Wave 14.1 | N/A | N/A | **Not required** | Wave 14.1 has zero firm-facing surface; reserved for Waves 14.3 + 14.9 |
| 4 | Tech-Focused Lawyer | SaaS Legal Counsel | N/A for Wave 14.1 | N/A | N/A | **Not required** | Reserved for marketing/signup Waves |
| 5 | Khaldoun Khater | Lawyer Advisor | "Super Admin" Arabic translation alternative | (deferred) | Async text | **Deferred — non-blocking** | Either translation correct; future single-key update |

## 7.2 Pre-Sign-Off Self-Check (Product Owner)

| Section | Confirmation Required | Status |
|---|---|---|
| 1. Intent Definition | 7 success metrics are quantifiable and verifiable post-deploy | ☐ |
| 1. Intent Definition | 4 admin roles match actual headcount and operational structure | ☐ |
| 2. User Stories | Every story's acceptance criteria can be tested boolean by non-author | ☐ |
| 2. User Stories | 7 stories cover full 14.1 scope; no operational scenarios missing | ☐ |
| 3. Wireframes | Path A (spec-first, no Figma) acceptable for internal-only Wave | ☐ |
| 3. Wireframes | 8 screens cover all flows; no orphan screens or missing transitions | ☐ |
| 4. API Contracts | Acceptance of zero public REST endpoints | ☐ |
| 4. API Contracts | 3 database migrations match production DB readiness | ☐ |
| 5. Content Specification | Arabic translations acceptable as MSA w/ Western numerals | ☐ |
| 5. Content Specification | Deferred "Super Admin" translation acceptable as post-Wave decision | ☐ |
| 6. Edge Cases | 14 critical edge cases agreed as blocking | ☐ |
| 6. Edge Cases | 8 deferred items acceptable gaps | ☐ |
| Cross-cutting | Scope boundary preserved across all 6 sections | ☐ |
| Cross-cutting | Estimated execution (2–3 AO hrs / 45–60 min Claude Code) is realistic | ☐ |

## 7.3 AO Pre-Wave Technical Review

AO must answer YES to each before sign-off:

| # | Question | YES Required |
|---|---|---|
| 1 | Can Filament v5.6 register a second `Panel` provider at `/admin` with `admin` guard without conflicting with existing workspace panel at `/app`? | ☐ |
| 2 | Does Filament v5.6 support custom render hooks for the topbar User Menu (Screen 8) without ejecting from Filament chrome? If not, what additional time? | ☐ |
| 3 | Is `sessions.guard` column migration safe on existing production DB? Sequence if sessions table doesn't yet exist? | ☐ |
| 4 | Can `EnforceIdleTimeout` and `EnforceAbsoluteTimeout` middleware be added via panel provider, or does this require a custom service provider? | ☐ |
| 5 | Is `RateLimiter::for('admin-login')` registration in `AppServiceProvider::boot()` compatible with existing SURGE-01 rate limiter setup? | ☐ |
| 6 | Are all Pest 4.7 patterns referenced available without additional package installation? | ☐ |
| 7 | Is `Password::min(12)->letters()->mixedCase()->numbers()->symbols()` available in Laravel 13.16? | ☐ |
| 8 | Does CLAUDE.md v6 contain any constraints conflicting with this package? | ☐ |
| 9 | Is ≥95% coverage achievable within 2–3h Wave estimate, or split into 14.1a + 14.1b? | ☐ |
| 10 | Are all 6 admin env vars provisioned on Cloudways before Wave start? | ☐ |

If any answer NO, surface before Wave start; package is amended.

## 7.4 Quality Gate Checklist (Post-Implementation)

### Code Quality

| # | Requirement | Status |
|---|---|---|
| QG-01 | All 14 critical edge cases (Section 6.10) have green Pest tests | ☐ |
| QG-02 | `php artisan test --coverage --min=95` passes for Wave 14.1 namespaces | ☐ |
| QG-03 | `vendor/bin/pint --test` passes with zero violations | ☐ |
| QG-04 | `vendor/bin/phpstan analyse --level=6` passes for Wave 14.1 namespaces | ☐ |
| QG-05 | `php artisan migrate:fresh --seed` runs cleanly on fresh DB | ☐ |
| QG-06 | `php artisan migrate:rollback` for 14.1 migrations is non-destructive (or destructive-by-design w/ documentation) | ☐ |

### Specification Conformance

| # | Requirement | Status |
|---|---|---|
| QG-07 | All 8 screens documented in Section 3 implemented and accessible | ☐ |
| QG-08 | All 4 admin roles function with access matrix from Section 4.10 | ☐ |
| QG-09 | All ~280 translation keys from Section 5 exist in BOTH `ar/admin.php` and `en/admin.php` with identical key structures | ☐ |
| QG-10 | `LocaleKeyParityTest.php` passes | ☐ |
| QG-11 | `NoHardcodedStringsTest.php` passes | ☐ |
| QG-12 | `AuditLogPasswordLeakageTest.php` passes | ☐ |
| QG-13 | Visual regression snapshots exist at `tests/Browser/__snapshots__/admin/` for all 8 screens in both locales (16 baselines) | ☐ |

### Security & PDPL Posture

| # | Requirement | Status |
|---|---|---|
| QG-14 | Guard isolation tests (AUTH-10, AUTH-11) pass | ☐ |
| QG-15 | `admin_users` is NOT using `HasApiTokens` trait | ☐ |
| QG-16 | `admin_activity_log` Resource exposes no Create/Edit/Delete actions | ☐ |
| QG-17 | Last super_admin invariant has three enforcement layers | ☐ |
| QG-18 | Seeder refuses to run in production without `ADMIN_SEED_*` env vars (INFRA-02 passes) | ☐ |
| QG-19 | Session cookie `platform_admin_session` path-scoped to `/admin` + `SameSite=Strict` | ☐ |

### Operational Readiness

| # | Requirement | Status |
|---|---|---|
| QG-20 | Deployment runbook updated: `php artisan filament:assets` + `db:seed --class=AdminUserSeeder` + env var checklist | ☐ |
| QG-21 | 7 success metrics (M-1 through M-7) measurable post-deploy; verifier assigned | ☐ |
| QG-22 | `openapi/spec.yaml` UNCHANGED in this PR | ☐ |
| QG-23 | `docs/README.md` updated with link to this 14.1 package and placeholders for 14.2–14.9 | ☐ |

If any QG item unchecked, PR blocked from merge.

## 7.5 Sign-Off Authority Hierarchy

| Priority | Authority | Scope |
|---|---|---|
| 1 | **Abdullah Mohammed** | All product, scope, feature decisions |
| 2 | **CLAUDE.md v6** | Architectural non-negotiables (cursor pagination, append-only ledgers, advisor precedence) |
| 3 | **Designated AO** | Implementation-level technical decisions within constraints set by 1 and 2 |
| 4 | **Khaldoun Khater** (for items in scope) | Practitioner-validated decisions (not applicable to 14.1) |

## 7.6 Package Status Transition

```
Draft  →  In Review (Abdullah signed)  →  Validated (AO signed)  →  Ready for Dev  →  In Progress  →  QG Review  →  Merged  →  Deployed
```

Wave 14.1 currently at: **Draft** — Awaiting Row 1 sign-off in Section 7.1.

---

# Reference Index

- **Surge plan:** `SURGE-14-Admin-Subscriptions.md`
- **Architectural governance:** `CLAUDE.md` v6
- **Source of truth for advisor decisions:** `docs/validation/02_advisor_meeting_log.md`
- **Engineer agent operating contract:** `AODC_Software_Engineer_Instructions.md`
- **Product Designer agent operating contract:** `AODC_Product_Designer_Instructions.md` (this artifact's producer)

# Document End — Wave 14.1 Package Complete
