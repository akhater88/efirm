# SURGE-12 — Integrations + Upmarket Features

**Surge ID:** S-12
**Name:** Integrations + Upmarket Features
**Type:** BUILD Surge (completing breadth coverage)
**Estimated duration:** 12–18 days (Claude Code-accelerated)
**Depends on:** SURGE-11 complete
**Pivot reference:** `decisions/D-09_breadth_pivot.md`

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None |
| Legal domain | Data residency implications for email/calendar integrations — `[ADVISOR-REVIEW-RECOMMENDED]` |
| Sign-off | PENDING |
| Architectural reminder | **Email and calendar are INTEGRATIONS via OAuth, NOT native builds.** See §3 below for why. |

---

## Goal

Close the remaining feature-coverage gap with HAQQ by:

1. **Email integration** — read inbox, attach emails to matters, send from product. NOT a native email client.
2. **Calendar integration** — sync events from Google Calendar + Outlook. Export obligations + hearings as ICS. NOT a native calendar.
3. **SSO / SAML** — upmarket feature for firms requiring federated identity
4. **Mobile PWA** — Progressive Web App responsive UI. NOT a native iOS/Android app.
5. **Audit log read-only UI** — surface the audit trail (already in DB) as a Filament UI for compliance review

By the end of this Surge: the product covers ~90% of HAQQ's nominal feature surface (the remaining ~10% is intentionally never-build items per CLAUDE.md §10).

---

## Critical architectural decision: integrations, not native builds

Original CLAUDE.md §10 excluded "native email client" and "native calendar" because each is a multi-year product. That exclusion stands even under the breadth pivot. What this Surge ships instead is the **integration layer** — OAuth-based read/write access to the user's existing Outlook/Gmail/Google Calendar/Outlook Calendar.

This delivers ~80% of the user value of HAQQ's native email/calendar with ~5% of the build cost. The trade-off: users still need a separate email client (Gmail, Outlook) for primary inbox interaction. That's fine — they already have one.

If after pilot use customers genuinely demand a native email/calendar (they almost certainly won't — no professional services product has succeeded at this), a future ADR can supersede this decision.

---

## Flows

### F-12.1 — Email Integration (Outlook + Gmail)

**Goal:** Workspace users can connect their Outlook or Gmail account via OAuth. The product can then: read recent emails, attach an email to a Matter or Contact, send emails from the product on behalf of the user.

**Scope:**

- `email_integrations` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `user_id` ULID FK (per-user connection)
  - `provider` ENUM('outlook','gmail')
  - `email_address` VARCHAR(255)
  - `oauth_access_token` TEXT (encrypted at rest via Laravel's encrypted casts)
  - `oauth_refresh_token` TEXT (encrypted)
  - `oauth_expires_at` TIMESTAMP
  - `scopes_granted` JSON
  - `is_active` BOOLEAN default true
  - `last_synced_at` TIMESTAMP nullable
  - audit timestamps + soft deletes + audit users
  - composite unique `(user_id, provider)` — one connection per provider per user

- `email_attachments` table (emails attached to entities):
  - `id` ULID
  - `workspace_id` ULID FK
  - `attached_to_type` VARCHAR(100) (`matter`, `contact`)
  - `attached_to_id` ULID
  - `email_integration_id` ULID FK
  - `email_provider_id` VARCHAR(255) (the provider's email ID, for re-fetching)
  - `subject` VARCHAR(500)
  - `from_address` VARCHAR(255)
  - `from_name` VARCHAR(200) nullable
  - `to_addresses` JSON
  - `cc_addresses` JSON nullable
  - `bcc_addresses` JSON nullable (rarely populated)
  - `received_at` DATETIME
  - `body_snippet` TEXT (first 500 chars, cached)
  - `has_attachments` BOOLEAN default false
  - `attachment_files` JSON nullable (filenames + sizes)
  - `is_outbound` BOOLEAN default false (sent from product = true)
  - audit timestamps + soft deletes + audit users

- OAuth flow:
  - Outlook: Microsoft Graph API, OAuth 2.0
  - Gmail: Google API, OAuth 2.0 (extend existing Socialite setup)
  - Both register additional redirect URIs in their respective consoles
  - Scopes: read mail, send mail (limited to user's own mail)

- Services:
  - `EmailIntegrationService::connect($provider, $oauthCallbackData, $user)` — establishes connection
  - `EmailFetcherService::recentEmails($integration, $sinceDate, $limit)` — fetches recent emails (pages 25 at a time)
  - `EmailAttachmentService::attachToMatter($emailProviderId, $matter, $user)` — caches email metadata, links to Matter
  - `EmailSenderService::send($integration, $to, $subject, $body, $relatedMatter)` — sends via provider API; creates outbound attachment record

- Filament Page: "Email Inbox" per user (their own inbox only — never another user's)
  - Lists recent 50 emails
  - Search by subject + from
  - Action per email: "Attach to Matter" → picker
  - Action: "Reply" → composer
- Filament Relation Manager on Matter: "Emails" tab showing attached emails
- Filament Relation Manager on Contact: "Emails" tab showing attached emails where Contact's email matches from/to

**Privacy + data handling:**
- Email body content is NEVER stored in our DB — only snippet (first 500 chars for preview). Full body re-fetched from provider on demand.
- OAuth tokens encrypted at rest (Laravel `encrypted` cast)
- Tokens never returned in API responses
- User can disconnect at any time — disconnect deletes tokens (soft delete on `email_integrations`)
- Workspace admin sees that a user has a connection, NOT the user's inbox contents

**`[ADVISOR-REVIEW-RECOMMENDED]`** items:
- Privacy policy implications (we briefly handle email content even if we don't store it; this needs disclosure in Privacy Policy)
- Data residency: emails sent from product transit our backend; some jurisdictions consider this data processing requiring notice

**Tests:**
- Pest: OAuth flow with mocked provider responses creates `email_integrations` row
- Pest: token encryption at rest
- Pest: fetching emails requires active integration
- Pest: attaching email creates `email_attachments` row with correct metadata
- Pest: sending email requires active integration and creates outbound attachment record
- Pest: user can only see their own emails; cannot see another user's inbox
- Pest: workspace isolation (attaching only works on matters in user's accessible workspace)
- Pest: disconnect deletes tokens

**API:** Full CRUD on integrations and attachments; specific endpoints:
- `GET /auth/email/outlook/connect`, `GET /auth/email/outlook/callback`
- `GET /auth/email/gmail/connect`, `GET /auth/email/gmail/callback`
- `GET /api/v1/email/inbox?limit=25&before=...`
- `POST /api/v1/email/{provider_id}/attach` — body: `{ matter_id }` or `{ contact_id }`
- `POST /api/v1/email/send` — body: `{ to, subject, body, related_matter_id }`

**Acceptance:** Founder connects own Google account via OAuth in development; fetches inbox; attaches one email to a Matter; sends a reply from the product; verifies receipt in their actual Gmail.

**Cost guardrail:** Provider APIs have rate limits. Cache aggressively (Redis, TTL 5 min for inbox lists). Surface rate-limit errors gracefully to user.

---

### F-12.2 — Calendar Integration (Google + Outlook)

**Goal:** Sync events from Google Calendar / Outlook Calendar. Export Obligations + Hearings as ICS for external calendars.

**Scope:**

- `calendar_integrations` table:
  - Same structure as `email_integrations` but for calendar scope
  - `provider` ENUM('google','outlook')
  - `calendar_id` VARCHAR(255) (which calendar to sync — primary by default; user can pick alternates)
  - audit timestamps + soft deletes + audit users

- `external_calendar_events` table (read-only mirror):
  - `id` ULID
  - `workspace_id` ULID FK
  - `user_id` ULID FK
  - `calendar_integration_id` ULID FK
  - `provider_event_id` VARCHAR(255)
  - `title` VARCHAR(500)
  - `description` TEXT nullable
  - `starts_at` DATETIME
  - `ends_at` DATETIME
  - `timezone` VARCHAR(50)
  - `is_all_day` BOOLEAN default false
  - `attendees` JSON nullable
  - `location` VARCHAR(500) nullable
  - `linked_matter_id` ULID FK nullable (lawyer manually links)
  - `linked_hearing_id` ULID FK nullable (when event corresponds to a Hearing)
  - `last_synced_at` TIMESTAMP
  - composite unique `(calendar_integration_id, provider_event_id)`

- Services:
  - `CalendarSyncService::syncRecent($integration)` — fetches next 30 days of events, upserts into `external_calendar_events`
  - `CalendarExportService::matterEventsAsIcs($matter)` — generates ICS file with the matter's Obligations + Hearings as VEVENTs
  - `CalendarExportService::workspaceEventsAsIcs($workspace, $user)` — user's accessible Obligations + Hearings as ICS, with subscription URL for live updates

- Sync schedule: every 15 minutes via scheduled task per active integration
- Filament Page: "My Calendar" — combined view of external events + the user's Obligations + Hearings, in calendar grid (Filament v5 has calendar widget support)
- Public ICS feed: `GET /ics/{user_uuid_token}/calendar.ics` — auth via random token, returns user's events; user can subscribe to this URL in Google Calendar / Outlook for one-way reverse sync

**Tests:**
- Pest: OAuth + sync
- Pest: events upserted on each sync (no duplicates)
- Pest: ICS export validates as proper ICS format
- Pest: matter events ICS contains correct Obligations + Hearings
- Pest: ICS feed token authentication
- Pest: workspace isolation

**API:** OAuth endpoints; `GET /api/v1/calendar/events?from=...&to=...`; `GET /api/v1/matters/{id}/calendar.ics`; `GET /api/v1/me/calendar.ics`

**Acceptance:** Founder connects Google Calendar; sees external events alongside internal Obligations + Hearings; subscribes the ICS feed to their own external calendar and verifies bidirectional visibility.

---

### F-12.3 — SSO / SAML (upmarket)

**Goal:** Workspaces can configure SAML 2.0 SSO with their existing identity provider (Azure AD, Okta, Google Workspace). Bypasses Google OAuth for users from that workspace.

**Scope:**

- `workspace_sso_configs` table:
  - `id` ULID
  - `workspace_id` ULID FK (unique — one SSO config per workspace)
  - `provider_type` ENUM('saml2','oidc')
  - `provider_name` VARCHAR(100) (display name, e.g., "Okta")
  - `idp_metadata_url` VARCHAR(500) nullable
  - `idp_metadata_xml` TEXT nullable (for static configs)
  - `idp_entity_id` VARCHAR(255)
  - `idp_sso_url` VARCHAR(500)
  - `idp_certificate` TEXT (encrypted)
  - `sp_entity_id` VARCHAR(255) (our identifier)
  - `attribute_mapping` JSON (e.g., `email` ← `urn:oid:0.9.2342.19200300.100.1.3`)
  - `enforce_for_domain` VARCHAR(255) nullable (when set, users with this email domain MUST use SSO; cannot use Google OAuth)
  - `is_active` BOOLEAN default true
  - audit timestamps + soft deletes + audit users

- Library: `aacotroneo/laravel-saml2` or equivalent (research in TTP)
- Routes: `/sso/{workspace_slug}/login`, `/sso/{workspace_slug}/acs` (Assertion Consumer Service)
- Pre-login workspace selection: at `/login`, if a user's email domain matches `enforce_for_domain` of an active SSO config, redirect to that workspace's SSO; otherwise show Google OAuth
- Just-In-Time provisioning: when SSO returns a new user, auto-create user + add as Member of the workspace
- Filament `WorkspaceSsoConfigResource` (Owner-only of the workspace; not visible to other workspaces)
- Setup wizard: guided form for configuring SAML metadata, testing the connection

**Tests:**
- Pest: SAML response validation
- Pest: Just-In-Time user creation
- Pest: enforced domain rejects Google OAuth attempts
- Pest: workspace isolation (one workspace's SSO config invisible to others)
- Pest: invalid SAML response rejected (security)

**API:** Full CRUD on SSO configs (Owner of workspace only); SP metadata endpoint at `/sso/{workspace_slug}/metadata`

**Acceptance:** Setup an Okta dev instance + configure a workspace SSO config + log in via SSO + verify user created with correct workspace membership.

---

### F-12.4 — Mobile PWA (Progressive Web App)

**Goal:** Responsive UI for tablet/mobile. NOT a native app. PWA install banner so users can "Add to Home Screen" on iOS/Android for an app-like icon experience.

**Scope:**

- Web App Manifest at `/manifest.json` (icons, name, theme color, display mode)
- Service Worker (`/sw.js`) for offline cache of static assets (NOT data — data still needs network)
- Tailwind responsive breakpoints applied to all Filament pages (Filament v5 is responsive by default; we extend where it's not)
- Mobile-specific overrides:
  - Sidebar collapses to hamburger menu at < 768px
  - Tables become card-style at < 640px
  - Kanban board (F-10.2) becomes a horizontally scrolling single-column-at-a-time view at < 768px
  - Document editor (SURGE-03) — explicit "this surface requires desktop" message at < 1024px (TipTap editor is poor on mobile; better to direct users to desktop)
- Install banner: a small "Add to Home Screen" prompt that respects user dismissal
- iOS/Android-specific meta tags for icon, status bar color, etc.

**Excluded:**
- Native iOS app
- Native Android app
- Push notifications (Year-2 — requires native or a PWA push setup that's still flaky on iOS)
- Offline data sync (Year-2 — complexity bomb)
- Camera access for document scanning (Year-2)

**Tests:**
- Pest browser test: PWA manifest valid
- Pest browser test: mobile breakpoint hides sidebar correctly
- Pest browser test: card view replaces table on small screens
- Manual QA: install on iPhone via Safari + Android via Chrome; verify icon, status bar, behavior

**API:** No new API

**Acceptance:** Founder installs the product on their phone via Safari/Chrome "Add to Home Screen", opens from home screen, can view Matters list and Contact details. Document editor explicitly says "open on desktop" — that's by design.

---

### F-12.5 — Audit Log read-only UI

**Goal:** Compliance-ready surface showing the audit trail of significant actions in a workspace. The data already exists in DB (via Laravel's auditing or our own audit log tables); this Flow surfaces it.

**Scope:**

- `audit_logs` table (already exists — confirm or extend):
  - `id` ULID
  - `workspace_id` ULID FK
  - `user_id` ULID FK nullable (null for system actions)
  - `action` VARCHAR(100) (e.g., 'matter.created', 'document.exported', 'trust_account.deposited', 'sso.login', 'role.changed')
  - `auditable_type` VARCHAR(100) nullable
  - `auditable_id` ULID nullable
  - `changes` JSON nullable (before/after diff for updates)
  - `metadata` JSON nullable (IP, user agent, request ID)
  - `ip_address` VARCHAR(45) nullable
  - `user_agent` VARCHAR(500) nullable
  - `created_at` TIMESTAMP
  - **APPEND-ONLY** (model + Policy + DB-level if possible)

- Coverage of significant events (verify via grep in audit phase):
  - All financial mutations (already required per SURGE-09)
  - All AI interactions (already required per SURGE-04)
  - Role changes
  - Workspace invitations + acceptances + removals
  - Document import/export/share/version creation
  - Hearing creation + status transitions
  - Login (SSO + Google OAuth)
  - Settings changes
  - Automation runs (already required per SURGE-11)

- Filament `AuditLogResource` (Owner/Admin only — never Member; never deletable in UI):
  - List with filters (by user, by action, by date range, by entity type)
  - Detail view shows full `changes` diff
  - Search by action / entity
  - Export to CSV (with download size limits — paginate)

- Retention policy: keep audit logs forever in MVP; Year-2 add archival/cold-storage policy
- Encryption at rest: standard Laravel DB encryption; sensitive fields in `metadata` JSON encrypted via `encrypted` cast

**Tests:**
- Pest: append-only enforcement (multi-layer)
- Pest: audit log entries created for each tracked event type
- Pest: workspace isolation
- Pest: Member role cannot access audit log
- Pest: CSV export works; respects pagination limits

**API:**
- `GET /api/v1/audit-logs` (Owner/Admin only) with filtering
- `GET /api/v1/audit-logs/export.csv` (Owner only) — streamed response

**Acceptance:** Founder can view audit log of every significant action that happened in the workspace; export to CSV for compliance review.

---

## Surge acceptance criteria

- [ ] F-12.1: Email integration works for both Outlook and Gmail; attach + send work; privacy preserved
- [ ] F-12.2: Calendar integration works; ICS export validates; reverse subscription works
- [ ] F-12.3: SSO works end-to-end with Okta dev instance
- [ ] F-12.4: PWA installs on iOS + Android; mobile breakpoints work
- [ ] F-12.5: Audit log UI surfaces all tracked events; append-only enforced
- [ ] All Pest tests green
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated (~30 new endpoints)
- [ ] No regression in S-01 to S-11 tests

---

## Items still NOT built even after SURGE-12

The CLAUDE.md §10 list still rejects (intentionally):

- **Native email client** — integration is sufficient; building a real client is multi-year
- **Native calendar module** — integration is sufficient
- **Native iOS / Android apps** — PWA is sufficient for MVP target market
- **Native messaging/chat** — Slack/Teams integration could come in a future Surge; building from scratch is out
- **Workflow visual designer with drag-drop nodes** — Year-2 if demanded
- **Form template marketplace** — Year-2
- **Custom code/script actions in automations** — never (security)

These intentional exclusions cap nominal HAQQ-coverage around 90% feature parity. The remaining 10% gap is the price of focus on shipping quality. Closing it would take an estimated additional 6–12 months of build work, mostly on platform features whose customer value is low.

---

## What the Software Engineer agent should produce

1. **For F-12.1 email integration**: token encryption MUST use Laravel's `encrypted` cast, and Pest tests must verify tokens are unreadable via direct DB query. Failed encryption = security incident.

2. **For F-12.2 ICS export**: validate generated ICS files against an external validator (e.g., `RFC5545` checker library). Producing invalid ICS that imports incorrectly into Google Calendar is the most common failure mode.

3. **For F-12.3 SSO**: SAML response validation MUST verify signature, audience, recipient, conditions, NotBefore/NotOnOrAfter. Skipping any of these creates real security holes. Cite the library's security advisories in the TTP and patch to latest.

4. **For F-12.4 PWA**: do NOT enable offline data sync. The complexity is enormous and frequently causes data corruption. Service worker caches static assets only.

5. **For F-12.5 audit log**: confirm by grep that the new events listed are actually firing. If any of them turn out to not be logged in current code, file Linear issues for the responsible Surge and add them in scope-creep work.

6. **Cumulative test count check**: after SURGE-12, the test count should be in the 700–900 range given the ~120 new endpoints across S-10/11/12. If significantly lower, that's a red flag worth raising before the Surge is marked complete.

---

## After SURGE-12

The breadth-pivot scope is functionally complete. The remaining work is:

- Verification (SURGE-VERIFY — overdue)
- Production hardening (originally planned as SURGE-10 in v0.2 roadmap, but pushed back by feature expansion)
- Lawyer/CPA signoff for hard-stop Surges (litigation, trust accounts, AI prompts, legal documents)
- Customer interviews (F-00.3 still deferred — should run with working product against pilots)
- Cloudways deployment
- Marketing site
- Pilot activation

None of these are build Surges. All are operational, validation, and go-to-market work.
