# SURGE-FIX-02 — Post-Walkthrough Corrections (PLACEHOLDER)

**Surge ID:** S-FIX-02
**Name:** Post-Walkthrough Corrections
**Type:** CORRECTION Surge (placeholder; scope determined Tuesday 2026-06-24)
**Estimated duration:** Unknown — depends on Monday walkthrough findings
**Depends on:** SURGE-FIX-01 complete (or in progress); Monday 2026-06-23 walkthrough with Khaldoun Khater complete
**Gates:** Should ship BEFORE SURGE-10 build resumes if findings are foundational
**Source:** `validation/02_advisor_meeting_log.md` Conversation 4 (Monday walkthrough — to be logged Tuesday)
**Status:** SCAFFOLDING — populated Tuesday morning post-meeting

---

## How to use this file

This Surge plan is intentionally empty. It exists as a structural placeholder so that Monday's walkthrough findings have a defined home Tuesday morning, rather than getting lost in conversation memory or scattered across ad-hoc notes.

**Tuesday 2026-06-24 morning workflow:**

1. Open `validation/02_advisor_meeting_log.md` and write Conversation 4 entry (full transcript or detailed notes from Monday)
2. Walk through every finding using the triage rubric (Section 3 below)
3. Each finding becomes one of: a Flow in this Surge, an entry in the feature backlog, an immediate hotfix outside any Surge, or a "no action" with rationale
4. After triage, replace this file's "Flows" section with the populated Flow list, update status from PLACEHOLDER to ACTIVE, commit
5. Hand off to Engineer agent with a Gate Status Report request

---

## 1. Intake — Monday walkthrough findings

> Populate this section Tuesday morning. One row per finding, in the order they surfaced during the walkthrough.

| # | Finding (Khaldoun's words or close paraphrase) | Surface affected | Severity (Khaldoun's framing) | Severity (founder triage) | Triage decision |
|---|---|---|---|---|---|
| F1 | [To populate] | [Document editor / AI panel / Litigation / etc.] | [Critical / High / Medium / Low / Nice-to-have] | [Same scale, founder's view] | [Flow / Backlog / Hotfix / No action] |
| F2 | | | | | |
| F3 | | | | | |
| F4 | | | | | |
| F5 | | | | | |

*(Add rows as needed. Expect 5–15 findings from a 90-minute walkthrough with a real practitioner seeing the product for the first time.)*

---

## 2. Walkthrough metadata

Populate Tuesday morning:

| Field | Value |
|---|---|
| Date of walkthrough | 2026-06-23 |
| Location | Café near Palace of Justice (قصر العدل), Amman |
| Duration (actual) | [hours:minutes] |
| Attendees | Abdullah (founder); Khaldoun Khater (advisor, Al-Dujani Office) |
| Test data used | [Anonymized real contract from Khaldoun] OR [Pre-prepared sample] |
| Demo flow followed | [Standard 5-step: workspace → contract import → editor → AI panel → version history → export] OR [Other — describe] |
| Recording / notes | [Founder's notes; no audio recording — Khaldoun working in his professional capacity] |
| Sentiment summary | [One sentence on Khaldoun's overall reaction] |
| Verbatim quotes worth preserving | [Phrases Khaldoun used that capture pain or praise specifically; these go in marketing/pitch material later] |

---

## 3. Triage rubric — turning findings into actions

Every finding from the walkthrough gets classified using this rubric. Tuesday morning, walk each finding through this decision tree.

### Step 1 — Severity classification

| Severity | Meaning | Default action |
|---|---|---|
| **Critical** | Product is broken in a way that blocks core use. A paying lawyer cannot complete a normal task. | Hotfix immediately; do not wait for Surge planning |
| **High** | Significant friction during real use; lawyer would stop using a specific feature and revert to Word/Excel for that workflow | Flow in this Surge (S-FIX-02) |
| **Medium** | Real friction but lawyer would tolerate it for now; affects efficiency not capability | Flow in this Surge OR feature backlog depending on effort |
| **Low** | Polish, cosmetic, edge-case behavior | Feature backlog; address opportunistically |
| **Nice-to-have** | New feature idea not connected to a specific friction | Feature backlog only; do not put in S-FIX-02 |

### Step 2 — Effort classification

| Effort | Meaning |
|---|---|
| **Hotfix** | < 4 hours of agent work; ships independently outside any Surge |
| **Small Flow** | 4–8 hours; one Wave within a Flow |
| **Standard Flow** | 1–3 days; multi-Wave Flow with schema changes |
| **Surge** | More than 3 days; doesn't fit in S-FIX-02; gets its own Surge plan |

### Step 3 — Decision matrix

| Severity ↓ / Effort → | Hotfix | Small Flow | Standard Flow | Surge |
|---|---|---|---|---|
| **Critical** | Immediate hotfix | Hotfix this week | Hotfix this week, refactor into Flow later | Block S-10 build; promote to its own Surge ahead of S-10 |
| **High** | Hotfix this week | Flow in S-FIX-02 | Flow in S-FIX-02 | Promote to own Surge after S-FIX-02 |
| **Medium** | Hotfix if quick wins | Flow in S-FIX-02 | Backlog if S-FIX-02 already full | Backlog |
| **Low** | Backlog | Backlog | Backlog | Backlog |
| **Nice-to-have** | Backlog | Backlog | Backlog | Backlog |

### Step 4 — Hard-stop check

For any finding that affects:
- Litigation procedural model (S-08)
- Trust account handling (S-09 F-09.2)
- AI prompt content
- Legal document content (ToS, Privacy, DPA, AI Disclaimer)

Even if it would normally go to Backlog, surface it explicitly to be reviewed by the formal paid lawyer (when engaged) before production. Mark with `[REQUIRES-PAID-LAWYER-REVIEW]` flag.

---

## 4. Flows

> POPULATE TUESDAY MORNING after triage. Each Flow gets one section using the standard template below.

### F-FIX-02.[N] — [Flow name]

**Goal:** [One sentence]

**Source:** `validation/02_advisor_meeting_log.md` Conversation 4, Finding #[F1, F2, ...] — Khaldoun's specific framing.

**Scope:**

- [Migration / model / service / Filament resource / etc. specifics]
- [Backward compatibility requirements]
- [Test specifications — minimum N Pest tests]

**Wave-Ready Package needed?** [Yes / No — Yes if Flow has > 1 Wave; No if single Wave fits in this scope]

**Estimated effort:** [hours or days]

**Dependencies on other Flows:** [None / F-FIX-02.N / S-XX F-XX.N]

**Acceptance:**
- [Specific verifiable acceptance criteria]

**`[PROVISIONAL-FOUNDER-DECIDED]` or `[REQUIRES-PAID-LAWYER-REVIEW]` items:**
- [Items to capture for advisor follow-up or lawyer review]

**Code attribution comment (for the agent):**
```
// Per advisor walkthrough finding, validation/02_advisor_meeting_log.md 
// Conversation 4, Finding #[F1]
```

---

*Repeat the Flow template above for each Finding triaged as "Flow in S-FIX-02" or "Standard Flow" in Section 3.*

---

## 5. Items routed to feature backlog (not in this Surge)

Populate Tuesday morning. Each item also gets a row in `validation/feature_backlog.md`.

| Finding # | Item | Why backlog (not Surge) | Backlog ID |
|---|---|---|---|
| F[N] | [Brief description] | [Low severity / Surge-sized / Year-2 candidate / Requires more discovery] | B-[NN] |

---

## 6. Items routed to immediate hotfix (outside this Surge)

Populate Tuesday morning. Each hotfix gets a Linear issue or direct prompt to the Engineer agent.

| Finding # | Hotfix | Estimated effort | Status |
|---|---|---|---|
| F[N] | [What to fix] | [hours] | [Linear issue # / In progress / Done] |

---

## 7. Items routed to "no action" with rationale

Populate Tuesday morning. Sometimes a finding is real but not worth acting on — document the reasoning so it doesn't get re-raised later.

| Finding # | Item | Why no action |
|---|---|---|
| F[N] | [Brief description] | [Below-threshold severity; out of scope; explicit founder decision to defer; etc.] |

---

## 8. Surge acceptance criteria

> POPULATE TUESDAY MORNING.

- [ ] All Flows F-FIX-02.1 through F-FIX-02.[N] built and tested
- [ ] All Pest tests green (minimum [N] new tests)
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated (estimated ~[N] new endpoints)
- [ ] No regression in S-01 through S-13 + S-FIX-01 tests
- [ ] Migration safety verified
- [ ] Code comments reference `validation/02_advisor_meeting_log.md` Conversation 4 Finding numbers
- [ ] CLAUDE.md updated to v6 if new entities, services, or non-negotiables added
- [ ] Founder sign-off recorded in `validation/02_advisor_meeting_log.md` under "Conversation 4 Findings Implemented"
- [ ] Khaldoun confirmation: post-implementation walkthrough scheduled and his sign-off captured

---

## 9. What this Surge does NOT do

> POPULATE TUESDAY MORNING with explicit non-goals based on findings that were considered and routed elsewhere.

- [Findings routed to feature backlog] — see Section 5
- [Findings routed to hotfix] — see Section 6
- [Findings routed to no action] — see Section 7

This Surge does NOT touch:
- Formal lawyer signoff items (those remain hard stops per CLAUDE.md §10)
- The 4 legal documents (ToS, Privacy, DPA, AI Disclaimer) — paid lawyer drafts those
- SURGE-10, S-11, S-12 build work — paused until S-FIX-02 ships if any findings are foundational

---

## 10. Engineer agent handoff

> POPULATE TUESDAY MORNING with the kickoff prompt that translates this Surge plan into a Gate Status Report request and then per-Flow TTPs.

Template:

```
PRIORITY CORRECTION SURGE — SURGE-FIX-02 (Post-Walkthrough Corrections).

The founder conducted a live product walkthrough with practicing 
commercial lawyer Khaldoun Khater on Monday 2026-06-23. The walkthrough 
surfaced [N] findings, captured in:

  validation/02_advisor_meeting_log.md (Conversation 4)

The Surge plan with triaged findings lives at:

  planning/SURGE-FIX-02-Post-Walkthrough.md

It contains [N] Flows applying the findings to the codebase. Total 
estimated duration: [N] days.

[Severity context — e.g., "Two findings are CRITICAL and require 
hotfix-style turnaround; the others are STANDARD and ship in normal 
Flow cadence."]

CONTEXT WHY THIS SURGE SHIPS BEFORE SURGE-10/11/12/13:

[Tuesday-morning reasoning — paste here based on findings severity]

REQUEST 1 — Produce a Gate Status Report (GSR) covering all [N] Flows.

For each Flow:
  - BUILD status: GREEN / AMBER / RED
  - PRODUCTION-DEPLOY status: GREEN / AMBER / RED
  - Reasons for AMBER or RED
  - Specific upstream gate files referenced
  - Dependencies on prior Surges' Flows
  - Migration safety assessment
  - Test count projection per Flow

Pay specific attention to:

[Tuesday-morning specific concerns — paste finding-specific watch items]

Include in your GSR:

  - Cumulative test-count audit
  - Architectural debt flag list
  - Hotfix prioritization recommendation (which Flow ships first)

Do not produce a TTP yet. Wait for my "proceed to F-FIX-02.[N]" reply.
```

---

## 11. Tuesday morning checklist

Use this when filling in the Surge Tuesday morning:

- [ ] Conversation 4 entry written in `validation/02_advisor_meeting_log.md` with full notes from Monday
- [ ] Every finding listed in Section 1 of this file with severity + triage decision
- [ ] Walkthrough metadata in Section 2 complete
- [ ] Each Flow populated in Section 4 (one section per "Flow in S-FIX-02" or "Standard Flow" decision)
- [ ] Backlog items added to both Section 5 of this file AND `validation/feature_backlog.md`
- [ ] Hotfix items added to Section 6 with Linear issues created
- [ ] "No action" items documented in Section 7 with rationale
- [ ] Acceptance criteria in Section 8 finalized
- [ ] Engineer agent prompt in Section 10 customized with specific context
- [ ] CLAUDE.md update plan drafted if new entities/services emerge
- [ ] Status header at top of this file updated from PLACEHOLDER to ACTIVE
- [ ] File committed to repo with message: `planning(s-fix-02): apply walkthrough findings from Khaldoun 2026-06-23`

---

## Notes on this scaffolding's design

**Why a placeholder Surge file exists before findings are known:**

Without scaffolding, Monday's findings get captured in WhatsApp threads, voice memos, or scattered notes. They lose triage discipline within 48 hours. The act of routing each finding through the explicit triage rubric (Section 3) on Tuesday morning prevents two failure modes:

1. **Cherry-picking findings the founder agrees with** — the rubric forces classification of every finding, including the uncomfortable ones
2. **Scope creep within the Surge** — the effort classification cap forces Surge-sized items into their own Surge plans rather than bloating S-FIX-02

**Why "no action" is an explicit triage destination:**

Section 7 ("no action with rationale") matters because some practitioner feedback is correct but not worth acting on. A founder who treats every advisor finding as a must-build will over-scope and never ship. Writing down WHY a finding is declined prevents the finding from being re-raised six months later as if it were new.

**Why hotfix is separate from this Surge:**

Critical findings ship outside any Surge plan — they're emergencies. Treating them as Flows within S-FIX-02 would delay them by the GSR/TTP review cycle. The discipline is: emergency = hotfix outside Surge; everything else = inside Surge.

---

*End of placeholder file. Update Tuesday morning per the checklist in Section 11.*
