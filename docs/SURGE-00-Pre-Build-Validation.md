# SURGE-00 — Pre-Build Validation

**Surge ID:** S-00
**Name:** Pre-Build Validation
**Type:** NON-BUILD Surge (no code, no migrations, no Filament resources)
**Estimated duration:** 3–6 weeks
**Depends on:** Nothing
**Gates:** All subsequent Surges (S-01 through S-06)

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | This Surge **resolves** the wedge — no `[REVISIT-AFTER-AI-TEST]` flag applies, this Surge IS the test |
| Legal domain | `[PENDING-LEGAL-REVIEW]` — entire Surge gated on lawyer co-founder/advisor |
| Sign-off | PENDING — Founder + (future) Legal Advisor |

---

## Goal

Resolve every strategic blocker (B1–B5 in README) before a single migration is written. Specifically:

1. Determine whether the wedge is **Arabic depth (A)** or **integrated single surface (B)** by grading HAQQ's AI Twin against a real bilingual contract.
2. Secure a lawyer co-founder or formal advisor who can act as the legal stakeholder for Sign-Off Logs.
3. Validate willingness-to-pay and feature priorities with 5–8 real small-firm Levant lawyers.
4. Lock the PRD (Product Requirements Document) v1.0.
5. Produce validated Figma wireframes for SURGE-01, SURGE-02, SURGE-03.
6. Resolve the Cloudways underlying provider + region decision (D-01).
7. Resolve the geography contradiction (B5) — confirm Levant-first vs GCC-pivot.

---

## Why this Surge exists

AODC's Critical Gate says: no development begins without documented stakeholder sign-off. There is no lawyer stakeholder yet. There is no validated wedge yet. There is no PRD yet. There are no wireframes yet. Skipping this Surge means SURGE-01 onward is built on assumption, which is precisely what AODC's Clarifying Questions Gate exists to prevent.

This Surge has no Software-Engineer-agent output. It has Founder output. The Software Engineer agent should **refuse to produce tech tasks for S-01 onward until S-00 deliverables are complete.**

---

## Flows

### F-00.1 — Run the Arabic AI Test against HAQQ

**Duration:** 2–3 days
**Owner:** Founder (+ lawyer advisor if secured)
**Output:** A graded report stored at `validation/01_haqq_ai_test_report.md`

**Method:**
1. Sign up for HAQQ's chat.haqq.ai at the Starter or Pro tier.
2. Prepare 5 test prompts in Arabic and 5 in English, covering:
   - Draft an NDA clause for a Jordanian commercial transaction
   - Review a Lebanese-law sale-of-shares clause for buyer-favourability
   - Translate an English commercial-lease clause to Arabic with Jordanian-law terminology
   - Identify risks in an Iraqi distributorship agreement
   - Suggest fallback positions for a Palestinian-law service-agreement liability cap
3. Grade each response on three axes (1–5 scale):
   - **Legal accuracy** (would a Levant commercial lawyer rely on this?)
   - **Arabic fluency** (is the Arabic grammatically correct and legally idiomatic?)
   - **Jurisdiction-awareness** (does it actually reflect Jordan/Lebanon/Palestine/Iraq law, or is it generic Anglosphere-trained output?)
4. Total score determines wedge:
   - **≤ 30/75 → Wedge A wins** (Arabic depth is the differentiator)
   - **≥ 60/75 → Wedge B wins** (integrated single surface is the differentiator)
   - **31–59 → both wedges remain viable** — proceed with Wedge A as default, design Wedge B compatibility into S-03

**Acceptance:** Report exists, has scores, has a one-paragraph wedge decision.

---

### F-00.2 — Secure Lawyer Co-Founder or Advisor `[PENDING-LEGAL-REVIEW]`

**Duration:** 2–6 weeks (this is the actual schedule risk)
**Owner:** Founder
**Output:** A signed advisory or co-founder agreement, stored at `validation/02_legal_stakeholder_agreement.pdf`

**Method:**
1. Source candidates from: existing network, MENA legal-tech LinkedIn, Levant bar associations, Crescent Petroleum / Aramex / Bank Audi alumni networks (corporate lawyers who left for adventure).
2. Target profile: 5–10 years commercial-contracts experience in at least one Levant jurisdiction (Jordan/Lebanon/Palestine/Iraq), bilingual AR/EN, willing to commit ≥10h/week to product validation.
3. Run 8–12 first conversations; convert 2–3 to deep dives; convert 1 to formal commitment.

**Acceptance:** Lawyer commits in writing (advisory letter or co-founder agreement); their name + role + start date is recorded in the Sign-Off Log of every subsequent Surge.

**Failure mode:** If no commitment by week 6 of S-00, **escalate to a re-plan** — the build pipeline pauses. Strategic Brief flags this as non-negotiable.

---

### F-00.3 — Customer Discovery Interviews

**Duration:** 2–3 weeks
**Owner:** Founder (lawyer advisor accompanies if secured)
**Output:** A discovery report at `validation/03_discovery_interviews.md` with: raw notes, themes, willingness-to-pay data, feature priority ranking.

**Method:**
1. Identify 12–15 candidate firms (2–10 lawyers, Levant-based, commercial-contracts focus).
2. Source via: bar association directories, LinkedIn, founder's network, lawyer advisor's network.
3. Run 8 structured interviews. Each interview:
   - 10 min: current contract workflow (Word? email? HAQQ? Clio? something else?)
   - 10 min: pain points specific to bilingual contracts
   - 10 min: pain points specific to clause reuse, version control, redline
   - 10 min: willingness-to-pay (show the 3 wedge framings, ask which would they pay for, how much/month)
   - 10 min: openness to be a paying pilot in 8–12 weeks

**Acceptance:**
- ≥ 5 of 8 interviews completed
- ≥ 3 firms verbally interested in piloting
- ≥ 1 firm offered a contingent prepay (signal of real intent)
- Willingness-to-pay anchor established (median monthly per-seat price)

**Failure mode:** If < 3 firms show pilot interest, **the wedge is wrong** — return to Phase 0 scoping. Do not proceed to S-01.

---

### F-00.4 — Lock PRD v1.0

**Duration:** 3–5 days
**Owner:** Founder (+ lawyer advisor sign-off)
**Output:** `validation/04_PRD_v1.0.md`

**PRD must contain (at minimum):**
1. Product vision (one paragraph — narrower than the roadmap's hypothesis)
2. Target user persona (one persona for MVP — e.g., "Jordanian senior associate at a 4-lawyer corporate boutique")
3. Job-to-be-Done statement
4. Top 10 features (mapped to Surges S-01 through S-06)
5. Explicit non-goals (mirror Section 4 of the Roadmap)
6. Success metrics (from Section 9 of the Roadmap, refined)
7. Pricing thesis (single tier or freemium? Anchor price?)
8. Competitive positioning paragraph vs HAQQ (post-AI-test)
9. Sign-off table: Founder + Legal Advisor + (optional) 1 design pilot

**Acceptance:** PRD exists, has all 9 sections, has founder + advisor signature/initials with date.

---

### F-00.5 — Design + Validate Figma Wireframes for S-01–S-03

**Duration:** 2–3 weeks
**Owner:** Founder (using AI design assistance or external designer)
**Output:** Figma file URL, walked-through with ≥ 3 of the discovery-interview firms.

**Scope:**
- All screens for SURGE-01 (auth, workspace creation, locale switch, basic Filament admin)
- All screens for SURGE-02 (Contact list/create/edit, Matter list/create/edit)
- All screens for SURGE-03 (Document workspace — the wedge surface)
- RTL layouts shown explicitly (not derived from LTR — they look different)
- All states: empty, loading, error, success
- All breakpoints: desktop primary, tablet secondary (mobile NOT supported MVP)

**Validation:**
- Walk the prototype through ≥ 3 firms from F-00.3
- Record reactions
- Iterate at least once

**Acceptance:** Figma file URL recorded; sign-off from Founder + Legal Advisor + 1 pilot firm.

**This deliverable is the gate to producing Wave-Ready Packages.** Without validated Figma, AODC explicitly prohibits Wave-level specification.

---

### F-00.6 — Resolve Cloudways Provider + Region (D-01)

**Duration:** 1–2 days
**Owner:** Founder
**Output:** A one-page decision note at `validation/06_infra_decision.md`

**Method:**
1. List candidate Cloudways underlying providers + regions:
   - DigitalOcean Frankfurt FRA1 (EU, GDPR jurisdiction)
   - DigitalOcean Amsterdam AMS3 (EU)
   - AWS Bahrain me-south-1 (GCC — for data residency claim)
   - Vultr Frankfurt (EU alternative)
2. Decision criteria:
   - Data residency story vs HAQQ (which markets UAE residency)
   - Latency from Amman/Beirut/Ramallah/Baghdad
   - GDPR vs sharia-compliant data-handling expectations of pilot firms
   - Cost
3. Decide; document why; lock for SURGE-01.

**Acceptance:** Decision documented and signed by Founder.

---

### F-00.7 — Confirm or Pivot Geography (B5)

**Duration:** 0.5 day
**Owner:** Founder
**Output:** Single-paragraph confirmation in the PRD (F-00.4)

**Method:** A short founder decision: is the strategy still **Levant-first MVP / GCC Year-2**, or has it shifted to **GCC-first**?

**Impact if Levant-first (current):**
- Localization: bilingual AR/EN equal weight
- Currency defaults: JOD primary, USD secondary
- Cloud region: EU-jurisdiction (Frankfurt/Amsterdam) plausible
- Pricing: lower anchor (~$30–60/seat/month)

**Impact if GCC-pivot:**
- Localization: AR primary, EN secondary
- Currency defaults: AED or SAR primary, USD secondary
- Cloud region: GCC-resident strongly preferred
- Pricing: higher anchor (~$80–250/seat/month — matches HAQQ tiers)
- Compliance: KSA PDPL or UAE PDPL may apply

**Acceptance:** Single line in PRD: "Geography: Levant-first / GCC-pivot" with rationale.

---

## Deliverables checklist (gate to SURGE-01)

- [ ] F-00.1 — AI test report; wedge decision (A / B / both-viable) recorded
- [ ] F-00.2 — Lawyer co-founder/advisor secured; agreement on file
- [ ] F-00.3 — ≥ 5 customer interviews complete; ≥ 3 pilot-interested firms
- [ ] F-00.4 — PRD v1.0 signed by Founder + Legal Advisor
- [ ] F-00.5 — Figma wireframes for S-01/02/03 validated by ≥ 3 firms
- [ ] F-00.6 — Cloudways provider + region decision documented
- [ ] F-00.7 — Geography confirmed in PRD

**SURGE-01 cannot start until ALL seven items are checked.** This is the AODC Critical Gate.

---

## What the Software Engineer agent should do for this Surge

**Nothing.** No tech tasks. No migrations. No Filament resources. No code.

If asked to produce S-01 tasks before S-00 deliverables are complete, the Software Engineer agent should **refuse and surface the missing items.** This is the same pattern AODC uses for the Clarifying Questions Gate — gated outputs require gated inputs.

---

## Out of scope for this Surge

- Any code
- Tech-stack decisions other than D-01 (those are locked in the Roadmap)
- LLM provider selection (deferred to S-04 planning, after AI test)
- Pricing-tier specifics (deferred to S-06)
- Brand / domain / logo (separate work stream, not gated by AODC)
