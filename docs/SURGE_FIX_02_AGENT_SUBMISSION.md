# Engineer Agent Submission — SURGE-FIX-02

> **Use:** Open a fresh Engineer agent session. Paste the prompt below (the entire code block).
>
> **Prerequisite:** All 7 files committed to repo first:
> - `planning/SURGE-FIX-02-Case-Management-Refinement.md`
> - `planning/F-FIX-02-01-Hearing-Session-History.md`
> - `planning/F-FIX-02-01-Hearing-Session-History-WRP-1.md`
> - `planning/F-FIX-02-02-Court-Review-Trainee-Dispatch.md`
> - `planning/F-FIX-02-03-Forked-Matter-Creation.md`
> - `planning/F-FIX-02-04-Contextual-Quick-Timer.md`
> - `planning/F-FIX-02-05-Hearing-Postponement-Chain-UX.md`
> - `planning/F-FIX-02-06-Case-Management-Navigation-Refinement.md`

---

## Commit sequence (run before sending agent prompt)

```bash
cd <repo-root>
git add planning/SURGE-FIX-02-Case-Management-Refinement.md
git add planning/F-FIX-02-01-Hearing-Session-History.md
git add planning/F-FIX-02-01-Hearing-Session-History-WRP-1.md
git add planning/F-FIX-02-02-Court-Review-Trainee-Dispatch.md
git add planning/F-FIX-02-03-Forked-Matter-Creation.md
git add planning/F-FIX-02-04-Contextual-Quick-Timer.md
git add planning/F-FIX-02-05-Hearing-Postponement-Chain-UX.md
git add planning/F-FIX-02-06-Case-Management-Navigation-Refinement.md
git commit -m "planning(s-fix-02): case management refinement surge — 6 flows from khaldoun advisor input"
git push origin main
```

---

## The prompt to send

```
SURGE-FIX-02 — Case Management Refinement.

Practicing commercial lawyer Khaldoun Khater (Al-Dujani Office, Amman, 12 
years experience) has provided practitioner input across three conversations
including a competitive review of HAQQ.ai. His input identifies six refinement
areas in the Case Management surface (Matter / Hearing / Court Review / 
Service Log) where the current implementation does not match how Levant 
commercial litigators actually work.

His full input is logged in:
  validation/02_advisor_meeting_log.md (Conversations 3 and 3.5)

The Surge plan with 6 Flows lives at:
  planning/SURGE-FIX-02-Case-Management-Refinement.md

Each Flow has its own .md file:
  planning/F-FIX-02-01-Hearing-Session-History.md
  planning/F-FIX-02-02-Court-Review-Trainee-Dispatch.md
  planning/F-FIX-02-03-Forked-Matter-Creation.md
  planning/F-FIX-02-04-Contextual-Quick-Timer.md
  planning/F-FIX-02-05-Hearing-Postponement-Chain-UX.md
  planning/F-FIX-02-06-Case-Management-Navigation-Refinement.md

Flow F-FIX-02.1 (Hearing Session History) has a Wave-Ready Package at:
  planning/F-FIX-02-01-Hearing-Session-History-WRP-1.md

The other Flow .md files contain sufficient specification to translate 
directly into TTPs without separate Wave-Ready Packages.

CRITICAL CONTEXT:

1. INFORMAL ADVISOR INPUT (per CLAUDE.md §4.f) — Khaldoun's input IS captured
   in code via this Surge with citation comments. This is INFORMAL advisor
   validation. The formal lawyer signoff hard stops in CLAUDE.md §10 are NOT
   cleared by this Surge. Production deployment of S-08 surfaces remains RED
   until the separately-engaged paid lawyer signs the formal attestation 
   files.

2. ADDITIVE ONLY — Every migration in this Surge is additive. No column
   drops. No existing enum value removals. Backward compatibility with the
   677-test baseline from S-01 through S-FIX-01 must be preserved.

3. TEST DISCIPLINE — Minimum 50 new Pest tests across the 6 Flows. The 
   industry baseline gap (currently 677 vs ~2500 target) cannot widen 
   further. Each Flow's spec lists minimum test counts; total ≥ 50.

4. CITATION DISCIPLINE — Every new class and significant method added by 
   this Surge requires a code comment referencing the specific decision 
   number from validation/02_advisor_meeting_log.md. CI grep will verify 
   this (per CLAUDE.md §9 requirement #25 from S-FIX-01).

5. PRE-EXISTING-DATA AUDIT — Where Flows touch existing data (Matter 
   backfill in F-FIX-02.3, etc.), produce audit CSVs in validation/ for 
   founder manual review. Do NOT auto-correct existing rows.

REQUEST 1 — Produce a Gate Status Report covering all 6 Flows.

For each Flow:
- BUILD status: GREEN / AMBER / RED
- PRODUCTION-DEPLOY status: GREEN / AMBER / RED  
- Reasons for AMBER or RED
- Migration safety assessment (must be additive)
- Test count projection per Flow (target: ≥50 cumulative)
- Dependencies on other Flows in this Surge
- Backward-compat concerns with existing 677-test baseline
- Estimated effort in hours

Pay specific attention to:

A. F-FIX-02.3 (Forked Matter Creation) — backfill migration assigns default
   matter_type to existing Matters. The default must be conservative:
   is_litigation=true → 'commercial_litigation'; is_litigation=false → 
   'commercial_contracts'. Every backfill must produce an audit CSV row.

B. F-FIX-02.1 (Hearing Session History) — HearingActionItem auto-creates an
   Obligation via observer. Verify transaction safety: if Obligation creation
   fails, HearingActionItem creation rolls back.

C. F-FIX-02.4 (Contextual Quick Timer) — single-active-timer constraint per
   user enforced at service layer. Verify race condition handling (two 
   concurrent start requests from same user).

D. F-FIX-02.6 (Navigation Refinement) — verify no broken bookmarks for 
   existing users (URLs unchanged; only navigation grouping changes).

E. F-FIX-02.5 (Postponement Chain) — circular reference detection must 
   prevent A→B→A loops. Test must explicitly attempt to create one.

Include in your GSR:

- Cumulative test-count audit: baseline 677; S-FIX-02 target ≥ 50 new; total
  after S-FIX-02 should be ≥ 727. Industry target for system this size: 
  2,500-4,000. Flag this gap as standing risk.

- Architectural debt flag list: anything in the Surge plans you suspect 
  cannot ship cleanly at the proposed effort estimates.

- Sequencing recommendation: confirm or revise the suggested order 
  (F-FIX-02.6 → F-FIX-02.3 → F-FIX-02.1 + F-FIX-02.2 parallel → F-FIX-02.5 
  → F-FIX-02.4).

REQUEST 2 (DO NOT EXECUTE YET — wait for "proceed to F-FIX-02.6" reply):

After GSR approval, produce TTPs one Flow at a time in the sequenced order.
Each TTP follows the standard 11-section structure with migrations, models,
services, Filament resources, API endpoints, tests, OpenAPI updates, and
explicit code attribution comments.

Confirm receipt by producing the GSR (REQUEST 1) only. Stop after GSR. 
Do not begin code work until I reply "proceed to F-FIX-02.6".
```

---

## What to expect

The agent should return within ~15 minutes with:

1. Confirmation it has read all 8 files
2. GSR table covering all 6 Flows
3. Specific responses to attention points A through E
4. Test count projection per Flow
5. Cumulative test-count gap analysis
6. Architectural debt list
7. Sequencing confirmation or revision
8. A "stop and wait" acknowledgment

## What to verify in the GSR

Before authorizing F-FIX-02.6, check the GSR for:

1. **All 6 Flows show GREEN build status** — if any are AMBER or RED, understand why before authorizing
2. **All migrations confirmed additive** — no breaking changes
3. **Test count ≥ 50 projected** — if less, push back; quote the prompt
4. **Each Flow's effort estimate is within stated range** — if significantly higher, ask why
5. **Sequencing matches the plan** — or has a defensible revision
6. **Citation discipline acknowledged** — agent commits to decision-number citations in code

## Authorization sequence

After GSR is satisfactory:

```
proceed to F-FIX-02.6
```

Wait for TTP. Review TTP. If clean, authorize:

```
proceed to execute F-FIX-02.6
```

After execution and verification:

```
F-FIX-02.6 verified. Proceed to F-FIX-02.3 TTP.
```

Continue this cycle per Flow until Surge completion.

---

## What NOT to do

- Do NOT paste this prompt before committing all 8 planning files. The agent must be able to read them.
- Do NOT authorize multiple Flows at once. The Flow-by-Flow cycle is the discipline that keeps quality high.
- Do NOT skip the GSR. The GSR is your last chance to catch design issues before code starts.
- Do NOT promise Khaldoun any of these features will ship in a specific timeframe until the GSR confirms feasibility.

---

## After SURGE-FIX-02 ships

The next planning actions:

1. Append Conversation 4 to advisor meeting log (post-Monday walkthrough notes — if Monday happens before this Surge completes, capture it then; if after, capture then)
2. Run VERIFY-03 (production readiness audit) — see `planning/COMPREHENSIVE_BACKLOG_2026-06-23.md`
3. Begin engaging the paid lawyer Khaldoun is introducing (Decision #23)
4. Schedule Khaldoun's post-implementation walkthrough to confirm the 6 Flows match his expectations
