# Planning Artifacts — Index

**Project:** Levant Commercial-Contracts Legal-AI Platform (working title: TBD)
**Owner:** Abdullah
**Date:** 2026-06-17
**Status:** DRAFT — pre-validation; pre-PRD; pre-stakeholder-signoff
**Stack:** Laravel 11 + Filament v3 + MySQL 8 + Blade/Tailwind/Livewire + Pest + Pint + Cloudways

---

## What these files are

This directory is the **Surge/Flow planning layer** of the AODC pipeline. It sits between:

```
[Competitive teardown] -> [Strategic scoping] -> [THIS LAYER: Surges/Flows] -> [Wave-Ready Packages] -> [Claude Code execution]
```

The files here are NOT Wave-Ready Packages. They are higher-level milestone and epic definitions intended for **the AODC_Software_Engineer agent** to consume and translate into tech tasks for Claude Code.

A Surge is a 1–2 week release milestone (or 2–4 days with multi-agent orchestration).
A Flow is a 1–3 day epic inside a Surge (or hours with Claude Code).
A Wave is a 1–4 hour atomic feature delivery (or 30–60 min with Claude Code).

This layer defines Surges and Flows. Waves are produced LATER, once each Flow has validated wireframes.

---

## How to consume these files (for the Software Engineer agent)

1. **Read `00_MVP_ROADMAP.md` first.** It is the load-bearing document. It states:
   - The wedge (PROVISIONAL — see flags)
   - The MVP scope and what is explicitly excluded
   - The Surge sequence and why this order
   - Cross-cutting non-negotiables (bilingual AR/EN, .docx round-trip, etc.)
   - Open strategic blockers that gate certain Surges

2. **Then read the Surge files in numeric order.** Each file is self-contained but assumes the master roadmap is loaded.

3. **Status flags inside each Surge are load-bearing:**
   - `[REVISIT-AFTER-AI-TEST]` — this decision depends on the Arabic AI test against HAQQ. Do NOT produce final tech tasks for items tagged this way until the test result is in.
   - `[PENDING-LEGAL-REVIEW]` — this decision requires sign-off from a lawyer co-founder/advisor before tech tasks should be produced.
   - `[PROVISIONAL]` — locked for now but expected to be reconsidered after first customer interviews.

4. **What this layer does NOT contain:**
   - Validated Figma wireframes (none exist yet)
   - GIVEN/WHEN/THEN acceptance criteria at story level (those go in Wave-Ready Packages)
   - Exact UI copy in AR + EN (same — Wave-Ready level)
   - Final API request/response schemas (the Surge defines the surface; the Wave defines the contract)

5. **What the Software Engineer agent should produce from these:**
   - A tech-stack-grounded engineering plan per Surge
   - Migration sequences, Eloquent model definitions, Filament resource skeletons
   - API endpoint inventories with FormRequest + Policy references
   - Test file inventories per Surge
   - Risk register entries for items flagged provisional or pending

---

## File inventory

| File | Purpose | Read order |
|---|---|---|
| `README.md` | This file. Index + how to consume. | 1 |
| `00_MVP_ROADMAP.md` | Master plan: wedge, scope, sequencing, principles, open blockers. | 2 |
| `SURGE-00-Pre-Build-Validation.md` | Non-build Surge: wedge validation, lawyer co-founder, PRD lock. **Gates all subsequent Surges.** | 3 |
| `SURGE-01-Auth-Workspace.md` | Google OAuth, workspace tenancy, RBAC primitives, locale switch. | 4 |
| `SURGE-02-Contacts-Matters.md` | Contact (Person/Org) + Client + Matter (lite, NO court fields). | 5 |
| `SURGE-03-Document-Workspace.md` | **THE WEDGE.** Editor + version history + .docx round-trip + bilingual document. | 6 |
| `SURGE-04-Clause-AI.md` | Clause library + AI inline (draft/review/suggest) inside the document. | 7 |
| `SURGE-05-Counterparty-Obligations.md` | Counterparty first-class + contract value/dates/term/renewal + obligation tracking. | 8 |
| `SURGE-06-Polish-Billing-Launch.md` | Onboarding, Stripe billing, help docs, soft launch. | 9 |

---

## Open strategic blockers (visible up-front, never quiet)

| ID | Blocker | Gates which Surges | Resolution path |
|---|---|---|---|
| B1 | Arabic AI test against HAQQ Twin not yet run; wedge is PROVISIONAL per Teardown Vol. 14. | SURGE-04 design depth depends on outcome. SURGE-03 design is unaffected. | Run the test before SURGE-04 detailed planning. |
| B2 | No lawyer co-founder / legal advisor secured. Strategic Brief flags this as non-negotiable. | All Surges that touch legal-domain logic (SURGE-03, SURGE-04, SURGE-05). | Secure advisor before producing Wave-Ready Packages for those Surges. |
| B3 | No validated PRD; no Figma wireframes; no customer interviews. | All build Surges (01–06). Wave-Ready Packages cannot be assembled without wireframes per AODC. | Run 5 small-firm interviews in SURGE-00; produce PRD; design and validate Figma. |
| B4 | Cloudways underlying provider + region unspecified. Affects data-residency claim used vs HAQQ. | All Surges (deployment). | Decide and document in SURGE-01 infrastructure section. |
| B5 | Geography contradiction: AODC instructions read GCC-shaped (Arabic-default), Strategic Brief locked to Levant. Strategy unchanged or shifted? | Localization defaults, billing currency defaults. | Confirm with founder before SURGE-01. |

---

## Cross-cutting non-negotiables (apply to every Surge)

These are enforced at the Wave-Ready Package level for every feature. The Software Engineer agent must treat these as constraints, not features:

1. **Bilingual AR/EN.** Every user-facing string in `resources/lang/ar/*.php` AND `resources/lang/en/*.php`. RTL layout for AR.
2. **.docx round-trip preservation.** Any feature touching documents must preserve .docx structure on import/export. Non-negotiable per Teardown Vol. 12.
3. **Audit timestamps everywhere.** `created_at`, `updated_at`, `created_by_id`, `updated_by_id` on every Eloquent model. Optimistic locking via `updated_at` check on concurrent edits.
4. **Soft deletes.** Every tenant-scoped model uses `SoftDeletes` trait. Hard delete only via explicit admin action.
5. **Workspace (tenant) scoping.** Every query, every Policy, every Filament resource scopes to the current workspace. Cross-workspace data leakage is a P0 bug.
6. **Policy + FormRequest on every endpoint.** No API endpoint ships without both. No Filament resource ships without `canViewAny`/`canCreate`/`canEdit`/`canDelete` policy bindings.
7. **Pest test coverage gate.** Every Wave includes Feature tests; Surge cannot ship without green Pest suite.
8. **OpenAPI spec sync.** Every API endpoint updates `openapi/spec.yaml` in the same PR.

---

## Naming conventions (so all files speak the same language)

- **Surge ID:** `S-NN` (e.g., `S-03`)
- **Flow ID:** `F-NN.M` (e.g., `F-03.2` = second Flow of Surge 3)
- **Wave ID:** `W-NN.M.K` (to be assigned in Wave-Ready Package phase)
- **Entity names:** Singular PascalCase (`Contract`, `Matter`, `Counterparty`)
- **Table names:** Plural snake_case (`contracts`, `matters`, `counterparties`)
- **Filament resource:** `<Entity>Resource` (`ContractResource`)
- **Policy:** `<Entity>Policy` (`ContractPolicy`)
- **FormRequest (store):** `Store<Entity>Request` (`StoreContractRequest`)
- **FormRequest (update):** `Update<Entity>Request` (`UpdateContractRequest`)
- **API controller:** `Api\V1\<Entity>Controller`
- **Service class:** `<Entity>Service`
- **Test (feature, API):** `tests/Feature/Api/V1/<Entity>ApiTest.php`
- **Test (feature, Filament):** `tests/Feature/Filament/<Entity>ResourceTest.php`
- **Test (unit, service):** `tests/Unit/Services/<Entity>ServiceTest.php`
