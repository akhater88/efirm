# D-06: Billing Provider

**Status:** DECIDED
**Date:** 2026-06-21
**Decision-maker:** Founder (under SURGE-00 waiver)

## Context

The MVP needs a billing and subscription system for workspace-level, per-seat pricing. Options evaluated: Stripe, Paddle, manual invoicing.

## Decision

**Stripe** via **Laravel Cashier** is the billing provider.

## Details

- **Pricing model:** Per-seat (per workspace member), monthly billing.
- **Trial period:** 14 days, no credit card required.
- **Currency:** USD only at MVP. Multi-currency is a Year-2 backlog item.
- **Pilot tier:** 6 months free for early-adopter firms (managed via Stripe coupon, not custom code).
- **Integration:** Laravel Cashier handles subscription lifecycle, webhooks, payment method management, and invoice generation.
- **Webhook events:** `customer.subscription.updated`, `customer.subscription.deleted`, `invoice.payment_failed` — update `workspaces.billing_status` accordingly.

## Alternatives Considered

- **Paddle:** Simpler tax handling (merchant of record), but weaker Laravel ecosystem support and limited MENA region coverage.
- **Manual invoicing:** Zero integration cost, but does not scale and blocks self-service onboarding.

## Consequences

- Stripe API keys stored in `.env` only (never committed).
- `workspaces.billing_status` enum: `trial`, `active`, `past_due`, `suspended`, `cancelled`.
- Billing UI is a Filament page within the workspace panel (SURGE-06 scope).
- No custom invoice/receipt tables — Cashier's built-in invoice support is sufficient for MVP.
