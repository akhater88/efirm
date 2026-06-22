# MVP Roadmap — Levant Commercial-Contracts Legal Platform (v0.2)

**Version:** 0.2 (BREADTH PIVOT — supersedes v0.1)
**Date:** 2026-06-21
**Owner:** Abdullah
**Status:** Locked under D-09 breadth-pivot ADR
**Supersedes:** `planning/00_MVP_ROADMAP.md` v0.1

---

## 0. What changed from v0.1

This roadmap supersedes the depth-wedge thesis with a breadth-coverage thesis. See `decisions/D-09_breadth_pivot.md` for full context. In short:

- **v0.1:** focused contract workspace, ~35–40% of HAQQ's surface, depth differentiator
- **v0.2:** broader legal platform, targeting 75–85% of HAQQ's surface, competitive on breadth + retaining depth on the wedge surfaces

The original SURGE-01 through SURGE-06 already shipped. They remain in scope as the "depth core." Three new Surges (SURGE-07, SURGE-08, SURGE-09) add breadth.

---

## 1. Product hypothesis (revised)

> A **bilingual (AR/EN), AI-native legal-OS** for Levant law firms (2–10 lawyers), competing head-to-head with HAQQ.ai on full eFirm + AI surface coverage, with deeper document/clause/AI workflow than HAQQ and locally-priced for the Levant SMB market.

---

## 2. Strategic positioning (revised)

**Old (v0.1):** "The contract workspace for Levant lawyers — not a broad legal-OS."
**New (v0.2):** "A Levant-built, fully bilingual legal-OS that goes deeper on contracts and AI than the GCC alternatives — at Levant prices."

Differentiation claims:

1. **Bilingual AR/EN throughout** (parity with HAQQ; expected baseline for Levant lawyers)
2. **Document workspace + AI inline** is deeper than HAQQ's two-app split (retained from v0.1)
3. **Clause Library with paired AR/EN** (retained from v0.1)
4. **Levant-jurisdiction-aware** litigation modules (new — Jordanian / Lebanese / Iraqi / Palestinian procedural shape, NOT GCC-default)
5. **Pricing under HAQQ** for full feature parity

---

## 3. MVP scope — what's IN (revised, expanded)

### Already shipped (SURGE-01 → SURGE-06 — the depth core)

- Auth, multi-tenant workspaces, RBAC (Owner/Admin/Member), bilingual locale
- Contacts (Person/Org with Client/Counterparty flags) and Matters (commercial fields only — extended in S-08)
- Document Workspace: import .docx, in-browser editor, version history, diff, lossless .docx export, share links
- Clause Library + 5 AI operations inline (draft/review/suggest/translate/explain), risk flags, fallback chains
- Counterparty + contract metadata + Obligations + renewal reminders + dashboard widgets
- Onboarding wizard, Stripe billing, monitoring, legal-doc framework (lawyer-fill pending)

### To be built — SURGE-07 (Practice Management Breadth)

- Tasks / Admin Tasks Dashboard with cross-entity linking
- Time tracking (per-Matter, per-Document)
- KYC workflow (basic — document checklist, status flags)
- KPI & Targets (per-user, per-team monthly billables, matter throughput)
- Teams Hierarchy / Org structure (groups within a workspace, beyond just roles)
- Smart Lists / saved filters across all entities

### To be built — SURGE-08 (Litigation Modules) `[HARD-STOP-LAWYER-REQUIRED for production]`

- Matter litigation extension (judge, court, case number, opponent fields — additive, not replacement)
- Court / Judge / Jurisdiction reference entities
- Hearings entity (scheduling, status, outcomes, linked to Matter)
- Court Reviews (judge-issued decisions log)
- Service Log (process service tracking)
- Opponent Lawyer (variant of Contact with non-counsel position)
- Levant-jurisdiction-shaped procedural data model (NOT GCC-default)

### To be built — SURGE-09 (Financial + CRM Modules)

- Chart of Accounts (simplified, not full GAAP — basic firm accounts + matter sub-ledgers)
- Trust Accounts `[HARD-STOP-LAWYER-REQUIRED for production]` (Levant bar association compliance)
- Journal Entries (manual entries on accounts)
- Client Invoicing (firm bills client; not vendor invoicing)
- Native CRM: Leads, Pipeline stages, Opportunities (separate from Matter — pre-matter intake)
- Receipts ledger

---

## 4. Explicit non-goals (revised — what remains permanently OUT)

The pivot expanded scope but the following items remain out. Each was reconsidered and reaffirmed as off-strategy:

| Module | Why still excluded |
|---|---|
| Native email client | 6+ month build to match competitors; integrate Outlook/Gmail in Year-2 via OAuth |
| Native calendar | Build cost vs value; integrate Google Calendar / Outlook Calendar in Year-2 via OAuth + ICS export |
| Native chat/messaging | Out of legal-OS scope; Slack/Teams integration only if asked |
| Mobile app (iOS/Android) | Web-only remains; PWA-progressive enhancement OK |
| SSO / SAML | Upmarket feature; defer until firm size > 25 lawyers becomes a target |
| Form Templates engine (HAQQ Vol 12) | Configurability vs hardcoded sensible defaults — defaults faster to ship and customers rarely need true configurability |
| Automations / workflow engine | Same reasoning as form templates |
| Global config builder | Same reasoning |
| Audit log UI (read-only) | Logs are written to DB and accessible via Filament admin; dedicated user UI is Year-2 |
| AI-extraction of obligations from contract text | Year-2 — manual entry at MVP |
| RAG over Levant statute corpus | Year-2 — depends on customer demand and corpus availability |
| E-signature integration | Year-2 — integrate DocuSign or similar, do not build |
| Real-time collaborative editing | Single-editor with optimistic locking remains MVP standard |
| Hijri calendar support | Year-2 |
| Eastern Arabic numerals | Year-2 |
| Conflict-of-interest checking | Year-2 |
| Specific GCC features (DIFC court formats, UAE PDPL workflows) | Out unless Levant-first strategy is also revised |

---

## 5. Revised sequencing

| # | Surge | Status | Estimated duration | Lawyer signoff for prod? |
|---|---|---|---|---|
| 01 | Auth & Workspace | ✓ Shipped | — | No |
| 02 | Contacts & Matters | ✓ Shipped | — | No |
| 03 | Document Workspace | ✓ Shipped | — | No |
| 04 | Clause Library & AI | ✓ Shipped | — | Prompts yes |
| 05 | Counterparty & Obligations | ✓ Shipped | — | No |
| 06 | Polish, Billing, Launch | ✓ Shipped (code-ready) | — | Legal docs yes |
| **07** | **Practice Management Breadth** | Next | 7–10 days | Recommended (KYC) |
| **08** | **Litigation Modules** | After 07 | 10–14 days | **Yes — hard stop** |
| **09** | **Financial + CRM Modules** | After 08 | 10–14 days | **Yes — trust accounts hard stop** |
| 10 | Production hardening + launch | After 09 | 5–7 days | Legal docs yes |

Total estimated additional build runway: **~6 weeks** to ship the full breadth product.

---

## 6. Architectural principles (carried forward from v0.1, with additions)

All v0.1 principles remain binding. Additions for the breadth Surges:

11. **Litigation data is jurisdiction-shaped, not jurisdiction-agnostic.** Court schemas store jurisdiction codes; procedural enums are seeded with Levant values (Jordan / Lebanon / Palestine / Iraq), not GCC defaults. Adding a new jurisdiction requires a seed migration and a lawyer review.
12. **Trust account ledgers are append-only.** No update, no delete on trust account ledger rows. Corrections require offsetting entries (accounting integrity).
13. **CRM Leads are separate from Contacts.** A Lead is a pre-Contact pipeline entity. Promotion converts a Lead to a Contact (and creates an Opportunity → Matter chain). Do not conflate.
14. **Tasks are tasks, Obligations are obligations.** A Task is a generic to-do attached to any entity. An Obligation (already built in SURGE-05) is a dated commitment derived from a contract clause. They are distinct models; one does not replace the other.
15. **KPIs are computed, not stored.** Aggregations run on read (with caching), not on write. No materialized KPI tables in MVP. Year-2 if performance demands.

---

## 7. Open decisions added by the pivot

| ID | Decision | Gating Surge | Status |
|---|---|---|---|
| D-09 | Strategic pivot (this roadmap's parent ADR) | All breadth Surges | Decided 2026-06-21 |
| D-10 | Pricing strategy revision (breadth at $30–60 vs raise to $80–150) | SURGE-10 launch | Pending — needs data from first pilot |
| D-11 | Geography re-evaluation (Levant-first vs GCC pivot under breadth) | Marketing post-launch | Pending |
| D-12 | Court reference data: build manual vs source from public API | SURGE-08 | Pending |
| D-13 | Accounting depth: simplified firm-ledger vs full double-entry GAAP | SURGE-09 | Pending — recommend simplified |
| D-14 | CRM-Matter handoff UX (when does a Lead become a Matter?) | SURGE-09 | Pending |

---

## 8. Risk register (additions for the pivot)

In addition to v0.1 risks (R-01 through R-08, all still active):

| ID | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| R-09 | Breadth modules dilute the depth-on-contracts wedge — customers can't tell what we're best at | M | High | Marketing positioning emphasizes depth WITHIN breadth |
| R-10 | Engineering complexity slows iteration speed — bug fixes in module A regress module B | M | Medium | Strong Pest coverage gates (already in place); modular service architecture |
| R-11 | Litigation modules wrongly modeled for Levant procedural law create legal liability | M | Critical | Hard-stop lawyer signoff gate before SURGE-08 production deployment |
| R-12 | Trust account modeling fails Levant bar association regulatory requirements | M | Critical | Hard-stop CPA + lawyer review before SURGE-09 trust account deployment |
| R-13 | Pricing economics break — Levant firms refuse $30–60 for breadth, won't pay $80–150 either | M | High | Pricing test in first 5 pilots before public launch |
| R-14 | HAQQ ships a counter-positioning feature (e.g., "we go deep on contracts too") that erases our retained depth advantage | L | High | Speed; ship S-07/08/09 in 6 weeks |
| R-15 | Net-new tests required for SURGE-07/08/09 push CI runtime past acceptable thresholds | M | Low | Pest parallel testing already configured |

---

## 9. Success criteria (revised)

The pivot succeeds if, at end of SURGE-10 production launch, we can demonstrate:

1. Feature parity claim is defensible — internal coverage matrix shows ≥ 75% of HAQQ's surface
2. Three paying pilot firms (small, Levant) are using the product weekly **across multiple modules** (not just the document workspace)
3. At least one pilot is actively using litigation modules on a real case (post lawyer-signoff)
4. .docx round-trip fidelity unchanged from SURGE-03 (no regression from breadth additions)
5. AI interactions per pilot per week ≥ 5 (engagement signal)
6. Pricing decision (D-10) made with real willingness-to-pay data
7. Lawyer co-founder secured OR formal advisor agreement signed

If any of 1–7 are missing at SURGE-10 end, the pivot has not yet succeeded and we pause for re-evaluation.

---

## 10. What this roadmap still doesn't decide

- Specific Filament resource UX per new module (handled in each Surge plan)
- Exact API schemas (Wave-Ready Package level)
- Marketing site / brand work (separate work stream)
- Lawyer advisor recruitment (F-00.2 still open — now more critical, not less)
- Customer interview execution (F-00.3 still deferred — now optional under the pivot, but recommended after SURGE-07 ships)
