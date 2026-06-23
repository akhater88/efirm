# SURGE-FIX-02 — Case Management Refinement & Advisor Workflow Corrections

**Surge ID:** S-FIX-02
**Name:** Case Management Refinement
**Type:** CORRECTION + FEATURE Surge
**Estimated duration:** 7–10 days at Engineer agent velocity
**Depends on:** SURGE-FIX-01 complete; SURGE-01 through SURGE-13 shipped
**Gates:** Should ship BEFORE any further S-10/S-11/S-12 feature work
**Source:**
- `validation/02_advisor_meeting_log.md` Conversation 3.5 (Khaldoun competitive review of HAQQ.ai)
- `validation/02_advisor_meeting_log.md` Conversation 3 (Khaldoun's three pain stories)
- Founder direct observation of current Hearing implementation
**Status:** ACTIVE

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None — refinements to practice-management surfaces |
| Legal domain | Informal advisor input (Khaldoun Khater). Does NOT clear S-08 production hard stop. |
| Sign-off | Founder + Khaldoun informal confirmation. Production deployment still RED until paid lawyer signoff per CLAUDE.md §10. |
| Backward compatibility | All migrations additive. No column drops. No enum value removals. |
| Test discipline | Minimum 50 new Pest tests across all 6 Flows. |

---

## Goal

Apply Khaldoun's competitive analysis of HAQQ.ai plus his earlier practitioner observations to refine the Case Management surfaces. Specifically:

1. Make the Hearing view show actual session content (judge statements, our submissions, follow-up actions) — not just a flat date-and-status entry
2. Make Court Reviews usable for the "dispatch a trainee to the Palace of Justice" workflow
3. Fork the Matter creation flow at the start so transactional Matters don't get litigation field clutter
4. Make the time tracker contextual to the Matter/Hearing being worked on
5. Surface the Hearing postponement chain in the UI so cases delayed by procedural issues are visible
6. Refine Case Management navigation per Khaldoun's confirmed 4-pillar taxonomy

By the end of this Surge, the Case Management surface (Matters, Hearings, Court Reviews, Service Log) matches how a Levant commercial litigator actually works, not how Western legal-tech assumes lawyers work.

---

## Flows

This Surge contains 6 Flows. Each has its own .md file with full specification. Engineer agent submits Gate Status Report covering all 6, then produces TTPs one Flow at a time.

| Flow | Name | Source | Effort | Wave-Ready Package |
|---|---|---|---|---|
| F-FIX-02.1 | Hearing Session History | Decision #28 + founder observation | ~2 days | Yes — multi-Wave |
| F-FIX-02.2 | Court Review Trainee Dispatch | Decision #29 | ~1 day | Yes — multi-Wave |
| F-FIX-02.3 | Forked Matter Creation Workflow | Decision #26 | ~2 days | Yes — multi-Wave |
| F-FIX-02.4 | Contextual Quick Timer | Decision #31 | ~1 day | Yes — multi-Wave |
| F-FIX-02.5 | Hearing Postponement Chain UX | Inferred from Decision #28 context | ~0.5 day | Single-Wave (no WRP needed) |
| F-FIX-02.6 | Case Management Navigation Refinement | Decision #27 | ~0.5 day | Single-Wave (no WRP needed) |

Total estimated effort: ~7 days at Engineer agent velocity.

Each Flow file lives at `planning/F-FIX-02-NN-{name}.md`. Each Wave-Ready Package lives at `planning/F-FIX-02-NN-{name}-WRP-N.md`.

---

## Sequencing

The Flows can ship mostly in parallel, but recommended order:

1. **F-FIX-02.6** (Navigation Refinement) — small, fast, no dependencies. Confirms Filament navigation structure before bigger Flows touch the same area.
2. **F-FIX-02.3** (Forked Matter Creation) — touches Matter creation; foundational for downstream Flows.
3. **F-FIX-02.1** (Hearing Session History) and **F-FIX-02.2** (Court Review Trainee Dispatch) — parallel; both touch S-08 surfaces.
4. **F-FIX-02.5** (Postponement Chain UX) — depends on F-FIX-02.1 completing first.
5. **F-FIX-02.4** (Contextual Quick Timer) — last; touches multiple surfaces (Matter, Hearing, Document); benefits from those surfaces being stable.

---

## Surge acceptance criteria

- [ ] All 6 Flows built and tested
- [ ] All Pest tests green (minimum 50 new tests added)
- [ ] Larastan level 6 clean
- [ ] Pint clean
- [ ] OpenAPI spec updated for new/changed endpoints
- [ ] No regression in S-01 through S-FIX-01 tests (677 baseline)
- [ ] Migration safety verified — all additive, backward-compatible
- [ ] Code comments cite `validation/02_advisor_meeting_log.md` decision numbers
- [ ] CLAUDE.md updated to v6 documenting any new entities or non-negotiables
- [ ] Founder sign-off recorded in `validation/02_advisor_meeting_log.md` under "S-FIX-02 implemented"
- [ ] Khaldoun informal review scheduled post-implementation (target: within 2 weeks of Surge completion)

---

## What this Surge does NOT do

- Does not address the formal lawyer signoff hard stops in CLAUDE.md §10 (those still require paid attorney)
- Does not implement the Year-2 backlog features (B-01 Expert Objection Workbench, B-02 Regulatory Knowledge Retrieval, B-03 Cross-Coverage Tracking, B-04 Client Portal)
- Does not touch the document editor surface (S-03) — that work is gated by Monday walkthrough findings if any
- Does not deploy to production — that requires the production-readiness audit (VERIFY-03) plus paid lawyer signoffs
- Does not change AI prompt content — those changes need paid lawyer review first

---

## Engineer agent handoff prompt

After this Surge plan is committed to the repo, send the following to a fresh Engineer agent session:

```
PRIORITY SURGE — SURGE-FIX-02 Case Management Refinement.

Practicing commercial lawyer Khaldoun Khater has provided:
1. A competitive review of HAQQ.ai identifying their Case Management 
   architectural strengths and weaknesses
2. Specific framing for how the Hearing view should work in commercial 
   litigation practice  
3. Confirmation that the current Hearing view (flat date-type-status) 
   does not match practitioner expectations
4. Three pain-point stories revealing workflow friction in the current 
   product

His full input is logged in:
  validation/02_advisor_meeting_log.md (Conversations 3, 3.5)

This Surge plan applies his input as 6 Flows. The Surge plan lives at:
  planning/SURGE-FIX-02-Case-Management-Refinement.md

Each Flow has its own .md file:
  planning/F-FIX-02-01-Hearing-Session-History.md
  planning/F-FIX-02-02-Court-Review-Trainee-Dispatch.md
  planning/F-FIX-02-03-Forked-Matter-Creation.md
  planning/F-FIX-02-04-Contextual-Quick-Timer.md
  planning/F-FIX-02-05-Hearing-Postponement-Chain-UX.md
  planning/F-FIX-02-06-Case-Management-Navigation-Refinement.md

Each Flow exceeding single-Wave scope also has Wave-Ready Packages 
at planning/F-FIX-02-NN-{name}-WRP-N.md.

INFORMAL VS FORMAL DISTINCTION (per CLAUDE.md §4.f):

Khaldoun's input is INFORMAL advisor validation. Code comments cite 
decision numbers from the advisor meeting log. This Surge does NOT 
clear the formal lawyer signoff hard stops in CLAUDE.md §10. 
Production deployment of S-08 surfaces remains RED.

REQUEST 1 — Produce a Gate Status Report covering all 6 Flows.

For each Flow:
- BUILD status: GREEN / AMBER / RED
- PRODUCTION-DEPLOY status: GREEN / AMBER / RED  
- Migration safety assessment (all must be additive)
- Test count projection per Flow (target: ≥50 cumulative across Surge)
- Dependencies on other Flows in this Surge
- Backward-compat concerns

REQUEST 2 (after I approve the GSR) — Produce TTPs one Flow at a time, 
following the sequencing in the Surge plan: F-FIX-02.6 first, then 
F-FIX-02.3, then F-FIX-02.1 and F-FIX-02.2 in parallel, then 
F-FIX-02.5, then F-FIX-02.4.

For each Flow, the Wave-Ready Package serves as the design specification. 
Translate it into a Tech Task Package (TTP) covering migrations, models, 
services, Filament resources, API endpoints, tests, and OpenAPI spec 
updates.

Confirm receipt and produce the GSR (REQUEST 1) only. Wait for "proceed 
to F-FIX-02.6" before REQUEST 2.
```
