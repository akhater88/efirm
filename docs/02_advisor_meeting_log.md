# Advisor Meeting Log — Khaldoun Khater

**Advisor:** Khaldoun Khater
**Firm:** Al-Dujani Office, Amman (commercial / corporate / major civil litigation)
**Relationship:** Founder's cousin; informal advisor (no formal agreement; cousin favour-economy)
**Practice focus:** Commercial law, corporate transactions, major civil litigation (no family law / Sharia)
**Experience:** 12 years at Al-Dujani
**Coverage:** Jordan (primary), GCC commercial practice (secondary)
**Commitment:** ~3–5 hours/month equivalent through ongoing chat-style consultation; permission to ask "any time, however much I need"
**Compensation:** None monetary; reciprocal favour-economy (founder built websites for the firm previously); cultural appreciation gifts only

**Important boundary:** Khaldoun cannot formally sign external compliance attestation documents on Al-Dujani letterhead. Formal signoff items in `validation/06_legal_docs/`, `validation/08_litigation_lawyer_signoff.md`, `validation/09_trust_account_lawyer_signoff.md`, and the `[LEGAL-REVIEW-APPROVED]` headers on AI prompt templates require a separately-engaged paid lawyer. Khaldoun has offered to introduce a tech-focused corporate lawyer in Amman for the SaaS document drafting (ToS, Privacy, DPA, AI Disclaimer).

---

## Conversation 1 — 2026-06-22

**Format:** WhatsApp message exchange, then 90-minute call
**Trigger:** Founder sent SURGE-08, SURGE-09 plans for review
**Outcome:** 12 substantive corrections received; logged below

### Khaldoun's input (verbatim)

> Habibi! Good to see you. I'm glad you reached out. When you told me you were building a legal-tech startup focused on the Levant, I was actually pretty excited for you. You know I've been holding things down at Al-Dujani office for 12 years now, and trust me, our industry is practically begging for a modernization shake-up.
>
> Look, because I work at Al-Dujani, my focus is strictly corporate, commercial, and major civil litigation. I don't touch family law or Sharia court cases at all—the office has a completely separate setup for that. So, looking at your roadmap from my 12 years of handling commercial disputes and corporate clients here and across the GCC, let me give you the real, unvarnished truth on your questions. Skip the textbook AI answers; this is how it actually works on the ground.
>
> **Bucket 2: How We Actually Use Tech (and My Pain Points)**
>
> If you sat me down and showed me your wireframes, my first question as your cousin wouldn't be "Is it cool?" It would be: "Khaled, is this going to save my team time, or is it just another chore I have to force my tired junior associates to data-entry at 8:00 PM?"
>
> At Al-Dujani, we use basic tools like App4Legal and shared cloud folders to keep track of files. But the real, bleeding pain points that software hasn't fixed for us yet are:
>
> - **The Court Tracking Anxiety:** My absolute biggest daily stress is missing a court deadline. Jordanian court schedules shift constantly. If an associate forgets to log a hearing outcome or a date, it can ruin a multi-million dinar commercial case.
> - **The Bilingual Translation Drain:** We draft everything in Arabic because the local courts demand it, but our corporate clients want updates and contracts in English. Constantly translating these files manually is a massive time-waster.
>
> If you're asking me to be an advisor, of course I'd love to help you out—you're family, and I want to see a Levant startup succeed. Just keep in mind that with my caseload, I can probably only give you about 3 to 5 hours a month. For compensation, a small slice of early advisor equity to give me some skin in the game, alongside naming me or the firm as a tech-forward partner, is the perfect setup.
>
> **Bucket 3: The Deep Product Logic (The "Load-Bearing" Stuff)**
>
> **1. Litigation Modeling (SURGE-08)**
>
> Your status and hearing enums are a decent start, but they don't quite match how a commercial case actually breathes in Jordan.
>
> The Real Lifecycle of a Dispute: A corporate client calls me → We draft the statement of claim (لائحة الدعوى) → We pay court fees (you need a specific state for "Fee Payment & Registration" / قيد الدعوى ودفع الرسوم) → Notifications are served (تبليغ) → Preliminary sessions → The Expert Phase (where cases literally go to sleep for 6 months) → Final pleadings → Judgment.
>
> Fix your Status Enums: You absolutely must add a state called "Referred to Expert" (الإحالة للخبرة). In Jordanian commercial litigation, this isn't just a minor step; it's an entirely distinct phase where the court pauses and an independent expert takes the file to calculate damages or audit accounts.
>
> Fix your Hearing Enums: We don't just have one generic "evidence" hearing. You need to split it into "Plaintiff's Evidence" (بينات المدعي) and "Defendant's Evidence" (بينات المدعى عليه). Also, add a "Notification Session" (جلسة تبليغ) because judges routinely push hearings back just because a party wasn't properly served.
>
> The Appeal Window: For standard commercial first-instance decisions in Jordan, the strict window to file an appeal is 30 days from the day after the judgment is issued (or from formal notification if it was a default judgment). Miss it by an hour, and your client is finished.
>
> Modeling the Expert Report (تقرير الخبير): Build a dedicated data object for this right now. When the court expert drops their report, both sides have a legal, strict 8-day window to object to it (الاعتراض على تقرير الخبير). Your software needs an automatic countdown timer for this 8-day deadline the second a user uploads an expert report.
>
> **2. Trust Accounts & Money (SURGE-09)**
>
> How it works at Al-Dujani: The managing partners hold absolute, sole authority over the firm's main bank accounts. We keep client retainer deposits strictly separated from our operational cash (like money used for rent, salaries, or office expenses).
>
> The Legal Danger: Under the Jordanian Lawyers Act (قانون نقابة المحامين), client money is sacred. Commingling client funds with the firm's operating cash is a massive ethical violation.
>
> Ledger Corrections: Your append-only ledger model is actually perfect. Bar rules and standard legal audits completely prohibit simply "deleting" or editing an accounting entry. If a mistake happens, we are required to issue an offsetting entry (قيد تسوية / عكسي) with a mandatory description text explaining why the change was made.
>
> **3. Compliance, SaaS, & AI (SURGE-06, 04, 10)**
>
> SaaS Terms Gotchas: If you're drafting B2B SaaS terms for Jordan, the biggest hurdle is Jurisdiction. Local companies hate seeing terms that say disputes are settled in Dubai or London. They will fight you on it; it needs to specify Amman Courts (Palace of Justice / قصر العدل).
>
> Data Privacy: Don't forget that Jordan's Personal Data Protection Law (No. 24 of 2023) is fully active now. It's very similar to GDPR. If your startup is hosting data in Frankfurt, you are doing a cross-border data transfer. Under the law, you must have the explicit prior consent of the data subject (the client) to store their sensitive data outside Jordan.
>
> The AI "Red Line": Only registered members of our Bar Association can practice law. If a business owner uses your software to generate a contract for themselves, that's fine. But, if a non-lawyer uses your AI tool to draft a contract for a third party and charges them for it, that crosses the line into unauthorized practice of law.
>
> The AI Disclaimer: Your disclaimer ("AI-generated. Not legal advice.") protects your startup, but it won't magically protect a lawyer from malpractice if they leave an AI error in a contract and their corporate client suffers. The disclaimer should read clearly in both languages: "This is an internal drafting aid for qualified legal professionals. It does not replace independent legal analysis."
>
> **4. KYC & Sanctions (SURGE-07, 13)**
>
> Is Screening Mandatory? Yes, 100%. Under Jordan's Anti-Money Laundering laws, law firms are considered "Designated Non-Financial Businesses and Professions" (DNFBPs). We are legally required to run KYC and screen for PEPs (Politically Exposed Persons) and sanctions.
>
> The Checklist: Your list (National ID/Passport, Address, Beneficial Owner) is fine for individuals. For the corporate clients I deal with, you must add the Company Registration Certificate (شهادة تسجيل الشركة) from the Ministry of Industry and Trade, and the Signatory Authority document (شهادة مفوضين بالتوقيع) to prove the person signing actually has the legal right to bind that company.
>
> **Buckets 4 & 5: Rolling This Out to Real Lawyers**
>
> How I start a contract: Look, I never start from a blank page. I pull up a template or a similar contract from a big case we won a couple of years ago and modify it. Where your AI would actually change my life is if it could scan my old 20-page Arabic contract and instantly highlight the three clauses that conflict with a new law passed last year.
>
> The Pricing Sweet Spot: If you want a firm like ours to adopt this without months of partner arguments, price it per user. If it costs more than 20 to 30 JOD ($30–$40 USD) per lawyer per month, the firm owners will overthink it and say "let's just stick to Excel." Keep it under that psychological barrier.
>
> Your Biggest Blind Spot: Your biggest blind spot right now is assuming lawyers love new tech. We don't; we are incredibly risk-averse and terrified of breaking our workflow mid-lawsuit. If your product takes more than one afternoon for my junior associates to learn, they will abandon it and go right back to WhatsApp groups and Microsoft Word. Make the onboarding dead simple, or the product will collect digital dust.

### Decisions made from Conversation 1

| # | Topic | Decision | Translates to |
|---|---|---|---|
| 1 | `litigation_status` enum | Add "Fee Payment & Registration" (قيد الدعوى ودفع الرسوم), "Notification" (تبليغ), "Referred to Expert" (الإحالة للخبرة) | Migration: extend enum |
| 2 | `hearing_type` enum | Split "evidence" into "plaintiff_evidence" (بينات المدعي) and "defendant_evidence" (بينات المدعى عليه); add "notification_session" (جلسة تبليغ) | Migration: extend enum |
| 3 | Expert Report entity | Build dedicated entity with mandatory 8-day countdown timer on upload | New table + service + Obligation auto-creation |
| 4 | Appeal window (initial — SUPERSEDED by Conversation 2) | 30 days from judgment, OR from formal notification for default | (Superseded — see Conversation 2 decision) |
| 5 | Trust account management authority | Managing partners hold sole authority | Policy update — only Owner role can manage main bank account |
| 6 | Trust account append-only model | Confirmed correct as designed | No change — validated |
| 7 | Trust account corrections | Offsetting entries with mandatory description (قيد تسوية / عكسي) | Schema: `description` field on trust_ledger_entries becomes required (NOT NULL) for entry_type='adjustment' |
| 8 | Jordan PDPL Law 24/2023 | Cross-border data transfer to Frankfurt requires explicit prior consent | Onboarding flow: consent checkbox; Privacy Policy text |
| 9 | SaaS ToS jurisdiction | Must specify Amman Courts (قصر العدل) | Legal-doc drafting input |
| 10 | AI Disclaimer language | "This is an internal drafting aid for qualified legal professionals. It does not replace independent legal analysis." | All AI-output footers; localized AR + EN |
| 11 | Unauthorized practice of law | Non-lawyer for self = OK; non-lawyer for third party for fee = UPL | ToS clause; product behavior unchanged |
| 12 | Corporate KYC items | Add "Company Registration Certificate" (شهادة تسجيل الشركة) and "Signatory Authority document" (شهادة مفوضين بالتوقيع) | KycItem enum extension for corporate Contacts |
| 13 | AML/PEP screening | Mandatory under DNFBP rules — not optional | Feature priority elevation (line items exist in S-07 F-07.3; actual external screening API integration deferred to Year-2 with explicit disclaimer that screening is currently manual checklist) |
| 14 | Pricing | JOD 20–30 ($30–40 USD) per user/month resistance ceiling | Pricing decision ADR (D-15) |
| 15 | Biggest blind spot | Lawyers do NOT love new tech; onboarding must be < one afternoon | UX priority across all Surges |
| 16 | Workflow pain points | Court tracking anxiety; bilingual translation drain | Feature prioritization signal |
| 17 | Competitor | App4Legal (local Jordan competitor) | Competitive intel — update market research |

---

## Conversation 2 — 2026-06-23

**Format:** Asynchronous message exchange (founder summary + advisor reply)
**Trigger:** Founder sent thank-you summary capturing 12 decisions from Conversation 1
**Outcome:** One critical correction to the appeal-window logic; three forward-moving commitments

### Khaldoun's input (verbatim)

> Hala Abdullah! You're welcome, habibi. I'm really glad our chat on Monday was that useful to you. Seeing how fast you mapped this out makes me proud—you've got the tech side handled, you just needed the ground reality of the Palace of Justice (قصر العدل).
>
> Your summary is almost 100% spot on, but as your lawyer cousin, I caught one critical mistake in your appeal window logic that will cause massive issues if you code it this way. Let's fix it right now before you write any lines of code:
>
> **⚠️ The Appeal Window Correction (Point #4)**
>
> You wrote: "30 days from judgment date OR from formal notification in the case of default judgments."
>
> That is only half true and a bit risky for commercial practice in Jordan. Under the Civil Procedure Law (قانون أصول المحاكمات المدنية), the timeline actually depends on the type of court:
>
> - **First Instance Courts (محاكم البداية):** This is where our major commercial disputes live. The appeal window here is indeed 30 days. It starts the day after the judgment if it was given in our presence (وجاهي), or the day after we are formally notified if it was default (بمثابة الوجاهي).
> - **Magistrate Courts (محاكم الصلح):** If a commercial case is smaller (under 10,000 JOD), it goes here. The appeal window for Magistrate judgments is only 10 days!
>
> What you need to build: Your system cannot just have a hardcoded "30 days" loop. Your case database entity must track the Court Type. If Court Type = البداية, trigger a 30-day countdown. If Court Type = الصلح, trigger a 10-day countdown. Missing a 10-day window because the software assumed 30 days would be a total catastrophe for a law firm.
>
> Everything else looks perfectly aligned with our conversation:
>
> - **The Expert 8-day countdown (Point #3):** Perfect. Make sure it calculates from the day after formal receipt of the report.
> - **The "Notification Session" (Point #2):** Crucial. Half our time is spent tracking if the opposing side actually got served.
> - **PDPL and Frankfurt Hosting (Point #6):** Exactly. Explicit consent at intake keeps your clients strictly compliant with the 2023 privacy law.
>
> **Moving Forward & Your Future Points:**
>
> 1. **The ToS/Privacy Drafting:** I really appreciate you thinking about my position with the partners at Al-Dujani. You're right, doing it officially through me could cause an issue since it's outside our usual corporate billing structure. I have a fantastic colleague, an independent tech-focused corporate lawyer in Amman, who can draft these for you for a very fair startup fee. I'll introduce you.
> 2. **The Seeder Data:** Don't stress about finding a court reference list from scratch. I have a clean spreadsheet tracking the structure of the Jordanian court hierarchy (Amman, Irbid, Zarqa, etc.) and their specific tiers. I'll clean it up and send it to you as a CSV.
>
> Fix that database logic for the 10 vs 30-day appeal windows, apply the rest of your list, and let's look at the live software on your laptop.

### Decisions made from Conversation 2

| # | Topic | Decision | Translates to |
|---|---|---|---|
| 18 | Appeal window logic (SUPERSEDES #4) | Court-level-dependent: 10 days for محاكم الصلح / 30 days for محاكم البداية. Start day after decision_date for in-presence (وجاهي); start day after notified_date for deemed-in-presence (بمثابة الوجاهي) | New AppealDeadlineService; matter.court_level field; court_review.judgment_presence + notified_date fields |
| 19 | Expert report countdown start | Day after formal receipt of report | Service logic detail |
| 20 | Notification session importance | Confirmed critical (half of litigation time tracks service) | No new feature — emphasis confirmed |
| 21 | PDPL implementation | Confirmed approach correct | No change — validated |
| 22 | Pending — pure default judgments (غيابي) | OPEN QUESTION — does pure default (defendant never properly served) exist as separate category from بمثابة الوجاهي with different appeal window? Founder asked in reply; awaiting Khaldoun's answer | Conservatively include judgment_presence='ghyabi' enum value; service throws explicit "unconfirmed regulation" exception until advisor confirms; alert raised |
| 23 | Paid lawyer for SaaS docs | Khaldoun to introduce tech-focused corporate lawyer for paid drafting of ToS/Privacy/DPA/AI Disclaimer | Pending introduction — track in `validation/06_legal_docs/introduction_pending.md` |
| 24 | Jordan courts CSV | Khaldoun to send curated CSV of Jordanian court hierarchy | Replace `JordanCourtsSeeder` hardcoded data with CSV-driven seeder reading from `database/seeders/data/jordan_courts.csv` |
| 25 | Product walkthrough | Khaldoun requested live walkthrough on founder's laptop after corrections applied | Schedule within 7–10 days after SURGE-FIX-01 ships |

---

## Open items awaiting Khaldoun's input

- Confirmation on judgment_presence='ghyabi' (pure default) — does it differ in appeal window from 'mithla_wijahi'? (Asked in reply to Conversation 2; awaiting response)
- Introduction to tech-focused corporate lawyer for paid drafting work
- Receipt of Jordan courts CSV
- Date for live product walkthrough session

## Open items NOT covered yet by Khaldoun

These remain `[PROVISIONAL-FOUNDER-DECIDED]` until raised in future conversations:

- Appeal court (محكمة الاستئناف) and Cassation court (محكمة التمييز) procedural specifics
- Specialized commercial courts (مَحاكم تجارية مخصصة) — do they exist with separate procedures?
- Sharia, Administrative, Labor court specifics (likely out of scope — confirm)
- Conflict-of-interest checking specifics
- Detailed engagement letter requirements
- Court fee calculation rules
- Hearing reminder cadence preferences (currently 24h before; is that optimal?)
- Lawyer-departure handoff process
- Pro hac vice / cross-jurisdiction practice

---

## How this file is used

This is the source-of-truth document for advisor input. Every schema correction in `planning/SURGE-FIX-01-Advisor-Corrections.md` cites a row from this log. Every code comment that explains a "why we did it this way" decision references a row from this log by date and decision number. Update this file after every substantive conversation with Khaldoun; never delete entries; supersede via new dated entries.
