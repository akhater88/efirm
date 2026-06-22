# HAQQ Coverage Gap Analysis

**Date:** 2026-06-21
**Context:** Post-D-09 breadth pivot. Maps what HAQQ has, what we've built, what we still need.

---

## Coverage scorecard

| Already built (S-01 to S-06) | To build (S-07 to S-09) | Still excluded after pivot |
|---|---|---|
| ~40% of HAQQ surface | ~35–40% of HAQQ surface | ~20–25% of HAQQ surface |

Target after SURGE-09: **~75–80% of HAQQ's surface area**, with depth advantage retained on documents + AI + clause library.

---

## Module-by-module — to-build inventory

Color key:
- 🟢 = low risk, build freely
- 🟡 = medium risk, document carefully, review post-build
- 🔴 = HIGH RISK — `[HARD-STOP-LAWYER-REQUIRED]` for production deployment

### Surge 7 cluster — Practice Management Breadth

| Module | HAQQ Vol | Risk | Notes |
|---|---|---|---|
| 🟢 Tasks / Admin Tasks Dashboard | Vol 7 | Low | Generic to-do system; cross-entity linking (Task → Matter, Contact, Document); status workflow |
| 🟢 Time tracking | Vol 1 (header timer) | Low | Per-user, per-Matter, per-Document time entries; daily/weekly summaries; manual + timer-driven entries |
| 🟡 KYC workflow | Vols 3, 13 | Medium | Document checklist per Contact (ID, address proof, beneficial-owner statement); status tracking; expiry reminders. Regulatory — recommend lawyer review of the checklist itself |
| 🟢 KPI & Targets | Vol 11 | Low | Per-user / per-team monthly billable hours, matter throughput, win rate; computed at read-time |
| 🟢 Teams Hierarchy | Vol 11 | Low | Groups within a workspace (e.g., "Corporate", "Litigation", "M&A"); a user can belong to multiple; KPIs roll up by team |
| 🟢 Smart Lists / saved filters | Vol 1 | Low | Saved query parameters per user, per entity; shareable to workspace |

### Surge 8 cluster — Litigation Modules `[ENTIRE SURGE HARD-STOP-LAWYER-REQUIRED]`

| Module | HAQQ Vol | Risk | Notes |
|---|---|---|---|
| 🔴 Matter litigation extension | Vol 2 | High | Adds judge_id, court_id, court_case_number, opponent_contact_id fields to Matter (additive — commercial Matter still supported). Schema change is permanent. |
| 🔴 Courts entity | Vol 2 | High | Reference data: court name, jurisdiction, court type (civil/criminal/commercial), seat city. Seeded with Levant courts only. |
| 🔴 Judges entity | Vol 2 | High | Optional registry of known judges per court. Many firms won't populate this. |
| 🔴 Jurisdictions reference | Vol 2 | High | Already partially exists in `contract_metadata.governing_law` — extend for litigation use |
| 🔴 Hearings | Vol 8 | High | Date, time, court, judge, matter, parties present, outcome notes, status (scheduled/held/postponed/cancelled), documents attached |
| 🔴 Court Reviews | Vol 9 | High | Judge-issued decisions: ruling date, decision type, outcome, next steps, appeal deadline |
| 🔴 Service Log | Vol 10 | High | Process service tracking: served party, method, date, recipient, proof attached |
| 🔴 Opponent Lawyer | Vol 3 | Medium | Variant of Contact with `is_opposing_counsel` flag; references Matter via `matter_counterparties.opposing_counsel_id` |

### Surge 9 cluster — Financial + CRM Modules

| Module | HAQQ Vol | Risk | Notes |
|---|---|---|---|
| 🟡 Chart of Accounts (simplified) | Vol 4 | Medium | Firm operating accounts + matter sub-ledgers. NOT full GAAP — basic income/expense/asset/liability categorization. Lawyer + CPA review recommended for chart structure. |
| 🔴 Trust Accounts | Vol 4 | **HIGH** | **`[HARD-STOP-LAWYER-REQUIRED for production]`** — bar association regulated in every Levant jurisdiction. Trust account modeling errors create direct disciplinary liability. Append-only ledger; never update or delete entries. |
| 🟡 Journal Entries | Vol 4 | Medium | Manual entries on Chart of Accounts; debit/credit double-entry; matter linkage optional |
| 🟡 Client Invoicing | Vol 4 | Medium | Firm bills client (NOT vendor invoices); links to Matter; supports retainer drawdown from trust accounts (when trust signed off); PDF export with firm letterhead |
| 🟢 CRM: Leads | Vol 6 | Low | Pre-Contact pipeline entity; source, status, owner, notes, expected matter type |
| 🟢 CRM: Pipeline & Stages | Vol 6 | Low | Configurable pipeline stages per workspace; weighted probability per stage; kanban-style view |
| 🟢 CRM: Opportunities | Vol 6 | Low | Linked to a Lead; converts to Matter on close-won; tracks expected fees, win probability |
| 🟢 Receipts ledger | Vol 4 | Low | Client payments received; linked to invoices; running balance per client |

### Items deferred even under the pivot (Year-2 backlog)

| Module | HAQQ Vol | Why deferred |
|---|---|---|
| Native email client | Vol 13 | Integrate Outlook/Gmail via OAuth in Year-2 |
| Native calendar | Vol 13 | Integrate Google/Outlook via OAuth + ICS export in Year-2 |
| Form Templates engine | Vol 12 | High build cost vs hardcoded defaults; revisit Year-2 if customers demand |
| Automations / workflow engine | Vol 12 | Same |
| Global config builder | Vol 12 | Same |
| SSO / SAML | Vol 14 (Pro) | Upmarket; defer until firm size > 25 |
| Audit log read-only UI | Vol 14 (Pro) | Logs exist in DB; UI Year-2 |
| AI extraction of obligations | (extension to S-05) | Year-2 enhancement |
| RAG over Levant statutes | (extension to S-04) | Corpus and licensing work Year-2 |
| E-signature integration | (extension to S-03) | Year-2 — integrate DocuSign or local equivalent |
| Real-time collaborative editing | (extension to S-03) | Year-2 — optimistic locking is sufficient for MVP |
| Hijri calendar | (extension to S-01) | Year-2 |
| Eastern Arabic numerals | (extension to S-01) | Year-2 |
| Conflict-of-interest checking | (extension to S-02) | Year-2 |
| Mobile app | — | Never; web-only |
| Native messaging/chat | — | Out of scope entirely |

---

## Why this order (S-07 → S-08 → S-09)

**S-07 first** because it's the lowest-risk Surge and exercises cross-entity linking patterns (Tasks → Matter/Contact/Document) that S-08 and S-09 will reuse. It also ships fastest (7–10 days) and gives an early win.

**S-08 second** because litigation requires the lawyer advisor signed off. If F-00.2 closes by week 2 (during S-07 build), S-08 can begin immediately. If F-00.2 slips, S-08 build can still proceed but production deployment blocks until signoff — providing pressure to close F-00.2.

**S-09 last** because it has the most regulatory complexity (trust accounts) AND the most external integration surface (potentially needs Stripe for receipts, accounting export formats). Best built when the team rhythm is established.

---

## Estimated total surface after S-09

| Category | HAQQ has | We will have after S-09 | Coverage |
|---|---|---|---|
| Auth, workspace, tenancy | ✓ | ✓ | 100% |
| Contacts | ✓ | ✓ | 100% |
| Matters (commercial + litigation) | ✓ | ✓ | 100% |
| Documents | ✓ | ✓✓ (deeper than HAQQ) | 110% |
| AI | ✓ (separate app) | ✓✓ (in-document) | 110% |
| Clause Library | ✗ (limited) | ✓ | 150% advantage |
| Counterparty + Obligations | partial | ✓ | 110% |
| Tasks | ✓ | ✓ | 100% |
| Time tracking | partial | ✓ | 100% |
| KYC | ✓ | ✓ basic | 70% |
| KPI / Targets | ✓ | ✓ | 90% |
| Teams Hierarchy | ✓ | ✓ | 90% |
| Litigation (Hearings/Reviews/Service Log) | ✓ | ✓ | 90% |
| Chart of Accounts | ✓ full GAAP | ✓ simplified | 60% |
| Trust Accounts | ✓ | ✓ | 90% |
| Invoicing | ✓ | ✓ | 80% |
| CRM | ✓ | ✓ basic | 75% |
| Smart Lists | ✓ | ✓ | 100% |
| Form Templates | ✓ | ✗ | 0% |
| Automations | ✓ | ✗ | 0% |
| Native Email | ✓ | ✗ | 0% (integration only) |
| Native Calendar | ✓ | ✗ | 0% (integration only) |
| Mobile app | ✓ | ✗ | 0% |
| SSO/SAML | ✓ (Pro) | ✗ | 0% |
| Audit log UI | ✓ (Pro) | ✗ | 0% |

**Weighted coverage estimate:** ~75–80% of HAQQ's surface, with depth advantage on the wedge surfaces (documents, AI, clause library) retained.

---

## What this analysis does NOT tell you

- Whether customers actually want any of S-07/08/09 — this remains unvalidated
- Whether the engineering complexity will compound bug rates
- Whether the pricing will work
- Whether the lawyer advisor will materialize in time for S-08

All of these are open. The analysis is a build plan, not a market validation.
