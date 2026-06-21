# FOUNDER WAIVER — SURGE-00 graduated skip

**Date:** 2026-06-21
**Signed by:** Abdullah (Founder)
**Status:** ACTIVE — overrides default AODC gating for SURGE-01 through SURGE-04 build
**Supersedes:** Default SURGE-00 gating in `planning/SURGE-00-Pre-Build-Validation.md`
**Read by:** AODC_Software_Engineer agent, Claude Code

---

## Statement of waiver

I, the Founder, am explicitly choosing to proceed with SURGE-01 (and subsequent build Surges through SURGE-04) without completing the standard SURGE-00 deliverables, on the following terms:

1. I accept the rework risk of building legal-domain placeholders that may need to change when a legal advisor is secured later.
2. I am NOT waiving the lawyer requirement permanently — I am deferring it. A signed lawyer/advisor remains a hard requirement before paid launch.
3. I will use the working product itself as the recruiting tool for the lawyer co-founder and the validation tool for customer feedback.

---

## What is waived (deferred)

| Item | Waiver scope | Re-engagement trigger |
|---|---|---|
| F-00.1 AI test vs HAQQ | Deferred for SURGE-01/02/03 design. Defaulting to Wedge A (Arabic depth). | Before SURGE-04 detailed Flow design. Run with working product as comparison reference. |
| F-00.2 Lawyer co-founder/advisor | Deferred during SURGE-01–04 build. | HARD STOP before any of: SURGE-04 AI prompts going to real users, SURGE-06 paid launch, ToS/Privacy/DPA going live. |
| F-00.3 Customer discovery interviews | Deferred until working product exists. | Run in parallel with SURGE-03 build (week 4–6). Pricing tier in SURGE-06 cannot be set without this data. |
| F-00.4 PRD v1.0 | Deferred — the Roadmap + Surge plans serve as the working PRD until v1.0 is locked. | Format into PRD v1.0 before SURGE-06 launch. |
| F-00.5 Figma wireframes for SURGE-01/02 | Skipped — Tailwind + Filament defaults are sufficient. | N/A |
| F-00.5 Figma wireframes for SURGE-03 | STILL REQUIRED before SURGE-03 build start. | Block SURGE-03 entry. |
| F-00.5 Figma wireframes for SURGE-04 | STILL REQUIRED before SURGE-04 build start. | Block SURGE-04 entry. |
| F-00.6 Cloudways infra ADR (D-01) | Founder will deploy manually. Decision recorded informally: DigitalOcean Frankfurt FRA1 (per validation/06 default recommendation). | D-01 to be formalized when first deployment happens. |
| F-00.7 Geography confirmation | Locked at Strategic Brief default: Levant-first, Arabic-default locale, USD pricing. | Re-confirm before SURGE-06 pricing tier work. |

---

## What stays BINDING (hard stops the waiver does NOT cover)

These cannot be waived. The Engineer agent should continue to refuse work that violates them:

### Hard stop 1 — Lawyer/Advisor signed before paid launch

NO production deployment accepting paying customers until a lawyer co-founder OR formal advisor is signed and on file at `validation/02_legal_stakeholder_agreement.md`. Pilot firms get a free tier under a signed but-internal ToS; paid customers require lawyer-drafted documents.

### Hard stop 2 — Lawyer sign-off on AI prompt templates before real users see them

`prompts/` directory's templates can be founder-drafted during SURGE-04 build, but EACH template must have a `[LEGAL-REVIEW-PENDING]` header until advisor signs. No prompt template ships to a real lawyer's session until the corresponding header is updated to `[LEGAL-REVIEW-APPROVED: <advisor name> <date>]`.

### Hard stop 3 — Lawyer-drafted legal documents before SURGE-06 launch

Terms of Service, Privacy Policy, Data Processing Agreement, AI Disclaimer — all four must be lawyer-drafted (NOT founder-drafted, NOT AI-drafted) before SURGE-06 launches publicly. Founder-drafted versions are acceptable internally during build for placeholder purposes only.

### Hard stop 4 — Customer interviews before pricing is finalized

SURGE-06 F-06.2 (billing) cannot ship without F-00.3 data (median willingness-to-pay anchor). Founder-guessed pricing is acceptable for the pilot tier (which is free) but not for paid tiers.

### Hard stop 5 — Schema integrity (this never changes)

The Matter schema STILL must not contain court/litigation fields. The codebase STILL must not contain accounting/CRM/email/calendar modules. Off-strategy module rejection is independent of the gate waiver.

---

## Operating instructions under this waiver

### For the Software Engineer agent

When producing Tech Task Packages for SURGE-01, SURGE-02, SURGE-03, SURGE-04:

- **Gate Status Report**: treat F-00.1, F-00.2, F-00.3, F-00.4, F-00.5(S-01/02), F-00.6, F-00.7 as AMBER (waived, not green) for these Surges. F-00.5 for SURGE-03 remains RED until Figma exists.
- **Legal-domain decisions**: produce tasks using the founder-decided defaults below. Mark each affected file/migration/seed with a `// [PROVISIONAL-FOUNDER-DECIDED]` code comment + a one-line note in the TTP "Risks and Open Items" section.
- **Continue to refuse**: SURGE-04 detailed Flow planning beyond F-04.1 spike + F-04.2 LLM abstraction (pending F-00.1), SURGE-06 entirely (pending lawyer + customer interviews).

### Founder-decided defaults (for placeholders during build)

These ship as `[PROVISIONAL-FOUNDER-DECIDED]` and become the advisor's review punch-list:

- **Roles:** Owner / Admin / Member (3 roles, no per-feature overrides)
- **Practice areas** (Matter enum): commercial_contracts, ma, corporate_governance, securities, general_counsel, other
- **Counterparty roles** (matter_counterparties pivot): buyer, seller, licensor, licensee, service_provider, client, other
- **Governing law list:** Jordan, Lebanon, Palestine, Iraq, UAE Federal, DIFC, ADGM, England & Wales, Saudi Arabia
- **Obligation types:** payment, delivery, reporting, notification, consent, other
- **Document types:** contract, memo, letter, amendment, other
- **AI operations:** draft, review, suggest, translate, explain (5 ops as planned)
- **Member permissions (default):** can view all matters in workspace; can edit assigned matters only; cannot delete matters; cannot manage billing
- **AI disclaimer placeholder text:** "AI-generated text. Review before use. Not legal advice. This product does not establish an attorney-client relationship." — founder-drafted, MUST be replaced by lawyer-drafted text before paid launch.

### For the Founder (self-tracking)

Track the punch-list of placeholders as you build. Each `[PROVISIONAL-FOUNDER-DECIDED]` tag in code = one item the advisor reviews when secured. Realistic expectation: 30–50% of these will be confirmed as-is; 50–70% will need revision. That's still cheaper than blocking build for weeks.

---

## Re-engagement schedule (mandatory check-ins)

| Week | Trigger | Action |
|---|---|---|
| Week 2 | SURGE-01 functional | Begin lawyer co-founder outreach in earnest |
| Week 3 | SURGE-02 functional | Begin scheduling F-00.3 customer interviews |
| Week 4 | SURGE-03 underway | Decide on F-00.5 SURGE-03 Figma approach (designer? AI-assisted? Founder-DIY?) |
| Week 5 | Working document workspace | Run F-00.1 AI test against HAQQ using your own working product as comparison baseline |
| Week 6 | SURGE-03 complete | Lawyer advisor must be in active conversation; ≥ 3 customer interviews complete |
| Week 8 | SURGE-04 build | Lawyer advisor must be signed; otherwise SURGE-04 prompt sign-off stalls |

If any week's trigger slides by more than 1 week, this waiver is reviewed.

---

## Sign-off

| Name | Role | Date | Acknowledgment |
|---|---|---|---|
| Abdullah | Founder | 2026-06-21 | I accept the risks above and the hard stops that remain binding. |

---

## Footer

This waiver does not modify the planning documents in `planning/`. They remain the canonical Surge/Flow specifications. This waiver only changes which gates are blocking right now and which are deferred. Update this file (with `Superseded by:` header and a new ADR-style waiver) if the operating mode changes.
