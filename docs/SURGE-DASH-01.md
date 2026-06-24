# SURGE-DASH-01 — Tenant Dashboard Redesign

> **Status:** Active (as of 2026-06-24)
> **Predecessor:** SURGE-05 (Counterparty + Obligations) — F-05.5 dashboard rollup superseded by this Surge
> **Concurrent state:** SURGE-14 (Super-Admin Panel) **paused** for the duration of this Surge; resumes after SURGE-DASH-01 sign-off
> **Estimated duration:** ~2 weeks (15 Waves)

---

## Strategic intent

The tenant dashboard currently renders as an empty Blade view with no brand expression (`resources/views/dashboard.blade.php`). This Surge replaces it with a HAQQ-inspired visual shell — chrome, sidebar, hero, widget grid, Quick Links rail — populated with **commercial-contract-aligned widgets** that respect CLAUDE.md §10's architectural prohibitions.

The shell mirrors HAQQ; the content does not. This is the "Option D" resolution recorded in the 2026-06-24 Product Design thread.

### Why HAQQ-parity shell

- HAQQ's dashboard pattern (chrome + 2×2 widget grid + dual feed strip + Quick Links rail) is now a regional table-stakes convention. Diverging without a strong reason creates unfamiliarity.
- eFirm's distinct brand color (`#0D5C2E` forest green vs HAQQ's wine/maroon) provides built-in visual differentiation even at parity layout.
- The Arabic/Levant commercial-contract wedge is reinforced by *content* (widget data sources), not by layout novelty.

### Why NOT HAQQ-parity widgets

CLAUDE.md §10 forbids `hearings`, `court_reviews`, `service_logs`, `judges`, `courts` migrations and entities. HAQQ's litigation-centric widget set (Hearings, Court Reviews, Service Log) would require building forbidden entities. The commercial wedge depends on **not** building them.

---

## Architectural constraint (must read before any Wave)

This Surge is HAQQ-parity at the **shell** level only:

| Layer | HAQQ-parity? | Notes |
|---|---|---|
| Top chrome (search, quick-add, timer, chat, notifications, user menu) | **Yes** | Workspace switcher replaced by static firm name (decision C5) |
| Left sidebar (collapse, nav groups, accordion submenus) | **Yes** | NO "Case Management" group (forbidden by CLAUDE.md §10) |
| Hero banner | **Yes** | AI Twin entry is a placeholder modal with email capture (decision C4) |
| Widget grid layout | **Yes** | 2×2 cards + dual feed strip |
| **Widget content** | **NO** | See widget mapping below |
| Quick Links rail | Partial | NO Hearings, Leads, or Financial-as-litigation-accounting |

### Widget mapping (HAQQ → eFirm)

| HAQQ widget | eFirm widget | Data source |
|---|---|---|
| Legal Matters | **Legal Matters** | `matters` (SURGE-02) |
| Calendar | **Calendar** | Obligation due dates + Matter milestones |
| Hearings | **Documents** | `documents` (SURGE-03) — recently updated |
| Tasks | **Tasks** | `tasks` (SURGE-02) |
| Court Reviews feed | **Upcoming Obligations feed** | `obligations` (SURGE-05 F-05.3) |
| Service Log feed | **Upcoming Renewals feed** | `contract_metadata.renewal_date` (SURGE-05 F-05.2) |

---

## Flows

### F-DASH-01.1 — Brand Foundation (W1)

**Goal:** Establish tokenized design system — palette, typography, brand assets, self-hosted fonts — that all subsequent Waves consume.

**Source:** `planning/SURGE-DASH-01/F-DASH-01.1-brand-foundation.md` (Wave-Ready Package v1.1).

**Acceptance:**
- `tailwind.config.js`, `resources/css/app.css`, `resources/design/tokens.json` reflect the full token set
- All fonts self-hosted in `public/fonts/` (no CDN refs)
- Brand assets in `public/img/brand/`
- Pest contrast + browser tests green
- Brand foundation invariants in CLAUDE.md §9 enforced

### F-DASH-01.2 — AI Twin Waitlist Entity (W2)

**Goal:** Build the `ai_twin_waitlist` table and minimal capture API to back the dashboard hero modal.

**Scope:**
- Migration: `ai_twin_waitlist_entries` (id, email, locale, workspace_id nullable, created_at)
- Model: `AiTwinWaitlistEntry` (no SoftDeletes — not tenant-scoped in the usual sense)
- Email validation + dedup (unique constraint on email)
- API endpoint: `POST /api/v1/ai-twin/waitlist` — public (no auth required from logged-in user; rate-limited)
- OpenAPI spec entry

**Acceptance criteria:**
- Submitting same email twice returns 200 idempotently (no duplicate row)
- Rate limit: 5 submissions/min per IP
- Locale captured from request (`ar` or `en`)

**Dependencies:** None — this is the smallest engineering Wave in the Surge.

### F-DASH-01.3 — Application Shell: Top Chrome (W3–W4)

**Goal:** Build the persistent header bar — firm name (left), global search modal (center), quick-add menu, Start Timer with split dropdown, chat placeholder, notifications dropdown, user menu, tier badge.

**Scope:**
- Livewire component `TopChrome`
- Firm name pulled from `workspace.name` (static, no switcher per C5)
- Global search modal — empty for now (search backend in later Surge)
- Start Timer wires to existing single-active-timer rule (CLAUDE.md invariant)
- Chat icon — placeholder, opens "coming soon" toast
- Notifications bell — reads from existing `notifications` (Laravel default)
- User menu dropdown with logout, profile (links to existing routes)
- Tier badge sources `subscription.tier` from Cashier subscription

**Acceptance:**
- Renders identically in RTL and LTR
- All strings via `__('shell.*')` keys
- Tier badge color from `tier-*` tokens

### F-DASH-01.4 — Application Shell: Left Sidebar (W5)

**Goal:** Build the collapsible left sidebar with nav groups, active state, accordion submenus.

**Scope:**
- Livewire component `LeftSidebar`
- Nav groups (per CLAUDE.md §10 carve-out): Dashboard, Firm, Contacts, **(NO Case Management)**, Tasks, Documents, Calendar, Smart Lists, View More
- Collapse to icon-only mode (persists via cookie)
- Logo block at top (mark when collapsed, full logo when expanded)
- Active state via `border-inline-start: 3px solid brand-300`

**Acceptance:**
- Sidebar renders right-aligned in RTL (`inset-inline-start: 0` resolves correctly)
- Collapse state persists across page loads
- No "Hearings", "Court Reviews", "Service Log" nav items anywhere

### F-DASH-01.5 — Hero Banner + AI Twin Placeholder (W6)

**Goal:** Greeting banner with locale-aware date + AI Twin placeholder card that opens a modal with email capture.

**Scope:**
- Livewire component `DashboardHero`
- Greeting: "Good morning/afternoon/evening, :name" — Arabic equivalent uses time-of-day vocative
- Date: locale-aware Gregorian (Arabic month names from Laravel's localized Carbon)
- AI Twin card: full-width below greeting, copy: "Click to start a conversation with AI assistant" (Arabic equivalent)
- Click opens modal: title "AI Twin — coming soon", email field, locale captured implicitly, submit button → toast "We'll be in touch."
- Modal posts to `/api/v1/ai-twin/waitlist` (W2 endpoint)

**Acceptance:**
- Modal accessible (focus trap, ESC to close, ARIA labels)
- Empty email shows inline validation error
- Successful submit shows toast, closes modal

### F-DASH-01.6 — Widget Grid + Reusable Card Component (W7)

**Goal:** Responsive widget grid (2×2 top + 2-col feed bottom) and a reusable `DashboardWidgetCard` Livewire component with header/body/footer slots and empty/loading/error states.

**Scope:**
- Blade component or Livewire: `DashboardWidgetCard`
- Slots: title, icon, body (default + empty + loading + error), footer (View All link + Create CTA)
- Responsive breakpoints per WRP F-DASH-01.1 §3.6

**Acceptance:**
- Card empty/loading/error states render with correct CSS tokens
- Footer CTAs are optional slots
- Widget grid responsive across desktop / laptop / tablet / mobile

### F-DASH-01.7 — Widget: Legal Matters (W8)

**Goal:** Top-grid widget showing 5 most-recently-updated matters with status badges.

**Scope:**
- Livewire component `Widget\LegalMattersWidget`
- Query: `Matter::where(workspace_id)->orderBy('updated_at', 'desc')->limit(5)`
- Cached 5 min per user per workspace via Redis
- Empty state: "No recent matters" + Create CTA
- Footer: "View All" → `/matters`, "+ Create" → `/matters/create`

### F-DASH-01.8 — Widget: Calendar (W9)

**Goal:** Top-grid widget showing upcoming events (obligation due dates + matter milestones) within next 14 days.

**Scope:**
- Livewire component `Widget\CalendarWidget`
- Query: union of `obligations.due_date` + (TBD) matter milestones
- Empty state: "No upcoming events" + Create CTA
- Footer: "View All" → `/calendar`, "+ Create" → opens modal (TBD which entity it creates)

**Note:** "Create" semantics need a follow-up clarifying gate — creating a Calendar entry implies an `events` entity, which doesn't exist. Likely resolution: button creates an Obligation. To be locked in Wave 9 WRP.

### F-DASH-01.9 — Widget: Documents (W10)

**Goal:** Top-grid widget showing 5 most-recently-updated documents. **Replaces HAQQ's "Hearings" widget.**

**Scope:**
- Livewire component `Widget\DocumentsWidget`
- Query: `Document::where(workspace_id)->orderBy('updated_at', 'desc')->limit(5)`
- Cached 5 min
- Empty state: "No recent documents" + Create CTA
- Footer: "View All" → `/documents`, "+ Create" → `/documents/create`

### F-DASH-01.10 — Widget: Tasks (W11)

**Goal:** Top-grid widget showing 5 most-recently-updated tasks assigned to the current user.

**Scope:**
- Livewire component `Widget\TasksWidget`
- Query: `Task::where(workspace_id)->where('assignee_id', auth()->id())->orderBy('updated_at', 'desc')->limit(5)`
- Empty state: "No recent tasks" + Create CTA

### F-DASH-01.11 — Feed: Upcoming Obligations (W12)

**Goal:** Bottom-strip feed widget showing obligations due in the next 14 days, searchable + date-filterable. **Replaces HAQQ's "Court Reviews" feed.**

**Scope:**
- Livewire component `Widget\UpcomingObligationsFeed`
- Search by obligation title or counterparty name (debounced 300ms)
- Date filter (default: next 14 days; max range: next 60 days)
- Cursor pagination (per CLAUDE.md §4)
- Empty state: "No upcoming obligations in this window"

### F-DASH-01.12 — Feed: Upcoming Renewals (W13)

**Goal:** Bottom-strip feed widget showing contracts whose `renewal_date` falls in the next 60 days. **Replaces HAQQ's "Service Log" feed.**

**Scope:**
- Livewire component `Widget\UpcomingRenewalsFeed`
- Search by contract title or client name
- Date filter (default: next 60 days; max range: next 180 days)
- Cursor pagination
- Empty state: "No upcoming renewals in this window"

### F-DASH-01.13 — Quick Links Rail (W14)

**Goal:** Right-side rail with shortcuts to: Calendar, Contacts, Documents, Legal Matters, Tasks, Clause Library, Obligations, Settings.

**Scope:**
- Livewire component `QuickLinksRail`
- Each link: icon + label (Arabic + English)
- Hidden on tablet (< 1280px) per WRP responsive notes

**Forbidden items:** Hearings, Court Reviews, Service Log, Leads, Financial (litigation accounting). Per CLAUDE.md §10.

### F-DASH-01.14 — Polish + Responsive QA (W15)

**Goal:** Final pass — RTL audit, responsive breakpoints, WCAG, performance.

**Scope:**
- Browser test pass in both `ar` and `en` locales
- Responsive QA across desktop / laptop / tablet / mobile breakpoints
- WCAG AA audit (axe-core CLI or equivalent)
- Lighthouse audit ≥ 95 Performance, ≥ 95 Best Practices, ≥ 95 Accessibility
- Style guide HTML page at `/dev/style-guide` (gated behind `APP_ENV=local`)

---

## Surge acceptance criteria

- [ ] All 14 Flows signed off
- [ ] All Pest tests green (Unit + Feature + Browser)
- [ ] Larastan level 6 clean
- [ ] Pint clean
- [ ] OpenAPI spec includes `POST /api/v1/ai-twin/waitlist`
- [ ] CLAUDE.md §9 brand invariants enforced (no Google Fonts requests, no raw hex codes, contrast tests green)
- [ ] At least one pilot lawyer confirms dashboard "feels native and professional" (qualitative sign-off)
- [ ] HAQQ side-by-side comparison shows clearly distinct visual identity (founder visual review)

---

## Dependencies

- SURGE-02 (Contacts + Matters) — supplies `matters`, `tasks`
- SURGE-03 (Document Workspace) — supplies `documents`
- SURGE-05 (Counterparty + Obligations) — supplies `obligations`, `contract_metadata`
- Brand foundation (this Surge's Wave 1) — supplies design tokens

---

## Out of scope

- Dark mode (light-only per C2 = light decision)
- AI Twin actual product (placeholder only)
- Print stylesheet (Year-2)
- Workspace switcher (single firm name per C5; multi-workspace UX deferred to Year-2)
- Filament v3.x admin theming (SURGE-14 paused; resumes after this Surge)
- Mobile app (web only per CLAUDE.md §10)
- Global search backend (search modal renders empty; backend is a separate Surge)

---

## What the AODC Software Engineer agent should produce

For each Flow F-DASH-01.N (N ≥ 2), a Tech Task Package (TTP) referencing this Surge plan + the corresponding Wave-Ready Package. **Exception:** F-DASH-01.1 (Brand Foundation) is executed directly from its WRP's Engineer Execution Checklist (Appendix A) — no TTP needed.

For each Wave touching a Livewire widget:
1. The component class path (`app/Livewire/Dashboard/Widget/<Name>Widget.php`)
2. The Blade view path (`resources/views/livewire/dashboard/widget/<name>-widget.blade.php`)
3. The query (with eager loading per CLAUDE.md §4)
4. The Redis cache key and TTL
5. The empty/loading/error state markup
6. The localization keys (`resources/lang/{ar,en}/dashboard.php`)
7. The test file (`tests/Feature/Livewire/Dashboard/Widget/<Name>WidgetTest.php`)

---

*End of SURGE-DASH-01.md*
