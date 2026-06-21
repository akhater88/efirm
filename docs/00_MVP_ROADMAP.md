# MVP Roadmap — Levant Commercial-Contracts Legal-AI Platform

**Version:** 0.1 (DRAFT)
**Date:** 2026-06-17
**Owner:** Abdullah
**Status:** PROVISIONAL — pre-validation, pre-PRD, pre-stakeholder-signoff
**Supersedes:** None
**Companion docs:** HAQQ eFirm Teardown Vols. 1–14; Strategic Scoping Brief v1.0; AODC Product Designer Instructions

---

## 1. One-line product hypothesis

> A **bilingual (AR/EN), AI-native commercial-contracts workspace** built for small Levant law firms (2–10 lawyers), where the document IS the workspace — editable, versioned, clause-aware, AI-inline — without the litigation/practice-management breadth that left HAQQ shallow on contracts.

---

## 2. The wedge — current state

**Status:** PROVISIONAL `[REVISIT-AFTER-AI-TEST]`

Per Teardown Vol. 14, the wedge has two candidate framings. Which one wins depends on a single experiment that has not yet been run: grading HAQQ's AI Twin output against a real bilingual commercial contract (Arabic + English NDA or SPA clause set).

### Wedge candidate A — "Arabic / Levant contract depth"
**Wins if:** HAQQ's Arabic-legal output is weak, generic, or jurisdiction-blind.
**Implication:** Lead with Arabic-legal quality, Levant-specific clause playbooks, jurisdiction-aware AI. Year-2 plan stays Levant-centric.

### Wedge candidate B — "Integrated AI-native single surface for small firms"
**Wins if:** HAQQ's Arabic output is competent.
**Implication:** Lead with UX — matter + document + AI on one surface (vs HAQQ's eFirm-Vue + chat.haqq.ai split). Year-2 plan can expand geographically faster.

### What's locked regardless of which wedge wins
The MVP scope below is identical in both cases. The wedge only changes *positioning* and the *depth of Arabic/clause-library investment* in SURGE-04. SURGES 01–03 are wedge-agnostic.

This means **build can start safely on SURGES 01–03 before the AI test runs.** SURGE-04 planning must wait until the test result is in.

---

## 3. MVP scope — what's IN

The MVP is a **focused vertical product**: small-firm commercial contracts on Levant law. Not a platform. Not a practice management system. Not an accounting system.

### Core wedge surface (the part HAQQ doesn't have)
- **Editable document workspace.** Bilingual AR/EN editor inside the product. Not a file blob in storage. (Closes Vol. 5 gap.)
- **Native version history.** Every save creates a version; redline/compare between versions.
- **Clause-aware structure.** Clauses as first-class addressable objects, not paragraphs in a blob.
- **AI inline in the document.** Draft / review / suggest / redline happen IN the document. No separate chat app. (Closes Vol. 14 architectural seam.)
- **.docx round-trip preservation.** Import .docx → edit → export .docx losslessly. **Non-negotiable** — lawyers live in Word (Vol. 12 finding).
- **Clause library.** Reusable clauses with fallback positions (favourable / balanced / adverse).

### Minimum supporting entities (so a small firm can actually use it)
- **Workspace + Users (Owner / Admin / Member).** Multi-tenant. Three roles only.
- **Contact (Person / Organization)** with Client + Counterparty distinction.
- **Matter (lite).** Title, Client, Counterparty, Status, Stage, Notes. **NO court fields.** This is where the differentiation from HAQQ starts at the schema level (Vol. 2).
- **Document tied to Matter.** With version history, clauses, AI, .docx.
- **Counterparty (first-class).** Contract value, currency, effective date, term, renewal date, governing law. (Closes Vol. 3 gap.)
- **Obligations / milestones.** Date-driven reminders on contracts.

### Cross-cutting
- **Bilingual AR/EN throughout.** Default Arabic RTL; English equal-weight (Levant lawyers work bilingually).
- **Workspace-scoped everything.** Policies + FormRequests + Filament resource policies on every surface.
- **Audit trail.** `created_at`, `updated_at`, `created_by_id`, `updated_by_id` everywhere.
- **Optimistic locking** on concurrent edits via `updated_at` check.

---

## 4. MVP scope — what's OUT (explicit non-goals)

Each item here was considered and dropped. Listed so the Software Engineer agent does not produce tasks for them, and so future scope-creep requests get auto-rejected.

| Module | Reason out of scope | Reference |
|---|---|---|
| Litigation / Hearings / Court Reviews / Service Log | We do not serve litigators. Pure scope discipline. | Teardown Vols. 8–10 |
| Accounting / Chart of Accounts / Trust Accounts / Journal Entry | Months of work, compliance liability, owned by QuickBooks/Xero. Integrate Year-2, do not build. | Teardown Vol. 4 |
| Full CRM / weighted pipeline / BD funnel | Off-wedge. A small firm doesn't need this Year-1. | Teardown Vol. 6 |
| Full Tasks / Admin Tasks Dashboard / cross-entity linking | A *task* in our product is an obligation/deadline on a contract, not a project-management object. | Teardown Vol. 7 |
| KYC workflow / sanctions / PEP screening | Defer to Year-2. Levant target firms generally don't need it Year-1. | Teardown Vols. 3, 13 |
| Native email client | Integrate Outlook/Gmail Year-2 only if customers ask. Do not build. | Teardown Vols. 12, 13 |
| Native calendar | Deadlines live on matters/obligations at MVP. No calendar UI. | Teardown Vol. 13 |
| KPI & Targets / Teams Hierarchy / Org structure | Up-market features. Our segment doesn't need it. | Teardown Vol. 11 |
| Form Templates engine / Automations engine / global config builder | Platform overhead. Hard-code sensible defaults. | Teardown Vol. 12 |
| Mobile app (Flutter or otherwise) | Web-only MVP. Lawyers draft on desktops. | Stack decision |
| Real-time collaborative editing (Google Docs style) | High complexity for marginal MVP value. Single-editor with optimistic locking is enough. | Scope discipline |
| SSO / SAML / fine-grained RBAC | Year-2 upmarket feature. Three roles is sufficient for 2–10-lawyer firms. | Teardown Vol. 14 |
| Granular per-feature permissions | Owner/Admin/Member is sufficient. | Teardown Vol. 11 |

---

## 5. Sequencing — the Surge order and why

### Why this order, not a different one

Three constraints drive the order:

1. **Demonstrable wedge by SURGE-04.** A pilot lawyer needs to see "this is different from HAQQ" by week ~8. That means document workspace + clause-aware AI must ship by SURGE-04.
2. **Skeleton before muscle.** Auth + tenancy + base entities must exist before the document workspace can be tenant-scoped. Hence SURGE-01–02 before SURGE-03.
3. **Defer billing until there's something to bill for.** Stripe + onboarding ship LAST, in SURGE-06.

### The sequence

| # | Surge | Purpose | Risk gate | Wedge dependence |
|---|---|---|---|---|
| **00** | Pre-Build Validation | Run AI test; secure lawyer; do interviews; lock PRD; produce Figma. | All subsequent Surges gated on this. | Decides A vs B. |
| **01** | Auth & Workspace | Google OAuth, tenancy, RBAC primitives, locale switch, Filament admin shell. | None blocking. | Wedge-agnostic. |
| **02** | Contacts & Matters (lite) | Person/Org Contact, Client/Counterparty flag, Matter (no court fields). | Depends on S-01. | Wedge-agnostic. |
| **03** | Document Workspace | Editor, version history, .docx round-trip, bilingual document. **THE WEDGE foundation.** | Depends on S-02. | Wedge-agnostic surface; depth of AR features depends on test. |
| **04** | Clause Library + AI Inline | Clauses as entities, AI draft/review/suggest, clause risk flags. **THE DIFFERENTIATION.** | Depends on S-03 AND on AI test result. | `[REVISIT-AFTER-AI-TEST]` |
| **05** | Counterparty & Obligations | First-class Counterparty, contract dates/term/renewal, obligation tracking. | Depends on S-02 + S-03. | Wedge-agnostic. |
| **06** | Polish, Billing, Launch | Onboarding, Stripe, help docs, paid pilot launch. | Depends on S-04 + S-05. | Wedge-agnostic. |

### Total estimated timeline
- **Optimistic (Claude Code-accelerated, agentic):** 5–7 weeks of build (S-01 → S-06), plus 3–4 weeks of S-00.
- **Conservative (founder-led, Pest + manual review):** 10–14 weeks of build, plus 4–6 weeks of S-00.

These estimates **exclude** lawyer co-founder securing time (which is the actual gating risk per Strategic Brief).

---

## 6. Architectural principles (binding on all Surges)

The Software Engineer agent must enforce these without re-deciding them:

1. **Single Laravel monolith.** No microservices for MVP. Filament for admin, Blade+Livewire+Tailwind for customer-facing, REST API for any client integrations or future mobile.
2. **MySQL 8 with InnoDB.** UUIDs as primary keys on tenant-scoped entities (so we can later shard or merge tenants without ID collision).
3. **Workspace scoping via global Eloquent scope.** Every tenant-scoped model uses a `BelongsToWorkspace` trait that auto-injects `workspace_id` filter. Not opt-in per query.
4. **Policies on every endpoint.** No `Route::resource` without a corresponding `<Entity>Policy` registered in `AuthServiceProvider`. No Filament resource without policy bindings.
5. **FormRequests on every write endpoint.** Validation lives in `app/Http/Requests/`. Never inline in controllers.
6. **OpenAPI spec is the source of truth for API shape.** `openapi/spec.yaml` is updated in the same PR that adds/changes an endpoint. CI enforces consistency.
7. **Bilingual strings via Laravel localization only.** Never hard-code AR or EN strings in Blade/Filament. Always `__('domain.key')`.
8. **Cloud:** Cloudways. Underlying provider + region `[PENDING-DECISION]` — affects data-residency positioning vs HAQQ.
9. **No N+1.** Every Eloquent query that displays a list must use eager loading. CI runs a Larastan rule for this.
10. **Document editing happens client-side.** Server stores the document model + versions; the editor (TipTap or ProseMirror — `[DECISION-NEEDED-S-03]`) runs in Livewire-embedded JS. AI calls go through the backend, not directly from the browser to the LLM provider.

---

## 7. Open decisions to resolve before specific Surges

| ID | Decision | Gating Surge | Status |
|---|---|---|---|
| D-01 | Cloudways underlying provider + region (DigitalOcean Frankfurt vs AWS Bahrain vs Vultr Amsterdam, etc.) | S-01 | `[PENDING]` |
| D-02 | Editor library: TipTap vs ProseMirror vs CKEditor vs Editor.js | S-03 | `[PENDING]` — affects .docx round-trip implementation |
| D-03 | LLM provider: Anthropic Claude vs OpenAI vs Cohere (Arabic-strong) vs multi-provider | S-04 | `[REVISIT-AFTER-AI-TEST]` |
| D-04 | Document storage: MySQL JSON column vs filesystem vs S3 | S-03 | `[PENDING]` — affects backup, search, performance |
| D-05 | Version diff algorithm: text-diff vs structural-diff via clauses | S-03 / S-04 | `[PENDING]` |
| D-06 | Billing: Stripe vs Lemon Squeezy vs Paddle (MENA payment-method coverage matters) | S-06 | `[PENDING]` |
| D-07 | Three-role RBAC granularity: any per-feature overrides needed at MVP? | S-01 | Default = no overrides at MVP; Year-2 upgrade |
| D-08 | Authentication: Google OAuth only, or also email/password + magic link? | S-01 | Default = Google only at MVP |

---

## 8. Risk register (rolls up to all Surges)

| ID | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| R-01 | Wedge collapses if HAQQ's Arabic AI output is strong | M | High | Run AI test in S-00 before S-04 planning |
| R-02 | No lawyer co-founder secured → no domain validation → product is wrong | H | Critical | Treat as P0; pause S-00 if not closed by week 2 of validation |
| R-03 | .docx round-trip is harder than expected; lawyers reject lossy import/export | M | High | Spike .docx round-trip in S-03 first Flow; if fails, redesign |
| R-04 | Bilingual editor (AR-RTL + EN-LTR mixed in same doc) is harder than expected | M | Medium | Spike in S-03; choose editor library based on multilingual support |
| R-05 | Small Levant firms don't pay for SaaS at the price points required to make unit economics work | M | High | Validate willingness-to-pay in S-00 interviews |
| R-06 | HAQQ ships an integrated AI workspace in their next release, closing the wedge | L | High | Speed to market; ship S-03 by week 8 |
| R-07 | Cloudways region choice undermines data-residency positioning vs HAQQ (which markets UAE residency) | M | Medium | Resolve D-01 with concrete data-residency claim |
| R-08 | Founder burnout — single technical founder without lawyer co-founder | M | Critical | Hard cap on hours; secure co-founder before S-04 |

---

## 9. Success criteria for the MVP

The MVP succeeds if, at end of SURGE-06, we can demonstrate:

1. A lawyer can sign in (Google OAuth), create a workspace, invite two colleagues with different roles.
2. A lawyer can create a Client (a Contact with Client flag) and a Counterparty.
3. A lawyer can create a Matter linked to that Client + Counterparty.
4. A lawyer can **import a .docx contract**, edit it in our editor, save versions, **export to .docx losslessly**, and the round-tripped file opens cleanly in Microsoft Word with formatting preserved.
5. A lawyer can ask the in-document AI to draft, review, or redline a clause — in Arabic or English — and the AI response is **inserted into the document at the clause level**, not given as a chat reply.
6. A lawyer can record contract value, currency, effective date, term, and renewal date; the system sends a renewal reminder 60 days before renewal date.
7. The product is fully bilingual AR/EN, RTL-correct on every screen.
8. Three paying pilot firms (small, Levant) are using the product weekly.

If any of (1)–(7) is missing at S-06 end, the MVP is not shipped. If (8) does not happen within 60 days of soft launch, the wedge is invalidated and we re-plan.

---

## 10. What this roadmap does NOT decide

- Specific UI / wireframes (handled at Wave-Ready Package level, after Figma)
- Exact API request/response schemas (same — Wave-Ready level)
- Pricing tiers or amounts (handled in S-06 once we have paying pilots' willingness-to-pay data)
- Marketing positioning copy (separate work stream)
- Go-to-market channel strategy (separate work stream)
- Hiring plan (out of scope for AODC)
