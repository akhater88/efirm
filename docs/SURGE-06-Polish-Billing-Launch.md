# SURGE-06 — Polish, Billing & Launch

**Surge ID:** S-06
**Name:** Polish, Billing & Launch
**Type:** BUILD Surge (launch-readiness)
**Estimated duration:** 7–10 days (with Claude Code); 2–3 weeks (manual)
**Depends on:** S-04 + S-05 complete; D-06 (billing provider) decided
**Enables:** Soft launch with paying pilots

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — but pricing depends on F-00.3 willingness-to-pay data |
| Legal domain | `[PENDING-LEGAL-REVIEW]` — Terms of Service, Privacy Policy, Data Processing Agreement, AI Disclaimer must all be lawyer-drafted |
| Sign-off | PENDING — Founder + Legal Advisor + ALL pilot firms |

---

## Goal

Take the product from "works" to "shippable." Polish the UX rough edges, build a respectable onboarding, add Stripe (or chosen provider) billing, write the help docs, draft + sign off all legal docs, and run a soft launch to the 3 pilot firms from F-00.3.

This is the least-glamorous Surge and the one most likely to be under-scoped. It's also the one that decides whether the MVP becomes a real business.

By the end of this Surge:
- A new firm can sign up, walk through onboarding, and reach an "import your first contract" state in < 8 minutes (per Roadmap success metric)
- Billing works: a workspace has a subscription, a card on file, monthly invoicing, dunning for failed payments
- Legal docs exist: Terms of Service, Privacy Policy, DPA, AI Disclaimer — all bilingual, advisor-signed
- Help docs exist for the core flows
- A status page + monitoring is set up
- A feedback channel (in-app + email) is wired
- The 3 pilot firms from F-00.3 are activated and paying

---

## Flows

### F-06.1 — Onboarding flow

**Goal:** First-run experience that gets a new firm from signup to "imported their first contract" in under 8 minutes.

**Scope:**
- Post-signup wizard (Livewire, multi-step):
  1. **Welcome** — bilingual greeting; "What language do you want the product in?"
  2. **Workspace name** — pre-filled with the user's email-domain firm name, editable
  3. **Invite teammates** — optional (skippable); 1–5 email invites with role selector
  4. **Practice areas of focus** — multi-select (informs library seeding if Wedge A)
  5. **Import your first contract** — file upload (.docx) with the same backend as S-03 F-03.3
  6. **You're in** — lands in the document editor with the imported contract open, with a small overlay tour pointing to: save, version history, AI panel, export
- Skip option at every step; tracked in `users.onboarding_state` JSON column
- Re-show wizard on next login if not completed
- Welcome email sent on signup with: dashboard link, help docs link, AI disclaimer, calendar link for a free 30-min setup call with founder (for first 50 firms)

**Entities touched:** `users.onboarding_state` (new JSON column).

**API surface:**
- `PATCH /api/v1/me/onboarding-state` — persist progress

**UI surface:**
- Multi-step Livewire wizard with progress indicator
- Editor overlay tour (a small JS library like driver.js or Shepherd.js)
- Welcome email (bilingual)

**Key decisions to make in Wave-Ready Package:**
- Wizard exact copy (advisor-approved tone)
- Editor tour exact steps
- Whether to gate the dashboard until wizard is at least at step 5 (default: no, allow skip)
- Sample contract: if user doesn't have one to import, offer a sample contract (lawyer-advisor-curated bilingual NDA fixture)

**Acceptance criteria:**
- Manual UX timing test: founder + 3 non-lawyer testers reach the editor with content in < 8 min (Roadmap success metric)
- Pest browser: full wizard flow completes; state persists if interrupted
- Pest browser: skip every step → user still lands on a usable empty dashboard
- Welcome email renders correctly in both locales

**Dependencies:** S-01 through S-05 complete.

---

### F-06.2 — Billing & subscriptions

**Goal:** Workspace Owners can add a payment method, choose a plan, and get billed monthly. Failed payments dunned automatically.

**Scope:**
- Provider: per D-06 decision. Default recommendation: **Stripe** (best MENA card coverage at MVP; consider Paddle Year-2 for handling VAT/MoR)
- Laravel Cashier (Stripe) integration
- `subscriptions` table managed by Cashier
- `subscription_items` for per-seat pricing
- Plan structure (subject to F-00.3 willingness-to-pay data):
  - **Pilot tier (free, time-limited):** for the 3 F-00.3 pilots, 6 months free, no card required
  - **Starter:** $X/seat/mo, includes Y AI interactions/seat/mo, Z GB storage
  - **Pro (post-MVP):** higher AI cap, advanced features (Year-2 — placeholder only at MVP)
- Stripe webhook endpoint: `POST /webhooks/stripe` (signature-verified)
- Webhook handlers: subscription created/updated/cancelled, invoice paid/failed, payment_method updated
- Dunning: failed payment → 3 retries over 14 days → workspace auto-suspended (read-only mode) at day 21
- Read-only mode: existing data accessible but no edits, no API writes, no AI calls
- Owner UI: billing page with current plan, seats, usage, invoice history, payment method, plan change, cancel

**Entities touched:** Cashier-managed tables (`subscriptions`, `subscription_items`); `workspaces.billing_status` ENUM('active', 'trial', 'past_due', 'suspended', 'cancelled') with default 'trial' (14 days post-signup unless pilot tier).

**API surface:**
- `GET /api/v1/workspaces/{id}/billing` — current plan, seats, usage
- `POST /api/v1/workspaces/{id}/billing/checkout` — creates a Stripe Checkout Session, returns URL
- `POST /api/v1/workspaces/{id}/billing/portal` — creates Stripe Customer Portal session
- `POST /api/v1/workspaces/{id}/billing/cancel` — cancel at period end
- `POST /webhooks/stripe` — webhook receiver

**UI surface:**
- Owner-only billing pages
- Filament `BillingResource` (admin oversight)
- "Your trial ends in N days" banner
- "Payment failed — please update" banner
- "Workspace suspended — please reactivate" lockout screen
- All bilingual; currency localized

**Key decisions to make in Wave-Ready Package:**
- D-06: confirm Stripe (vs Paddle vs Lemon Squeezy)
- Price points (gated on F-00.3 data)
- Currency for billing (USD default; pilot firms in JOD/LBP face FX — accept USD for MVP)
- Trial length (default 14 days)
- VAT handling — Stripe Tax for some, but Lebanon/Jordan/Palestine/Iraq VAT often manual. MVP: USD pricing only, customer handles local VAT
- Refund policy (advisor input)

**Acceptance criteria:**
- Pest: full Stripe Checkout test flow with Stripe's test cards (success, failure, 3DS)
- Pest: webhook signature verification rejects unsigned payloads
- Pest: failed-payment dunning state transitions work over time (using `Carbon::setTestNow()`)
- Pest: suspended workspace blocks write API calls but allows reads
- Manual QA with Stripe test mode; switch to live mode only after legal docs are signed

**Dependencies:** All prior Surges; legal docs (F-06.4); D-06 decided.

---

### F-06.3 — Help docs + in-app help

**Goal:** A respectable docs site (bilingual) covering the core flows; in-app contextual help links.

**Scope:**
- Docs site: hosted alongside the app at `/docs` — static Markdown rendered by a Laravel route (or a separate static site — decide in Wave-Ready Package; default: Laravel route + Markdown to keep it close to the product)
- Content (each topic in AR + EN):
  - Getting started (signup, workspace, invite teammates)
  - Importing your first contract
  - Editing & versioning
  - Using the AI Assistant
  - Sharing with counterparties
  - Tracking obligations & renewals
  - Billing & subscriptions
  - Keyboard shortcuts
  - Troubleshooting common issues
  - FAQ
- In-app help: every major page has a "?" icon linking to the relevant docs page
- Search across docs (MVP: client-side search via Lunr or similar; Year-2 add Algolia)

**Entities touched:** None.

**API surface:** None (static content).

**UI surface:** `/docs/*` routes; in-app help icons everywhere.

**Key decisions to make in Wave-Ready Package:**
- Docs hosting (in-app vs separate static site)
- Docs content sign-off (founder writes; advisor reviews legal-touching parts)
- Image hosting (CDN vs in-repo)

**Acceptance criteria:**
- All 10 topics published in both AR and EN
- In-app help icons present on at least: dashboard, document editor, library, billing
- Docs search returns relevant results
- Docs render correctly in both directions

**Dependencies:** Content from all prior Surges.

---

### F-06.4 — Legal docs: ToS, Privacy, DPA, AI Disclaimer `[PENDING-LEGAL-REVIEW]`

**Goal:** All legal documents drafted (by lawyer advisor or external counsel), signed off, published, and enforced.

**Scope:**
- **Terms of Service** — covers acceptable use, IP, liability, governing law (default: Jordan — confirm with advisor)
- **Privacy Policy** — covers data collected, retention, third-party processors (Stripe, LLM provider, AWS/Cloudways)
- **Data Processing Agreement (DPA)** — required for firms with data-protection-conscious clients; especially relevant if Cloudways region is in EU
- **AI Disclaimer** — separate document explaining AI limitations, no legal advice, data handling for AI prompts
- All four documents bilingual (AR + EN); the AR translation must be advisor-signed-off, not machine-translated
- Display:
  - Pre-signup: linked from sign-up button; "By clicking Sign in, you agree to..."
  - In-app: footer links visible on every page
  - On material change: in-app banner requiring re-acceptance
- Acceptance tracking:
  - `legal_acceptances` table: `(user_id, document_type, version, accepted_at, ip_address, user_agent)`
- All four docs versioned; new version forces re-acceptance

**Entities touched:** `legal_acceptances` (new).

**API surface:**
- `GET /api/v1/legal/{document_type}` — fetch current version + content
- `POST /api/v1/legal/{document_type}/accept` — record acceptance

**UI surface:**
- `/legal/terms`, `/legal/privacy`, `/legal/dpa`, `/legal/ai-disclaimer` — static pages
- Sign-up button gating
- Re-acceptance modal on material change

**Key decisions to make in Wave-Ready Package:**
- `[PENDING-LEGAL-REVIEW]` — ENTIRE Flow blocked until lawyer advisor delivers signed drafts of all four documents in both languages
- Governing law of the ToS itself
- Whether ToS allows or prohibits use of AI-generated text as legal advice (almost certainly prohibits; advisor confirms)
- Liability cap structure

**Acceptance criteria:**
- All 4 documents exist in AR + EN; PDFs archived in `/legal-archive/` with advisor signature page
- Sign-up flow forces acceptance
- Acceptance is logged to `legal_acceptances`
- Material change re-acceptance flow tested

**Dependencies:** F-00.2 (lawyer advisor secured). **HARD GATE** — F-06.2 (billing) cannot go live without these.

---

### F-06.5 — Monitoring, status page, feedback

**Goal:** When something breaks, we know. When customers complain, we can act on it.

**Scope:**
- **Monitoring:**
  - Error tracking: Sentry (free tier at MVP) — both Laravel + Livewire JS
  - Uptime: Better Uptime or Uptime Robot (ping `/health` every 1 min)
  - Performance: Laravel Telescope in non-prod; basic OpenTelemetry export in prod (Year-2)
  - LLM API monitoring: track p95 latency + error rate per LLM provider; alert on degradation
- **Status page:** `https://status.<product>.com` via Better Uptime or BetterStack (free tier)
- **Feedback channels:**
  - In-app: small "Send feedback" widget in footer → emails founder
  - Email: support@<product>.com routes to founder inbox
  - In-app: `feedback` table + Filament resource (alternative to email-only for traceability)
- `/health` endpoint returns DB connectivity, Redis connectivity, queue health, LLM provider reachability

**Entities touched:** `feedback` (optional table).

**API surface:**
- `GET /health` — public health endpoint
- `POST /api/v1/feedback` — auth'd; submits feedback

**UI surface:**
- Feedback widget (sticky footer button)
- Filament FeedbackResource (admin triage)

**Acceptance criteria:**
- Sentry receives a test error from staging
- `/health` returns 200 on healthy, 503 on dependency failure
- Status page is publicly accessible
- Feedback widget round-trip works

**Dependencies:** Cloudways deployment configured.

---

### F-06.6 — Soft launch + pilot activation

**Goal:** Activate the 3 pilot firms from F-00.3 on the product with founder hand-holding.

**Scope:**
- Per pilot firm:
  - 1:1 founder kickoff call (60 min): walk through signup, onboarding, importing their first real contract
  - 7-day intensive support: founder responds to messages within 4 hours
  - 14-day check-in call (45 min): collect feedback, address blockers
  - 60-day decision call: convert to paying customer or end pilot
- Pilot tier: 6 months free with full feature access
- Feedback log (Linear board) tracks every pilot-reported issue with priority + Surge assignment
- Weekly retrospective with the lawyer advisor reviewing how pilots are using the product vs how we expected

**Entities touched:** None (process Flow).

**Acceptance criteria — these are the MVP success criteria (Roadmap §9):**
- 3 pilot firms active and using the product weekly
- Each pilot has imported ≥ 5 real contracts
- Each pilot has accepted ≥ 1 AI suggestion in a real document
- Each pilot has used .docx export and the file opened cleanly in Word
- At least 1 pilot expresses intent to convert to paid

**If after 60 days fewer than 2 pilots convert:** the wedge is not strong enough as built. Re-plan.

**Dependencies:** All previous Flows.

---

## Surge acceptance criteria (MVP launch readiness)

- [ ] F-06.1: Onboarding wizard works; < 8 min to first-contract demonstrated
- [ ] F-06.2: Stripe billing works end-to-end including dunning
- [ ] F-06.3: 10 help docs published bilingual
- [ ] F-06.4: All 4 legal docs published bilingual; advisor-signed
- [ ] F-06.5: Monitoring + status page + feedback channel live
- [ ] F-06.6: 3 pilots activated; weekly active usage
- [ ] All Pest tests green; full regression suite passes
- [ ] Pen-test or basic security audit complete (use a SAST tool + manual review)
- [ ] All Roadmap §9 success criteria demonstrated end-to-end
- [ ] All `[PENDING-LEGAL-REVIEW]` items historically across S-01–S-06 verified closed
- [ ] Sign-off: Founder + Legal Advisor + ≥ 2 pilot firms

---

## Out of scope for this Surge

- Marketing site / public website (separate work stream; not AODC scope)
- Self-serve teams onboarding videos (Year-2)
- Multi-currency billing (Year-2)
- Annual billing discounts (Year-2)
- Referral program (Year-2)
- Affiliate program (Year-2)
- Advanced security: SOC 2, ISO 27001 (Year-2 — required to compete with HAQQ's Pro tier per Teardown Vol. 14)
- White-label / firm branding customization (Year-2)
- Mobile apps (out of MVP entirely)
- Public API documentation portal (Year-2)
- SSO/SAML for enterprise prospects (Year-2)

---

## What the Software Engineer agent should produce

Same template plus:

1. **Stripe configuration manifest:** every product, price, webhook secret, signing secret listed (values in `.env`, not in repo)
2. **A "launch readiness checklist"** as a single markdown file the Software Engineer agent produces, with line-items the Founder ticks off pre-launch (database backup verified, on-call rotation set, runbook for common issues, etc.)
3. **A post-launch runbook** for the founder: what to do if Stripe webhooks fail; what to do if the LLM provider is down; what to do if a pilot reports data loss
4. **Refuse to mark F-06.2 (billing) complete** until F-06.4 (legal docs) is fully signed off — this is a hard ordering gate
5. **All security-sensitive endpoints get `requires_security_review` tag** in the OpenAPI spec; CI flags any new endpoint added without this tag for manual review

---

## Post-MVP (Year-1+ backlog seeded from prior Surges)

Items deferred during S-01–S-06 that should land in Year-1 next-six-months:

- AI extraction of obligations from contract text
- Conflict-of-interest checking
- Hijri calendar support
- Annual billing
- Referral program
- KYC workflow (when first customer asks)
- Calendar export (.ics)
- Document templates
- Track-changes import from Word
- Advanced search (Meilisearch/Typesense)
- Audit log UI
- GDPR data export
- Email/Magic-link auth in addition to Google
- Workspace deletion UX

Items that should NOT land Year-1 regardless of customer demand (off-strategy):

- Litigation features
- Native accounting / GL
- Native CRM / sales pipeline
- Native email client
- Native calendar
- KPI / targets / org hierarchy
- Mobile app

If a customer demands an off-strategy item, the answer is "we integrate, we don't build." Strategic discipline > feature requests.
