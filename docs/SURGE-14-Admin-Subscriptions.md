# SURGE-14 — Super-Admin Panel & Subscription Management

**Surge ID:** S-14
**Name:** Super-Admin Panel & Subscription Management
**Type:** BUILD Surge
**Estimated duration:** 3–4 days (with Claude Code); 12–16 AO hours total across 9 Waves
**Depends on:** SURGE-01 (Auth + Workspace) complete
**Enables:** Beta launch — first paying law firms can be provisioned, billed, and supported without engineering involvement

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — internal operational tooling |
| Legal domain | PDPL Law No. 24 of 2023 (cross-border consent + data retention) — Khaldoun review required for Waves 14.3 and 14.9 only |
| Sign-off | PENDING — Founder (per-Wave); Khaldoun for Waves 14.3 + 14.9 only |
| Stack reconciliation | Spec targets **Laravel 13.16 + Filament v5.6** (actual stack), NOT the v11/v3 referenced in older project docs |

---

## Goal

Deliver the complete internal administrative surface that enables the platform team to manage law firm customers, subscriptions, billing, entitlements, and PDPL compliance without direct database access. By the end of this Surge:

- A separate Filament panel exists at `/admin` with its own `admin` guard, isolated from the firm-facing workspace at `/app`
- Four admin roles enforce least-privilege access (super_admin, support, finance, read_only)
- Three subscription plans (Starter $20 / Pro $25 / Enterprise $30 per seat, USD) with per-seat pricing and feature gates
- Five-state subscription lifecycle (trial → active → past_due → suspended → cancelled) with automatic transitions
- Stripe integration handles customer creation, subscription creation, seat-quantity propagation, and webhook-driven state transitions
- Entitlement service enforces seat count, storage quota, matter caps, contact caps, and feature flags at the FormRequest layer
- Super-admins can impersonate firm users with full audit trail (60min idle / 4h hard cap, persistent banner, append-only log)
- PDPL cancellation flow honors Jordan Law No. 24 of 2023 (cross-border consent, retention window, scheduled purge)

This is the operational backbone of the SaaS business. Without it, the platform cannot accept paying customers at scale.

---

## Decisions locked during Discovery

| # | Decision | Value | Source |
|---|---|---|---|
| D-1 | Admin panel scope | Super-admin (platform operators) only — no firm-admin self-serve in this Surge | Discovery Q1(a) |
| D-2 | Pricing model | Multiple plans (Starter/Pro/Enterprise), each with its own per-seat USD rate + caps + feature flags | Discovery Q2(a) reconciled with Q3(e) |
| D-3 | Limits enforced | Seat count, storage quota, matter caps, contact caps, feature gates | Discovery Q3(a+b+d+e) |
| D-4 | Billing integration | Stripe-integrated (real environment provisioned) | Discovery Q4(b) + Q6 |
| D-5 | Lifecycle states | trial (14d, no card) → active → past_due (7d grace) → suspended → cancelled (14d total) | Discovery Q5(b) |
| D-6 | Impersonation | In scope for Wave 14.3 with full audit + 60min idle / 4h cap | Discovery Q7 |
| D-7 | Currency | USD (Stripe charges in USD; UI displays USD) | User input |
| D-8 | Tax handling | Stripe Tax disabled; manual handling; columns reserved for future enable | User input (deferred) |
| D-9 | Pricing values | Starter $20 / Pro $25 / Enterprise $30 per seat per month (under Khaldoun's JOD 20–30 ceiling — intentional) | User confirmation |
| D-10 | Admin provisioning | Seeded super_admin + invite-only thereafter; no self-signup; no public `/admin/register` route | Default approved |
| D-11 | Trial duration | 14 days, no credit card required | Default approved |
| D-12 | Past-due grace | 7 days to suspend; 14 days total to cancel | Default approved |
| D-13 | Admin authentication | Session-cookie based via `admin` guard (NOT Sanctum tokens) | Architectural |
| D-14 | Stripe API version | Pinned to `2025-08-27.acacia` in `config/services.php` | Architectural |

---

## Wave breakdown

| Wave | Name | Est. (AO hrs) | Depends on | Status |
|---|---|---|---|---|
| **14.1** | Super-Admin Panel Foundation | 2–3h | SURGE-01 | **Wave-Ready Package complete** |
| **14.2** | Plan Model + Plan Resource CRUD | 1.5h | 14.1 | Pending packaging |
| **14.3** | Workspace Management Resource + Impersonation (revised scope) | 3h | 14.1 | Pending packaging (Khaldoun input needed for impersonation policy text) |
| **14.4** | Subscription Model + Lifecycle State Machine + SubscriptionEvent ledger | 2h | 14.2, 14.3 | Pending packaging |
| **14.5** | Stripe Customer & Subscription Sync | 2h | 14.4 | Pending packaging |
| **14.6** | Stripe Webhook Handler | 1.5h | 14.5 | Pending packaging |
| **14.7** | SubscriptionEntitlementService + BlockSuspendedWorkspace middleware | 2h | 14.4 | Pending packaging |
| **14.8** | Usage Tracking & Cap Enforcement | 1.5h | 14.7 | Pending packaging |
| **14.9** | PDPL Cancellation & Retention Flow | 1.5h | 14.4 | Pending packaging (BLOCKED on Khaldoun input: retention window + cross-border consent text) |

**Critical path:** 14.1 → 14.2 → 14.4 → 14.5/14.7 (parallel) → 14.6/14.8 (parallel) → 14.9

**Parallel opportunities once 14.4 lands:** Stripe sync track (14.5 → 14.6) and entitlement track (14.7 → 14.8) can run on separate AO sessions.

---

## Cross-cutting architectural constraints

These constraints apply to every Wave in SURGE-14 and must not be deviated from:

### 1. Append-only ledgers (CLAUDE.md hard rule)

The following tables introduced in this Surge are append-only:

- `admin_activity_log` (Wave 14.1)
- `admin_impersonation_sessions` (Wave 14.3)
- `subscription_events` (Wave 14.4)
- `stripe_webhook_events` (Wave 14.6 — idempotency ledger)

For each: no `updated_at` column, no soft deletes, no Filament edit/delete actions. Corrections via new offsetting events only.

### 2. Cursor pagination only

Every list/index endpoint introduced in this Surge uses Laravel cursor pagination. No `paginate()` calls (offset). Per CLAUDE.md.

### 3. Defense in depth for invariants

Critical invariants (e.g., last-super-admin-must-exist, single-active-timer-per-user) are enforced at THREE layers:

- FormRequest validation
- Policy method
- Pest test asserting the database-level invariant

### 4. No password / secret in audit payloads

`password`, `password_confirmation`, `new_password`, `current_password`, Stripe webhook secrets, API tokens — none of these appear in any audit/event log payload. CI test enforces.

### 5. Idempotency for Stripe webhooks

Every Stripe webhook is recorded by `stripe_event_id` in `stripe_webhook_events` before any side effect. Replays no-op. Webhook signature verification non-negotiable.

### 6. Stack version target

Laravel 13.16 + Filament v5.6 + Pest 4.7 + PHP 8.3 + MySQL 8 + Redis. Filament v5.6 uses the Schema-based form API (`Schema::make()`), NOT v3's `Form::make()`. Any spec or migration referencing v11/v3 syntax is a defect.

### 7. Admin authentication: session, not token

`AdminUser` model does NOT use `HasApiTokens` trait. No Sanctum tokens issued for admins. Admin session cookie `platform_admin_session` is path-scoped to `/admin` with `SameSite=Strict`.

### 8. PDPL Article 13 audit trail

Every admin action that touches firm-user data must produce an audit log entry. The audit log is read-democratically — all admin roles can view it for accountability investigations.

---

## Surge acceptance criteria (must all pass to mark S-14 done)

1. Filament admin panel at `/admin` accessible only via `admin` guard; `/app/login` and `/admin/login` are isolated (zero cross-guard auth success)
2. Four admin roles (super_admin, support, finance, read_only) enforced via policies; access matrix matches Wave 14.1 Section 4.7
3. Three plans (Starter, Pro, Enterprise) seeded with USD pricing; admin can edit per-seat rate, caps, and feature flags via Filament resource
4. Workspaces resource shows all firms with status, seat count, storage usage, current plan, subscription state
5. Subscription lifecycle state machine transitions correctly: trial → active (on first payment), active → past_due (on failed payment), past_due → suspended (after 7 days), suspended → cancelled (after 14 days total), cancelled → archived (PDPL)
6. Stripe customer and subscription provisioned automatically on workspace activation; seat changes propagate to Stripe with proration; webhook signatures verified; idempotent
7. Entitlement service blocks operations exceeding caps: seat add when at limit, matter create when at limit, feature use when not in plan's `features` JSON
8. Suspended workspaces are read-only across all firm-facing routes via `BlockSuspendedWorkspace` middleware
9. Super-admin impersonation works with persistent banner, 60min idle / 4h hard cap, append-only audit log; cannot impersonate other admins
10. Cancellation flow captures explicit cross-border consent reference and schedules data purge per PDPL retention window
11. All 9 Waves' Pest test suites green; project-wide coverage ≥95% on SURGE-14 namespaces
12. `vendor/bin/pint --test` green; `vendor/bin/phpstan analyse --level=6` green
13. CLAUDE.md updated to vNext with SURGE-14 architectural decisions
14. Deploy runbook updated with Stripe env vars, admin seeder env vars, webhook endpoint registration

---

## Out of scope for this Surge (deferred or never)

| Item | Disposition |
|---|---|
| Firm-admin self-serve subscription management (firm owners changing their own plan inside `/app`) | Deferred — Year 2 backlog |
| Multi-currency support (Stripe in JOD or multi-currency display) | Deferred — USD only |
| Stripe Tax automatic VAT | Deferred — manual handling for now; columns reserved |
| 2FA / TOTP for admin accounts | Deferred — schema reserved (`two_factor_secret` column); future security Wave |
| Common-password blocklist (haveibeenpwned integration) | Deferred — complexity floor sufficient |
| IP anomaly detection for admin sessions | Deferred — logs collected; analysis layer separate |
| Tamper-evident audit log (hash chain / cryptographic signing) | Deferred — standard append-only sufficient for PDPL minimum |
| Firm-user notification of impersonation session post-completion | PENDING Khaldoun input (Wave 14.3) |
| Refund / dispute UI inside admin panel | Future Wave — Stripe Dashboard sufficient for beta |
| Annual billing intervals | Deferred — monthly only |
| Promotional discount codes / coupons | Deferred |
| Webhook for plan changes (firm sees plan change in real-time via WebSocket) | Deferred |

---

## What the Software Engineer agent should produce from this Surge

The Engineer agent consumes the Wave-Ready Packages (one per Wave, starting with `WAVE-14.1-Super-Admin-Panel-Foundation.md`) and produces:

1. **Per-Wave Tech Task Package (TTP)** — exact migrations, models, FormRequests, Policies, Filament Resources, Services, middleware, jobs, test inventory, OpenAPI diff (none for 14.1, populated from 14.5 onward), localization keys
2. **Gate Status Report (GSR)** at Surge start — verifies SURGE-01 completion, advisor sign-offs for Waves 14.3/14.9, Stripe environment provisioned
3. **CLAUDE.md diff** at Surge completion — appends SURGE-14 architectural decisions (admin guard, append-only ledger pattern, Stripe API version pinning, impersonation policy, PDPL retention window)

---

## Pre-Surge gate checks

Before Wave 14.1 starts, the following must be true:

| Check | Status |
|---|---|
| SURGE-01 (Auth + Workspace) complete and deployed | Required |
| CLAUDE.md at v6 with all current architectural rules | Required |
| Stripe environment provisioned with test + live API keys | Required (per D-4) |
| 6 admin env vars provisioned on Cloudways: `ADMIN_SEED_EMAIL`, `ADMIN_SEED_NAME`, `ADMIN_SEED_PASSWORD`, `ADMIN_SESSION_IDLE_MINUTES`, `ADMIN_SESSION_ABSOLUTE_HOURS`, `ADMIN_PASSWORD_MIN_LENGTH` | Required before Wave 14.1 start |
| Stripe env vars provisioned: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_MODE` (test/live) | Required before Wave 14.5 start |
| Khaldoun advisor review of impersonation policy text | Required before Wave 14.3 start |
| Khaldoun advisor review of PDPL retention window + cross-border consent text | Required before Wave 14.9 start |
| Cross-border consent capture back-ported to SURGE-01 signup flow | Required before Wave 14.9 ships (suggest packaging as Wave 14.1.5) |

---

## Reference documents

- `WAVE-14.1-Super-Admin-Panel-Foundation.md` — Wave 14.1 complete Wave-Ready Package
- `CLAUDE.md` v6 — architectural non-negotiables
- `docs/validation/02_advisor_meeting_log.md` — Khaldoun's 31+ documented decisions (source of truth for advisor input)
- AODC Software Engineer Instructions — Engineer agent operating contract
- AODC Product Designer Instructions — Product Designer agent operating contract (produced this artifact)
