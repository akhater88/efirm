# SURGE-02 — Contacts & Matters (lite)

**Surge ID:** S-02
**Name:** Contacts & Matters
**Type:** BUILD Surge
**Estimated duration:** 5–7 days (with Claude Code); 1.5–2 weeks (manual)
**Depends on:** S-01 complete
**Enables:** S-03 (Documents need a Matter to attach to)

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — wedge-agnostic |
| Legal domain | `[PENDING-LEGAL-REVIEW]` for the Matter field set and the Member role's read/write scope (raised in S-01 F-01.4, must be settled before this Surge ships) |
| Sign-off | PENDING — Founder + Legal Advisor |

---

## Goal

Build the two entities every later Surge depends on: **Contact** (polymorphic Person/Org with Client/Counterparty flags) and **Matter** (lite — title, client, status, stage, notes, NO court fields). Borrow the entity shapes from HAQQ (Teardown Vols. 2, 3) but **drop every litigation-specific field**. This is where the schema-level differentiation from HAQQ becomes physically visible.

By the end of this Surge:
- A lawyer can create a Contact (Person or Organization)
- A Contact can be flagged as Client and/or Counterparty
- A lawyer can create a Matter linked to a Client and (optionally) one or more Counterparties
- All Matter fields are commercial-contract-shaped (no Judge, Court, Court Case Number, Opponent, etc.)
- Filament admin shows Contacts + Matters tables
- Customer-facing app shows Contacts + Matters tables with Filament-equivalent UX
- Search across Contacts + Matters works (basic — full-text comes Year-2)

---

## Flows

### F-02.1 — Contact entity (Person / Organization)

**Goal:** Polymorphic Contact model that can be either a Person or an Organization, with optional Client / Counterparty flags. Borrows shape from HAQQ Vol. 3 minus the cosmetic KYC flag and the Google Maps Link (replaced with structured address).

**Scope:**
- `contacts` table:
  - `id` UUID
  - `workspace_id` UUID FK
  - `type` ENUM('person', 'organization')
  - `first_name`, `middle_name`, `last_name` (nullable; only used when type='person')
  - `organization_name` (nullable; only used when type='organization')
  - `display_name` (computed-on-save: full name or org name; indexed for search)
  - `email`, `phone` (nullable)
  - `nationality` ISO 3166-1 alpha-2 (nullable; relevant to KYC later)
  - `tax_registration_number` (nullable; relevant to invoicing later)
  - **Address (structured):** `address_line_1`, `address_line_2`, `city`, `country` ISO 3166-1 alpha-2 (replaces HAQQ's Google Maps Link approach per Vol. 3)
  - `is_client` BOOLEAN default false
  - `is_counterparty` BOOLEAN default false
  - `notes` TEXT nullable
  - `labels` JSON (array of strings)
  - `parent_organization_id` UUID FK self-referential (nullable; for Person → Organization linkage)
  - audit timestamps + soft deletes + `created_by_id`, `updated_by_id`

- `Contact` Eloquent model:
  - `BelongsToWorkspace` trait
  - `scopePerson()`, `scopeOrganization()`, `scopeClient()`, `scopeCounterparty()` query scopes
  - `getDisplayNameAttribute()` mutator-on-save fills `display_name` column
  - `parentOrganization()` belongsTo relationship
  - `peopleInOrganization()` hasMany inverse

- `ContactPolicy`:
  - `viewAny`: any workspace member
  - `view`: same-workspace member
  - `create`: same-workspace member (Owner/Admin/Member all allowed — `[PENDING-LEGAL-REVIEW]`)
  - `update`: same-workspace member
  - `delete`: Owner or Admin only

**Entities touched:** `contacts` (new), `Contact` model (new), `ContactPolicy` (new).

**API surface:**
- `GET /api/v1/contacts` (paginated, filterable by `type`, `is_client`, `is_counterparty`, search by `display_name`)
- `POST /api/v1/contacts` (FormRequest: `StoreContactRequest`)
- `GET /api/v1/contacts/{id}`
- `PATCH /api/v1/contacts/{id}` (FormRequest: `UpdateContactRequest`)
- `DELETE /api/v1/contacts/{id}` (soft delete)

**UI surface:**
- Filament: `ContactResource` with form (different field set per type — Person fields vs Organization fields), table with type icon, name, email, phone, flags (Client/Counterparty), labels
- Customer-facing list page: `resources/views/contacts/index.blade.php` (Livewire component for filters + pagination)
- Customer-facing create/edit pages (or modal — decide in Wave-Ready Package)

**Key decisions to make in Wave-Ready Package:**
- Type switch at create time: single combined form with conditional fields, or separate "New Person" / "New Organization" CTAs (lean toward latter — clearer UX)
- Search index: MySQL full-text on `display_name + email + phone`, or use Laravel Scout with Meilisearch/Typesense later? (Default: MySQL FULLTEXT at MVP)
- Address country list: pre-seeded with Levant + GCC + common counterparty jurisdictions (UK, US, EU)
- Validation: email format only if provided (nullable), phone via libphonenumber? (Lean toward simple regex at MVP)
- `[PENDING-LEGAL-REVIEW]` Can a Member create/edit Contacts, or only Owner/Admin? (Default: Member can.)

**Acceptance criteria:**
- Pest: full CRUD round-trip for both Person and Organization
- Pest: workspace isolation — a contact from Workspace A is invisible to Workspace B
- Pest: `Contact::client()->organization()->get()` returns only client-organizations
- Pest: linking a Person to an Organization works; `peopleInOrganization()` returns the right collection
- Filament resource renders correctly in AR (RTL) and EN
- OpenAPI spec updated with all 5 endpoints

**Dependencies:** S-01 complete.

---

### F-02.2 — Matter entity (lite — commercial-contracts shape)

**Goal:** The Matter model — the analog of HAQQ's `legal_matters` — but with **zero litigation fields**. This is the schema-level differentiation made physical.

**Scope:**
- `matters` table:
  - `id` UUID
  - `workspace_id` UUID FK
  - `title` VARCHAR(255) NOT NULL — the matter name
  - `client_id` UUID FK to `contacts` (must reference a Contact with `is_client=true`)
  - `practice_area` ENUM('commercial_contracts', 'ma', 'corporate_governance', 'securities', 'general_counsel', 'other')
  - `status` ENUM('active', 'on_hold', 'closed', 'archived') default 'active'
  - `stage` VARCHAR(100) nullable — free-form pipeline stage (e.g., "Drafting", "Negotiation", "Signed")
  - `description` TEXT nullable
  - `internal_reference` VARCHAR(100) nullable — client's matter code if any
  - `lead_lawyer_id` UUID FK to `users` nullable
  - `opened_at` DATE nullable
  - `closed_at` DATE nullable
  - `tags` JSON
  - audit timestamps + soft deletes + `created_by_id`, `updated_by_id`

- `matter_counterparties` pivot:
  - `matter_id` UUID
  - `contact_id` UUID
  - composite unique
  - `representing_us_or_them` ENUM('we_represent', 'they_represent', 'no_counsel') — captures the rare case where we record opposing counsel for transactional work

- `matter_lawyer` pivot (additional team members beyond `lead_lawyer_id`):
  - `matter_id` UUID
  - `user_id` UUID
  - `role` VARCHAR(50) — free-form ("associate", "reviewer", etc.)

- **Explicitly absent (vs HAQQ Vol. 2):** Judge, Court, Court Case Number, Court Type, Representation Type, Opponent Contact, Opponent's Lawyer (as a non-counsel construct). Vol. 8/9/10 entire litigation cluster (Hearings, Court Reviews, Service Log) does not exist in our schema.

- `Matter` Eloquent model:
  - `BelongsToWorkspace`
  - `client()` belongsTo Contact (scoped to clients)
  - `counterparties()` belongsToMany Contact through matter_counterparties
  - `leadLawyer()` belongsTo User
  - `lawyers()` belongsToMany User through matter_lawyer
  - `documents()` hasMany Document (FK created in S-03)
  - `scopeActive()`, `scopeByPracticeArea($area)`

- `MatterPolicy`:
  - `[PENDING-LEGAL-REVIEW]` — exact rules below are STUB:
  - `viewAny`: any workspace member
  - `view`: same-workspace; if Member, only if assigned (via `lead_lawyer_id` OR `matter_lawyer` pivot)
  - `create`: Owner / Admin / Member
  - `update`: Owner / Admin, or assigned Member
  - `delete`: Owner / Admin only

**Entities touched:** `matters`, `matter_counterparties`, `matter_lawyer` (all new); `Contact` (gets `mattersAsClient()`, `mattersAsCounterparty()` reverse relations).

**API surface:**
- `GET /api/v1/matters` (paginated, filterable by `practice_area`, `status`, `client_id`, `lead_lawyer_id`; searchable by `title`)
- `POST /api/v1/matters` (`StoreMatterRequest`)
- `GET /api/v1/matters/{id}` (includes client, counterparties, lawyers eager-loaded)
- `PATCH /api/v1/matters/{id}` (`UpdateMatterRequest`)
- `DELETE /api/v1/matters/{id}` (soft delete; refuse if any non-archived documents exist — return 422)
- `POST /api/v1/matters/{id}/counterparties` — attach a Contact as counterparty
- `DELETE /api/v1/matters/{id}/counterparties/{contact_id}` — detach
- `POST /api/v1/matters/{id}/lawyers` — attach an additional team member
- `DELETE /api/v1/matters/{id}/lawyers/{user_id}` — detach

**UI surface:**
- Filament `MatterResource`:
  - Form: tabs for "Details" / "Parties" / "Team"
  - Table columns: Title, Client, Practice Area, Status, Lead Lawyer, Opened At
  - Filters: tabs for All / Active / On Hold / Closed; practice-area dropdown
- Customer-facing list + detail pages
- Detail page tabs: **Overview** (we'll add **Documents** tab in S-03, **Obligations** in S-05)

**Key decisions to make in Wave-Ready Package:**
- `[PENDING-LEGAL-REVIEW]` Matter policy details (esp. Member access)
- Practice-area enum values — confirm with lawyer advisor
- Practice areas as enum vs lookup table (lean toward enum at MVP — small set, rarely changes)
- "Stage" as free text vs enum — lean toward free text (every firm has different stage names)
- Matter numbering: auto-increment per workspace? (Default: yes — `matter_number` column auto-set on insert via Eloquent observer)
- Validation: a Contact set as `client_id` MUST have `is_client=true` (FormRequest check)

**Acceptance criteria:**
- Pest: cannot set a non-Client Contact as `client_id` — returns 422 with localized error
- Pest: cannot attach the Client as their own counterparty (validation)
- Pest: Member without assignment cannot view a Matter; with assignment, can
- Pest: workspace isolation
- Pest: practice-area filter works
- Filament resource renders correctly in AR (RTL) and EN
- OpenAPI spec updated with 9 endpoints

**Dependencies:** F-02.1.

---

### F-02.3 — Cross-entity search + filtering

**Goal:** A lawyer can find a Contact or a Matter quickly via a global search bar. Echoes HAQQ's ⌘K omnibox (Vol. 1) but scoped tighter — only Contacts and Matters at MVP.

**Scope:**
- Global search component (Livewire): keyboard shortcut `⌘K` / `Ctrl+K`, opens a modal with a search input
- Backend: `GlobalSearchService::search($query, $workspaceId, $limit = 10)` — queries Contacts (by display_name, email) and Matters (by title, internal_reference) using MySQL FULLTEXT or LIKE fallback
- Results grouped by entity type with localized labels
- Click-through to detail page
- Per-entity filters on list pages (Contacts: type, client/counterparty, labels; Matters: practice_area, status, lead_lawyer)

**Entities touched:** None (read-only).

**API surface:**
- `GET /api/v1/search?q=...&types=contact,matter&limit=10`

**UI surface:**
- Header: search input visible at all times (existing customer shell); keyboard shortcut
- Filters on `/contacts` and `/matters` list pages

**Key decisions to make in Wave-Ready Package:**
- MySQL FULLTEXT vs LIKE: FULLTEXT requires MyISAM or InnoDB ≥5.6 with `ngram` parser for Arabic. Default: InnoDB FULLTEXT with `ngram` parser, with a LIKE fallback if Arabic results look poor
- Result ranking: most recently updated first within each type
- Result count cap: 10 per type, total 20

**Acceptance criteria:**
- Pest: search for an exact match returns the right record
- Pest: search by partial Arabic name returns relevant results (or, if FULLTEXT ngram is unsuitable, this test documents the known limitation and the LIKE fallback works)
- Pest: a different workspace's records never appear
- Latency budget: search responds in < 300ms on a workspace with 1000 contacts + 500 matters (load test in Pest)

**Dependencies:** F-02.1, F-02.2.

---

### F-02.4 — Workspace member invitations (deferred from S-01)

**Goal:** An Owner or Admin can invite another user to the workspace by email, assigning them a role. The invitee receives a bilingual email with a link to accept; on accept (after Google OAuth), they're added with the right role.

**Scope:**
- `workspace_invitations` table:
  - `id` UUID
  - `workspace_id` UUID FK
  - `email` VARCHAR(255)
  - `role` ENUM matching `Role`
  - `token` VARCHAR(64) unique (random secure)
  - `invited_by_user_id` UUID FK
  - `expires_at` TIMESTAMP (default 7 days from creation)
  - `accepted_at` TIMESTAMP nullable
  - audit timestamps
- `InvitationService::invite($workspace, $email, $role, $invitedBy)`
- `InvitationService::accept($token, $authenticatedUser)`
- Mailable: `WorkspaceInvitationMail` (Markdown, fully localized)
- Routes: `GET /invitations/{token}` (public; redirects to OAuth if not auth'd)
- Filament: a `WorkspaceMember` resource that includes both current members AND pending invitations

**Entities touched:** `workspace_invitations` (new), `WorkspaceMember` (read/write).

**API surface:**
- `POST /api/v1/workspaces/{id}/invitations` (`StoreInvitationRequest`) — Owner/Admin only
- `GET /api/v1/workspaces/{id}/invitations` — Owner/Admin only
- `DELETE /api/v1/workspaces/{id}/invitations/{invitation_id}` — Owner/Admin only (revoke)
- `POST /api/v1/invitations/accept` (auth'd) — accepts a token

**UI surface:**
- Filament: WorkspaceMemberResource with invite action
- Email template (Markdown mailable)
- Acceptance page if token requires sign-in

**Key decisions to make in Wave-Ready Package:**
- Email content (AR + EN versions, signed off by Founder/Advisor for tone)
- Token format + entropy (Laravel `Str::random(64)` default)
- Resend window (1/min, 5/day per recipient)
- Behavior if invited email's user already exists in another workspace (just add to this workspace — multi-tenancy is allowed)

**Acceptance criteria:**
- Pest: full invite → email → accept → membership round-trip
- Pest: expired invitation returns 410 with localized message
- Pest: revoked invitation cannot be accepted
- Pest: cannot invite if not Owner/Admin
- Email renders correctly in both locales

**Dependencies:** F-02.1, F-02.2 (for context; not strictly required).

---

## Surge acceptance criteria

- [ ] F-02.1: Contacts CRUD works; flags + type + structured address all functional
- [ ] F-02.2: Matters CRUD works; ZERO litigation fields present in schema (verify via column-list assertion in Pest)
- [ ] F-02.3: Global search works across Contacts + Matters
- [ ] F-02.4: Invitation flow works end-to-end
- [ ] All Pest tests green
- [ ] Larastan + Pint clean
- [ ] `[PENDING-LEGAL-REVIEW]` items resolved with signed-off Matter policy + Contact policy
- [ ] OpenAPI spec updated
- [ ] Founder + Legal Advisor sign-off recorded

---

## Out of scope for this Surge

- Document attachment to Matters (S-03)
- Counterparty as a first-class entity with contract value/dates/term (S-05 — at this Surge it's only a flag on Contact)
- Practice-area-specific custom fields per Matter (HAQQ Vol. 12's Form Templates engine; Year-2)
- Bulk import of contacts via CSV (defer to S-06 or Year-2 — write to backlog)
- Conflict-checking on new clients (HAQQ's hidden module; Year-2)
- KYC workflow (Year-2)
- Time tracking on matters (HAQQ Vol. 1 header timer; off-wedge entirely)
- Smart Lists / saved filters (HAQQ Vol. 1 collapsed module; Year-2)

---

## What the Software Engineer agent should produce

Same template as S-01 (migrations, models, Filament resources, routes, controllers, FormRequests, policies, tests, OpenAPI diff, localization keys, Larastan notes), per Flow.

The Software Engineer agent should specifically verify that the produced schema for `matters` does **not** contain ANY of the following fields (these are HAQQ-specific litigation fields and their presence would indicate accidental copy-paste):

- `judge_name`, `judge_id`
- `court_id`, `court_type`, `court`
- `court_case_number`
- `opponent_name`, `opponent_contact_id`, `opponents_lawyer`
- `representation_type`
- `jurisdiction_id` (note: a separate `governing_law` field will appear on Counterparty in S-05; not on Matter itself)
- `region` (litigation construct)

If any of these appear, the Software Engineer agent should reject the tech-task spec and flag the violation.
