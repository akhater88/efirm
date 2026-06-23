# D-15: Pricing Ceiling — Per-User Monthly Cap

**Status:** Accepted
**Date:** 2026-06-23
**Decision maker:** Founder (with advisor input)
**Supersedes:** None

## Context

Per advisor input (docs/02_advisor_meeting_log.md, Conversation 1, Decision #14):

> "If you want a firm like ours to adopt this without months of partner arguments,
> price it per user. If it costs more than 20 to 30 JOD ($30-$40 USD) per lawyer
> per month, the firm owners will overthink it and say 'let's just stick to Excel.'
> Keep it under that psychological barrier."

Khaldoun Khater (12 years at Al-Dujani Office, Amman) represents our primary target persona: managing partner of a 2-10 lawyer commercial/corporate firm in Jordan. His pricing feedback reflects direct experience with software procurement decisions at this firm size.

## Decision

1. **Pricing model:** Per-user, per-month subscription (via Stripe/Laravel Cashier per D-06).
2. **Price ceiling:** JOD 20-30 / USD 28-42 per user per month at launch. Never exceed JOD 30 (~USD 42) for the base tier.
3. **Tier structure (provisional):**
   - **Starter:** Up to 3 users. Target: JOD 20/user/month.
   - **Professional:** Up to 10 users. Target: JOD 25/user/month. Includes AI features.
   - **Custom/Enterprise:** 10+ users. Custom pricing (still under JOD 30/user/month for base features).
4. **Free trial:** 14 days, no credit card required. Essential for overcoming risk aversion (Decision #15 — onboarding simplicity).

## Consequences

- Stripe product/price configuration in SURGE-06 (F-06.1) must respect these ceilings.
- AI usage (LLM API costs) must be budgeted within the per-user margin. If Claude API costs exceed ~$5/user/month, implement usage caps or tiered AI access.
- Feature gating between tiers should be minimal at MVP — focus on user count, not feature paywalls. Lawyers hate discovering features they cannot use.
- Annual discount (15-20%) can bring effective monthly below JOD 20 for annual commitments.

## Alternatives considered

- **Flat-rate per-workspace:** Rejected. Penalizes small firms (2-3 lawyers paying same as 10).
- **Per-matter pricing:** Rejected. Creates anxiety about cost-per-case, discourages adoption.
- **Freemium:** Rejected for MVP. Too early to know which features drive conversion.
