# Advisor Meeting Agenda — Questions to Ask the Lawyer

> **Purpose:** Structured question set for the first three meetings with a prospective lawyer advisor. Designed to be skimmed during the meeting, not read line by line.
>
> **Total time:** Three meetings of 45–90 minutes each, spaced 1–2 weeks apart. Do NOT try to cover everything in one meeting.

---

## Before any meeting — preparation checklist

- [ ] Have you logged into the product yourself? You need to be able to demo it live or show working screens. Wireframes are a poor substitute.
- [ ] Have a copy of your one-page product summary ready (what it does, who it's for, what stage you're at)
- [ ] Have the validation files printed or ready to share: `00_FOUNDER_WAIVER`, the list of `[PROVISIONAL-FOUNDER-DECIDED]` items, the list of hard stops
- [ ] Bring a notebook. The advisor's specific phrasings matter and will get lost if you don't write them down.
- [ ] Bring water. They'll talk longer if you're prepared.
- [ ] If the meeting is virtual, share-screen the actual running product. Don't share PowerPoint.

---

## MEETING 1 — Qualification + Mutual Fit (45–60 minutes)

> Goal: Is this person right for the advisor role, and do they want it?
> Outcome: A clear yes/no decision on continuing.

### Opening (5 min) — Warm intro

- Brief background on yourself
- One-paragraph product summary (what it does, who it's for, why now)
- One sentence on why you're meeting them specifically — "[mutual connection / referral / specific reason you reached out]"

### Bucket 1 — Their practice (15 min)

- Tell me about your practice. What kinds of matters take up most of your time?
- What proportion is commercial contracts vs. litigation vs. other?
- How much of your work is in Arabic, English, or bilingual?
- What's the typical client profile — corporate, SMB, individual, government?
- Have you advised tech startups or SaaS products before?
- Are you currently bar-admitted and active in Jordan?

*Listen for: depth in commercial contracts, comfort with technology, real exposure to small-firm practice (your target customer), language mix matching your product's bilingual focus.*

*If the answers don't match the product's needs (e.g., they're a pure litigator or family-law specialist with no commercial work), thank them, ask for a referral to a commercial colleague, end the meeting politely.*

### Show, don't tell (15 min)

- Live demo (or screenshare) of the actual product or wireframes
- Walk through 2–3 key flows: create a Matter, import a contract, ask AI to review a clause, export back to .docx
- Their reactions during the demo are the most valuable information you'll get. Note specific moments where they:
  - Lean forward (something resonated)
  - Frown or hesitate (something is wrong or unclear)
  - Ask a follow-up question (this is the gold — write it down verbatim)

### Bucket 2 — Their interest in the role (15 min)

- What's your honest first reaction to what you just saw?
- Have you used any legal-tech products in your practice? Which? What worked and didn't?
- What are the biggest pain points you and your colleagues face that software hasn't solved?
- Is "advisor to a legal-tech startup" something you've considered before? Why or why not?
- What would an advisor commitment look like to you — hours per month, scope of involvement, communication style?
- What would make this worth your time?

*Listen for honest answers about motivation: equity, paid hourly, intellectual interest, ecosystem-building, name recognition. All are valid; you just need to know which.*

### Close (10 min)

- Be explicit: "Here's what an advisor relationship would look like for us..." (describe time commitment, compensation if any, scope)
- "Is that something you'd want to explore in a second meeting where we'd go deeper into the specific legal questions I need help on?"
- If yes: schedule meeting 2, send them the question pre-read 48h before
- If no or unclear: ask for referrals to colleagues who might be a fit

### Red flags from Meeting 1

If any of these happen, this is probably not the right person:

- They forward you AI-generated summaries instead of speaking from their own practice experience
- They have no specific stories from their own client work
- They want to immediately discuss compensation before understanding the scope
- They're vague about whether they're currently licensed and practicing
- They don't ask a single question about the product itself
- They claim expertise across every area of law (no one has it)
- They mention that they've never read a SaaS contract

---

## MEETING 2 — Big Decisions (90 minutes)

> Goal: Get expert input on the largest unresolved questions in the product.
> Pre-read sent 48h in advance: validation files + this question list.
> Outcome: At least 3–5 decisions documented; advisor relationship confirmed.

### Opening (5 min)

- Thanks for reviewing the pre-read
- Quick recap of what the advisor relationship will look like going forward
- Set expectation: today we cover BIG questions; details come later

### Bucket 3a — Litigation Modeling (20 min)

Refer to `planning/SURGE-08-Litigation-Modules.md` and the `[HARD-STOP-LAWYER-REQUIRED]` items in CLAUDE.md §10.

- Walk me through the lifecycle of a commercial dispute in Jordan from a client's first call to final judgment. What are the discrete procedural states?
- We have `litigation_status` enum values: `pre_filing, filed, in_evidence, in_judgment, appealed, closed_won, closed_lost, settled, withdrawn`. What's missing? What's miscategorized?
- We have `hearing_type` values: `first_session, evidence, expert_witness, witness_testimony, final_arguments, judgment, enforcement, other`. What's missing?
- What's the standard appeal window in Jordan for commercial first-instance decisions? Does it differ by court type?
- How should we model **expert reports (تقرير الخبير)?** Currently not explicit in our schema. Is this a hearing type, a document type, a court-review type, or something else?
- We have `representation_role` values: `plaintiff, defendant, intervenor, third_party`. Anything missing for Jordanian practice?
- Are there Bar Association rules on what data about hearings or judges can be stored electronically?

*Write down every specific enum value or status they mention that we're missing. This list directly drives the next migration.*

### Bucket 3b — Trust Accounts (20 min)

Refer to `planning/SURGE-09-Financial-CRM-Integrations.md` F-09.2 and the `[HARD-STOP-LAWYER+CPA-REQUIRED]` flags.

- Walk me through how trust accounts work at your firm in practice. Who has authority? What records are kept? What audit cycle?
- Under the Jordanian Lawyers Act, what are the specific requirements for:
  - Commingling (can two clients' funds be in one bank account?)
  - Interest accrual (where does interest go — client, firm, Bar Association?)
  - Reporting (what must be reported to whom, on what cadence?)
  - Reconciliation (mandatory frequency?)
- If a client deposits funds and the matter is dropped before any work, what's the regulated refund process?
- We're modeling trust ledger as append-only with offsetting-entry correction. Is that the standard approach in Jordanian practice, or do bar rules require a different mechanism?
- Do you have a CPA you work with who could review the financial side, or should I source one separately?

### Bucket 3c — Legal Documents for the SaaS itself (15 min)

These are the documents YOUR PRODUCT needs to be sold, distinct from documents your product produces.

- Have you ever drafted a SaaS Terms of Service for a Jordanian-licensed company?
- What are the non-obvious gotchas?
- Are there Jordanian consumer-protection rules that affect B2B SaaS contracts where the customer is a sole proprietor?
- What's the current state of Jordanian data protection law in 2026? Is there cross-border-transfer guidance for hosting in Frankfurt?
- Is there a difference between us being licensed as a tech company vs. as a legal-services provider for purposes of data handling?
- We need an AI Disclaimer for AI-generated content. Is there Jordanian case law or Bar Association guidance on AI-assisted legal work that should inform that document?
- Would you be willing to draft these documents for us, or would you refer them to a colleague?

### Bucket 3d — Unauthorized Practice of Law (15 min)

This is the question that determines whether your product can legally exist in its current form.

- We use AI to suggest contract revisions and to generate full contracts. Under Jordanian rules, where's the line between "AI tool assisting a lawyer" and "unauthorized practice of law"?
- Specifically: can a non-lawyer end-user (paralegal, business owner) use our product to generate a contract? Or must a lawyer be in the loop?
- Our AI outputs always include a disclaimer like "AI-generated. Not legal advice. Review before use." Is that disclaimer legally sufficient?
- What language would you recommend?
- Does the answer change if our product is sold only to law firms (B2B) vs. directly to businesses (B2B not-lawyer)?
- Are there specific bar rules about a non-lawyer-owned legal-tech company servicing law firms?

### Close (10 min)

- Summarize what they've committed to (specific documents to draft, specific signoffs to give, hours per month)
- Compensation discussion if you haven't had it yet — equity grant range, hourly rate, retainer
- Next meeting scheduled with specific topic and pre-read

### Documentation after Meeting 2

Within 24 hours, draft `validation/02_advisor_meeting_2_summary.md` containing:

- Date, attendees, duration
- Key decisions made (with the advisor's specific phrasing)
- Open items deferred to next meeting
- Committed deliverables and timelines (theirs and yours)
- Send the summary to the advisor for confirmation

---

## MEETING 3 — Implementation Details (60–90 minutes)

> Goal: Resolve specific `[PROVISIONAL-FOUNDER-DECIDED]` items and review draft deliverables.

### Pre-meeting prep

- Implement the changes from Meeting 2's decisions in code
- Draft what they've committed to draft (or get their drafts)
- Update the `[PROVISIONAL-FOUNDER-DECIDED]` list with their corrections

### Bucket 4 — Operational Detail (30 min)

Choose 5–8 of these based on what your product needs most:

- Walk me through how you'd document a typical hearing outcome in your current system. What fields would help you?
- When you draft a contract, do you start from a template, from a similar prior contract, or from scratch? Where would AI help in that flow?
- How do you currently manage client conflicts of interest? Should our software check, and how?
- For invoicing — what's the standard structure for a Jordanian lawyer's invoice? Are there mandatory fields, VAT considerations, retention requirements?
- For client communications — what gets logged in your file vs. what stays in email?
- How do you handle file retention after a matter closes? Is there a regulated retention period?
- What's your KYC process for new clients? What documents do you collect for individuals vs. companies?
- Are there specific bar rules about confidentiality of opposing parties' information in your case file?
- How are paralegals supervised in Jordanian practice? Does our role system (Owner/Admin/Member) make sense, or do we need a "supervising attorney" relationship?
- What would happen to your firm's files if you died tomorrow? Is there a regulated succession requirement?

### Bucket 5 — Strategic & Pricing (20 min)

- If you saw a competitor product positioning itself the way we are — bilingual, Levant-focused, SMB-priced — what would you predict are the three biggest reasons it could fail to get traction with real lawyers?
- What's the realistic timeline for a small Jordanian firm to switch from their current workflow (Word + Outlook + Excel) to a product like this?
- What pricing point breaks resistance? At what price do firms start saying yes without negotiation?
- Are there Bar Association political dynamics that affect adoption? Is there an officially-blessed legal tech product we'd be competing with for institutional buy-in?
- Would the Bar Association ever endorse or recommend us if we did things "right"? How would we earn that?

### Bucket 6 — Honest Critique (15 min)

This is the most valuable 15 minutes of all three meetings if the advisor is willing to give it.

- What's your honest view of what we're building?
- Where's our biggest blind spot?
- What did we get wrong that you've been too polite to mention?
- If you were me, what would you stop building?
- If you were me, what would you start building that we don't have?
- What's the question I should be asking you that I haven't?

*That last question is the most important question to ask any advisor. Always.*

### Close

- Document next steps
- Confirm the advisor agreement (if not already signed)
- Schedule recurring cadence (monthly check-ins; ad-hoc for specific signoffs)

---

## What you do AFTER Meeting 3

By this point you should have:

- [ ] A signed advisor agreement (equity grant or paid retainer)
- [ ] Documented decisions on litigation enum values, trust account requirements, AI disclaimer language
- [ ] Either drafted-by-advisor or commitment-to-draft for ToS, Privacy Policy, DPA
- [ ] A clear answer on unauthorized-practice-of-law question
- [ ] A list of `[PROVISIONAL-FOUNDER-DECIDED]` items the advisor has now corrected
- [ ] A clear next-meeting cadence

Update these files:
- `validation/02_legal_stakeholder_agreement.md` — signed
- `validation/08_litigation_lawyer_signoff.md` — populated with decisions
- `validation/09_trust_account_lawyer_signoff.md` — populated
- `validation/13_lawyer_management_advisor_review.md` — populated
- Each `prompts/*.md` file — add `[LEGAL-REVIEW-APPROVED: {advisor name}, {date}]` header
- Each `prompts/document_generation/*.md` file — same
- `CLAUDE.md` v5 — update §10 hard stops table with the resolved signoff files

Then tell the Engineer agent: "F-00.2 lawyer advisor secured. Hard stops in CLAUDE.md §10 marked as resolved per the validation files. Production deployment of S-08, S-09 F-09.2, S-10 F-10.4, and S-11 F-11.3 is now permitted."

---

## Quick reference — questions by document

If you'd rather organize by what you need the advisor to produce, here's the same questions sorted by deliverable:

**To unblock `validation/06_legal_docs/` (ToS, Privacy, DPA, AI Disclaimer):**
- Meeting 2 Bucket 3c — all questions
- Specific output: AR + EN drafts of all 4 documents

**To unblock `validation/08_litigation_lawyer_signoff.md`:**
- Meeting 2 Bucket 3a — all questions
- Specific output: a one-page sign-off note confirming the litigation_status enum, hearing_type enum, court_type enum, decision_type enum, and service_method enum are correct or revised

**To unblock `validation/09_trust_account_lawyer_signoff.md`:**
- Meeting 2 Bucket 3b — all questions
- Specific output: sign-off note confirming the trust_ledger model (append-only with adjustment offsets) matches Jordanian Bar requirements, and one specific item about interest accrual

**To unblock `prompts/*.md` and `prompts/document_generation/*.md`:**
- Meeting 2 Bucket 3d — all questions
- Each prompt template reviewed and either approved-with-header or revised-then-approved

**To resolve `[PROVISIONAL-FOUNDER-DECIDED]` items:**
- Spread across Meeting 2 Bucket 3a and Meeting 3 Bucket 4
- Each item gets a resolution: confirmed-as-is, revised, or marked-for-Year-2

---

## Final thought

You're not looking for a lawyer who agrees with everything you've built. You're looking for a lawyer who **disagrees specifically** — who looks at your enum and says "no, this value is missing and that one is wrong, here's why." Agreement is cheap; specific disagreement based on practice experience is the entire value of the relationship.

If after three meetings the advisor hasn't disagreed with anything specific, they're either being too polite or they don't have the depth you need. Either way, you don't have the advisor relationship you came for. Be honest with yourself about that signal.
