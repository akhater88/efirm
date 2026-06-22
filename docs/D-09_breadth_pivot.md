# D-09 — Strategic Pivot: Depth-Wedge → Breadth-Coverage

**Status:** Decided
**Date:** 2026-06-21
**Decider(s):** Founder (Abdullah)
**Supersedes:** Strategic positioning in `planning/00_MVP_ROADMAP.md` v0.1 §1, §3, §4
**Superseded by:** None

---

## Context

The MVP was built on the "depth-wedge" thesis: go deep on commercial-contract workflow (Documents + AI + Clause Library + Counterparty + Obligations) and deliberately avoid HAQQ.ai's broader surface (litigation, accounting, CRM, tasks, KPIs, KYC, native email, native calendar). The thesis was that small Levant firms (2–10 lawyers) would prefer a focused workspace at $30–60/seat over a broad legal-OS at $80–250/seat.

The build agent has completed SURGE-01 through SURGE-06. The codebase covers approximately 35–40% of HAQQ's surface area by module count, but represents the surfaces where HAQQ is shallowest (per Teardown Vols. 5, 12, 14).

The Founder has elected to pivot from depth-wedge to breadth-coverage. Specifically: build the modules previously excluded under Roadmap §4, using HAQQ's feature set as a second-mover validation signal in lieu of running F-00.3 customer interviews and the F-00.1 AI test as gating prerequisites.

## Rationale (founder-stated)

> HAQQ already ran the customer research and worked with legal advisors. HAQQ's feature set IS the validated answer for what MENA legal firms want. Re-running interviews duplicates work HAQQ has already done. I can use HAQQ's surface as the validation signal and skip discovery before building.

## Trade-offs being explicitly accepted

The Founder accepts the following consequences of this pivot:

1. **No longer a focused-wedge product.** The marketing position changes from "the contract workspace for Levant lawyers" to "a Levant legal-OS competitive with HAQQ." Buyer comparisons become head-to-head on feature lists.
2. **Direct competition with HAQQ on their strongest dimension (breadth).** HAQQ has a funded team and a multi-year head start on breadth modules. Catching up requires sustained build velocity.
3. **Higher engineering surface to maintain.** Estimated 2–3× the maintenance burden of the depth-wedge MVP. More tests, more migrations, more upgrade paths to manage.
4. **Different pricing implications.** A broad legal-OS targeting Levant firms at $30–60/seat may be economically unworkable. Either prices rise toward HAQQ's tier ($80–250) or per-firm CAC must drop dramatically.
5. **Geographic positioning may need to shift.** A broad legal-OS competes more naturally in GCC than in Levant given the willingness-to-pay gap. F-00.7 geography decision may need re-evaluation.
6. **Schema rework.** The existing schema deliberately excludes litigation/accounting/CRM tables. Adding them requires net-new migrations and may force adjustments to existing models (e.g., Matter gets litigation-related FKs).
7. **Customer evidence gap remains.** No customer interviews have been run. The pivot proceeds on the assumption that HAQQ's feature set is the right surface for Levant small firms — this assumption is unvalidated for Levant specifically.

## What is NOT changed by this pivot

1. **The wedge surfaces SURGE-03 (Document Workspace) and SURGE-04 (Clause Library + AI) remain in scope and remain the differentiator.** Breadth is added; depth is not removed.
2. **The bilingual AR/EN requirement remains.** All new modules ship bilingual.
3. **The workspace-scoping, soft-delete, Policy+FormRequest non-negotiables (CLAUDE.md §4) remain.** Every new module follows them.
4. **Filament-everywhere UI strategy (CLAUDE.md §4a) remains.** New modules ship as Filament resources.
5. **The lawyer-advisor hard stop remains** for litigation modules and for the legal documents in SURGE-06.
6. **The off-strategy items that remain permanently excluded:**
   - Native email client (Vol 13) — integrate Outlook/Gmail instead
   - Native calendar (Vol 13) — integrate Google Calendar / Outlook Calendar instead
   - Native chat/messaging — out of scope entirely
   - Mobile app — web-only remains

## What IS added back (now in scope)

| Module (was excluded) | Now scoped to | Risk level | Lawyer required for production? |
|---|---|---|---|
| Tasks / Admin Tasks Dashboard | SURGE-07 | Low | No |
| Time tracking | SURGE-07 | Low | No |
| KYC workflow | SURGE-07 | Medium (regulatory) | Recommended |
| KPI & Targets | SURGE-07 | Low | No |
| Teams Hierarchy / Org structure | SURGE-07 | Low | No |
| Smart Lists / saved filters | SURGE-07 | Low | No |
| Hearings | SURGE-08 | **HIGH (regulatory)** | **YES — hard stop** |
| Court Reviews | SURGE-08 | **HIGH (regulatory)** | **YES — hard stop** |
| Service Log | SURGE-08 | **HIGH (regulatory)** | **YES — hard stop** |
| Judges / Courts entities | SURGE-08 | **HIGH (regulatory)** | **YES — hard stop** |
| Matter litigation fields (judge, court, case number, opponent) | SURGE-08 | **HIGH (regulatory)** | **YES — hard stop** |
| Chart of Accounts | SURGE-09 | High (regulatory/financial) | Recommended |
| Trust Accounts | SURGE-09 | **HIGH (regulatory)** | **YES — hard stop** |
| Journal Entries | SURGE-09 | Medium | Recommended |
| Invoicing / billing of clients | SURGE-09 | Medium | Recommended |
| Native CRM / leads / pipeline | SURGE-09 | Low | No |
| Form Templates engine | Deferred to Year-2 still | — | — |
| Automations / workflow engine | Deferred to Year-2 still | — | — |
| Global config builder | Deferred to Year-2 still | — | — |
| Email/Calendar — **as integrations, not native** | Year-2 backlog (Outlook/Gmail OAuth, ICS export) | Low | No |
| SSO / SAML | Year-2 still (upmarket feature) | — | — |
| Pro tier audit log UI | Year-2 still | — | — |
| Mobile app | Never — web only | — | — |

## Consequences

### Immediate

- `planning/00_MVP_ROADMAP_v0.2.md` supersedes v0.1 with revised IN/OUT lists.
- `CLAUDE.md` §10 ("What NOT to Do") schema-level rejections are LIFTED for `judge_name`, `court_case_number`, `chart_of_accounts`, `journal_entries`, `trust_accounts`, `leads`, `pipelines`. New constraints added per new module domain.
- The Engineer agent stops refusing tech tasks for litigation/accounting/CRM modules — except where `[HARD-STOP-LAWYER-REQUIRED]` flag applies.
- Three new Surge plans (SURGE-07, SURGE-08, SURGE-09) become the active build queue after SURGE-06 production hardening.

### Hard stops that remain (do not waive)

The lawyer advisor (F-00.2) becomes more critical, not less, under this pivot:

- **SURGE-08 (Litigation) entire Surge cannot deploy to production without a Levant-licensed lawyer signoff on the procedural model.** Wrongly modeled court procedures create real legal liability. The Engineer agent will refuse production deployment.
- **SURGE-09 Trust Accounts cannot deploy to production without lawyer signoff and a Levant CPA review.** Trust account handling is regulated under bar association rules in every Levant jurisdiction.
- **SURGE-06 legal documents (ToS, Privacy, DPA, AI Disclaimer)** — unchanged hard stop, applies regardless of pivot.

### Future implications

- Pricing strategy needs re-thinking. A broad legal-OS at $30–60/seat may not be economically viable. Founder should test pricing at $80–150/seat with the breadth product, accepting that the target customer becomes the upper end of Levant SMB firms.
- Marketing positioning needs re-thinking. The "deep on contracts, not broad" pitch no longer applies.
- The HAQQ comparison becomes head-to-head. Win narrative shifts from "different product" to "better/cheaper version of the same product."

### Re-evaluation triggers

This pivot is revisited if any of:

- After SURGE-07 ships and is in front of 3 firms, the firms primarily use ONLY the original depth-wedge surfaces (Documents + AI + Counterparty + Obligations) and ignore the new breadth modules
- Pricing data from F-00.3 (eventually run) confirms Levant firms refuse to pay >$60/seat for any product, breadth or depth
- A lawyer advisor secured under F-00.2 strongly recommends reverting to depth-wedge based on their understanding of the market

## Sign-off

| Name | Role | Date | Acknowledgment |
|---|---|---|---|
| Abdullah | Founder | 2026-06-21 | I accept the trade-offs above and the hard stops that remain binding. I am proceeding with breadth-coverage strategy using HAQQ's surface as a second-mover validation signal. |

---

## Notes for future readers

This ADR is the strategic pivot point of the project. If this product succeeds, this is the call that made it work. If it fails, this is the call to examine first. The reasoning is sound (second-mover validation is a real pattern) but the execution risk is real (depth-to-breadth pivots have killed more startups than they have saved). Future-me should read this with that context in mind.
