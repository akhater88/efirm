# SURGE-LP-01 — eFirm Public Landing Page

**Wave-Ready Package** | **Version:** 1.0 | **Status:** Ready for Engineer Handoff
**Product Designer:** Abdullah Mohammed (Founder)
**Date Assembled:** 2026-06-24
**Methodology:** AODC (Agent-Orchestrated Development Cycle)

**Stack:** Laravel 13.16 · Filament v5.6 · PHP 8.3 · MySQL 8 · Redis · Cloudways/DigitalOcean Frankfurt FRA1 · Tailwind CSS · Blade
**Estimated effort:** 14–18 hours of Wave work across 6 Waves; 2–3 days with Claude Code execution
**Storage location (recommended):** `docs/surges/SURGE-LP-01-landing-page.md`

---

## How to consume this file

This is a **Wave-Ready Package (WRP)** produced by the AODC Product Designer agent. Two downstream consumers:

1. **AODC Software Engineer agent** — Reads this WRP and produces a **Tech Task Package (TTP)** with migration commands, model definitions, FormRequest classes, test inventories, and OpenAPI diffs. Then hands the TTP to Claude Code.
2. **Claude Code (direct path)** — For well-bounded Surges (like this one, a server-rendered Blade landing page), the AO may paste a runbook directly into Claude Code without producing an intermediate TTP. The companion file `SURGE-LP-01-claude-code-runbook.md` is the prepared runbook.

Read sections 1–7 in order. Section 8 is the Sign-Off and handoff metadata.

---

## Table of Contents

1. [Intent Definition](#1-intent-definition)
2. [User Stories](#2-user-stories)
3. [Wireframes Documentation](#3-wireframes-documentation)
4. [API Contracts](#4-api-contracts)
5. [Content Specification](#5-content-specification)
6. [Edge Cases & Error Handling](#6-edge-cases--error-handling)
7. [Sign-Off Log](#7-sign-off-log)
8. [Engineer Handoff Appendix](#8-engineer-handoff-appendix)

---

# 1. Intent Definition

## Problem

eFirm has no public-facing presence today. The product exists as a Filament application accessible only to authenticated users at the same Laravel monolith. Prospective customers (small Levant law firms, 2–10 lawyers), inbound advisor referrals (lawyers introduced by Khaldoun Khater and his network), investors evaluating the company, and journalists assessing the market position have no way to discover eFirm, evaluate its capabilities, compare its pricing, or initiate a trial without direct contact with the founder.

Concretely, the absence of a public landing page creates five enumerable pains:

1. **Inbound advisor referrals stall** — a lawyer told about eFirm cannot independently evaluate it. They either contact the founder directly (founder-time-bottlenecked) or drop off.
2. **Self-serve trial signup is impossible** — even though the technical capability exists (Stripe environment provisioned, trial-state lifecycle implemented), there is no consumer-facing surface to initiate registration.
3. **Competitive positioning is invisible** — the differentiators developed across 14 volumes of HAQQ.ai teardown work (Arabic-native legal intelligence, integrated single-surface architecture, Jordanian procedural accuracy, PDPL Article 13 compliance, all-inclusive pricing) cannot be communicated to prospects.
4. **The Monday café walkthrough with Khaldoun** near the Palace of Justice and any subsequent practitioner demonstration requires a URL the lawyer can revisit, share with partners, and use to ground the conversation. No such URL exists.
5. **SEO presence is zero** — searches for "legal practice management Jordan", "law firm software Amman", "ذكاء اصطناعي قانوني", or any related Arabic or English query return no eFirm result, while competitor pages rank.

## Target User

**Primary:** Managing partner or senior associate at a Levant law firm of 2–10 lawyers (predominantly Jordan-based for the launch window; secondarily Lebanon, Palestine, and Iraq), evaluating practice-management software for their firm. Reads English fluently for SaaS evaluation but operates in Arabic professionally. Price-sensitive (JOD 20–30/user/month is the established willingness-to-pay ceiling per Khaldoun's advisor input). Comfortable with web SaaS but distrustful of foreign software that does not understand Jordanian court procedure or PDPL requirements.

**Secondary:** Solo practitioner or 1–2 person firm in Amman (the long tail of the Jordanian bar) who wants a simple, professional, affordable workspace without a sales conversation.

**Tertiary:** Advisor-referred lawyer (Khaldoun's network) who has been told verbally about eFirm and needs a self-serve evaluation path.

**Quaternary:** Investor, journalist, or strategic partner conducting independent due diligence on eFirm without founder involvement.

## Outcome

When this Surge ships:

- A public website at `efirm.io/` serves a single-scroll landing page with 13 sections covering hero, problem framing, single-workspace claim, six feature pillars, Arabic AI demonstration, Jordanian procedural accuracy, pricing, security, FAQ, and final call-to-action.
- English is the default language with full Arabic RTL toggle; both languages render correctly with appropriate fonts (Inter for English, IBM Plex Sans Arabic for Arabic) and directionality.
- Visitors can initiate a 14-day self-serve trial via the primary CTA on every section, routing to the existing `/register` flow.
- Visitors with 5+ seat requirements can request a demo via a qualifying form (name, firm, seat count, email, phone, locale), which generates an email notification to `abdullah@efirm.io` and an auto-created Linear ticket in the "Leads" project.
- Full pricing is publicly disclosed: Starter $20, Pro $25, Enterprise $30 per seat per month, with a feature matrix and a "First 50 founding firms: 30% off Pro for the first year" promotional badge on the Pro tier.
- Pages are crawlable by search engines with complete meta tags, OpenGraph, JSON-LD `LegalService` structured data, and an XML sitemap. Target keywords span practice-management, AI, and PDPL/procedural-compliance clusters in both languages.
- A PDPL Article 13-compliant cookie consent banner with granular toggles (Essential / Analytics / Marketing) is presented on first visit, with consent state persisted and re-presentable from the footer.
- Legal footer links (Terms of Service, Privacy Policy, Data Processing Addendum, AI Disclaimer) point to stub pages populated with generic SaaS template content, with a visible "Last updated" date and a footer disclaimer that finalized documents are pending — flagged as a known legal risk to be remediated post-launch via Khaldoun's tech-lawyer referral.
- The page deploys to the same Laravel 13.16 monolith on Cloudways / DigitalOcean Frankfurt FRA1, with a public route group separate from the Filament admin panel at `/app`.

## Success Metrics

Quantitative metrics to be measured 30 days after launch:

| ID | Metric | Target | Measurement |
|---|---|---|---|
| **M-1** | Unique visitor sessions per week | ≥ 50 by week 4 | Google Analytics 4 with consent-aware tracking |
| **M-2** | Trial signups initiated from landing | ≥ 5 in first 30 days | UTM parameters on internal CTAs + direct referrer = `efirm.io/` |
| **M-3** | Demo requests submitted | ≥ 3 in first 30 days | Email + Linear ticket within 60s of submission |
| **M-4** | Largest Contentful Paint (LCP) | < 2.5s on 4G mobile | PageSpeed Insights |
| **M-5** | Cumulative Layout Shift (CLS) | < 0.10 | PageSpeed Insights, desktop + mobile |
| **M-6** | Lighthouse SEO score | ≥ 95 | Lighthouse audit on every deploy |
| **M-7** | Cookie consent banner first-visit appearance | 100% | Manual audit + Pest browser test |
| **M-8** | RTL layout zero regressions on 5 reference devices | iPhone 12 Safari, Galaxy S22 Chrome, iPad 10 Safari, MacBook Pro Chrome 1440px, Windows Firefox 1920px | Manual QA |
| **M-9** | Zero JS console errors, zero broken links | Zero | Weekly automated link check |
| **M-10** | Advisor qualitative feedback | ≥ 1 positive ("strong enough to show a partner") | `docs/validation/02_advisor_meeting_log.md` |

## Business Value

The landing page is the gating asset for three downstream business outcomes that cannot proceed without it:

1. **Pilot firm acquisition.** The Monday café walkthrough with Khaldoun, and every subsequent practitioner demonstration in his network, requires a URL to anchor the conversation and a self-serve path that survives after the meeting ends. Without it, every prospect requires personal founder time, capping pilot acquisition at the founder-throughput ceiling.

2. **Competitive defensibility on the Arabic / Levant wedge.** Per Teardown Vol. 14, eFirm's defensible positioning rests on two claims: (a) the integrated single-surface architecture versus competitor two-app splits, and (b) Arabic-native legal intelligence quality. Neither claim can be substantiated to a prospective buyer without a public surface that demonstrates them. The landing page is where the wedge becomes legible to the market.

3. **Pricing economics validation.** The $20 / $25 / $30 per-seat USD pricing is materially below the competitor's AI-alone Starter tier of $25 per seat per month (which excludes practice management). Making this pricing publicly comparable is the single strongest economic argument in the funnel, but only if it is visible.

Secondary business value: founder credibility for fundraising conversations, press coverage in regional legal-tech journalism, and SEO presence that compounds over time as eFirm acquires organic traffic for Arabic legal software queries that no competitor currently ranks for.

---

# 2. User Stories

## US-001: Hero section renders with primary CTA above the fold

As a prospective Levant law firm partner visiting `efirm.io` for the first time, I want to immediately see the value proposition, a product screenshot, and a clear primary action so that I can decide within 8 seconds whether eFirm is worth my evaluation time.

**Acceptance Criteria:**
- GIVEN a desktop visitor at viewport ≥ 1024px width WHEN they load `efirm.io/` THEN the hero section is fully visible above the fold, containing: headline (max 80 chars), sub-headline (max 160 chars), primary CTA button ("Start Free Trial"), secondary CTA button ("Book a Demo"), and one product screenshot frame.
- GIVEN a mobile visitor at viewport ≤ 640px width WHEN they load `efirm.io/` THEN the hero displays the headline, sub-headline, primary CTA, and a stacked secondary CTA above the fold; the product screenshot is positioned immediately below the fold.
- GIVEN a visitor on the hero section WHEN they click the primary CTA THEN they are routed to `/register` with UTM parameter `utm_source=landing_hero` attached to the URL.
- GIVEN a visitor on the hero section WHEN they click the secondary CTA THEN they are routed to `/demo-request` with UTM parameter `utm_source=landing_hero_demo` attached.
- GIVEN the page has loaded WHEN measured via PageSpeed Insights on a simulated 4G connection THEN the Largest Contentful Paint (LCP) for the hero section completes in under 2.5 seconds.

**Edge Cases:**
- **Error:** If the hero product screenshot fails to load (404 or network failure), display a CSS-styled placeholder block matching the screenshot's exact dimensions (1200x800px aspect ratio preserved) with background colour `bg-slate-100` and a centred SVG icon of an abstract document. No broken-image icon may render.
- **Empty:** Not applicable — hero is content-static.
- **Loading:** Hero text renders immediately from server-rendered HTML. Product screenshot uses native lazy-loading attribute `loading="lazy"` with a low-quality image placeholder (LQIP) blur-up effect using `width: 100%; aspect-ratio: 3/2` to prevent layout shift.
- **Offline:** If the visitor goes offline after initial page load, the page remains fully readable since all assets are server-rendered or already cached. No interactive elements depend on network.
- **Boundary:** Headline character limit enforced in localization file at 80 characters; sub-headline limit at 160 characters; CI test in `tests/Feature/Marketing/LandingCopyLimitsTest.php` asserts both.
- **RTL:** When locale is `ar`, hero layout flips: CTA order reverses (primary CTA on left, secondary on right per RTL reading order), text aligns right, product screenshot frame appears on the left half of the layout instead of the right. Font switches to IBM Plex Sans Arabic. Headline and sub-headline render with `dir="rtl"` attribute.

## US-002: Language toggle switches between English and Arabic with URL change

As a bilingual lawyer or an Arabic-first visitor, I want to switch the landing page language between English and Arabic with a single click and have my choice persist across sessions so that I can read the page in the language I am most comfortable with.

**Acceptance Criteria:**
- GIVEN a visitor on `efirm.io/` in default English locale WHEN they click the "العربية" link in the header THEN the URL changes to `efirm.io/ar` and the page re-renders in Arabic RTL within 1 second.
- GIVEN a visitor on `efirm.io/ar` WHEN they click the "English" link in the header THEN the URL changes to `efirm.io/` (with locale set to `en`) and the page re-renders in English LTR within 1 second.
- GIVEN a visitor has previously selected Arabic WHEN they return to `efirm.io/` on a subsequent visit THEN the application checks the `efirm_locale` cookie, redirects to `efirm.io/ar` via HTTP 302, and preserves their choice for 365 days.
- GIVEN a visitor has no `efirm_locale` cookie WHEN they visit `efirm.io/` THEN the locale detection middleware reads the `Accept-Language` HTTP header; if the primary language tag is `ar`, `ar-JO`, `ar-LB`, `ar-PS`, or `ar-IQ`, the visitor is redirected to `/ar`; otherwise the page renders in English at `/`.
- GIVEN the page has rendered in Arabic WHEN inspected THEN every user-facing string originates from `resources/lang/ar/marketing.php`; no English strings appear in the document body, with the explicit exception of brand names (eFirm), URLs, and email addresses.
- GIVEN the page has rendered in Arabic WHEN inspected THEN the `html` element carries `lang="ar"` and `dir="rtl"` attributes, and Tailwind RTL utility classes apply correctly to all sections.

**Edge Cases:**
- **Error:** If a translation key is missing from `resources/lang/ar/marketing.php`, the Blade helper `__()` falls back to the English key, and a Laravel log warning is written at level `warning` with channel `marketing-i18n`. The page never renders a raw bracketed key like `[marketing.hero.headline]`.
- **Empty:** Not applicable.
- **Loading:** Language switch is server-rendered via redirect; there is no interim loading state. The browser performs a standard navigation.
- **Offline:** If the visitor is offline, the language toggle anchor still appears in the DOM but clicking it produces a browser-native offline page. No graceful in-app offline message is required for v1.
- **Boundary:** Cookie `efirm_locale` accepts only two values, `en` and `ar`; any other value is treated as unset and triggers re-detection on the next request. Cookie is `HttpOnly=false` (must be readable for in-page toggle), `SameSite=Lax`, `Secure=true` in production, expires 365 days.
- **RTL:** All page sections must respect `dir="rtl"` when locale=ar, including: nav order (logo right, links left, CTA leftmost), card grids (first card on the right), form inputs (text-align right, placeholder right-aligned), icons that imply direction (arrows, carets) must mirror, and decorative illustrations must not invert text within them.

## US-003: Pricing table displays three tiers with feature matrix and founding-firm badge

As a price-sensitive Levant law firm partner, I want to see eFirm's complete pricing publicly without contacting sales so that I can self-qualify and compare tiers before committing time to a trial.

**Acceptance Criteria:**
- GIVEN a visitor reaches the pricing section WHEN the section renders THEN three pricing cards display in this exact order: Starter ($20/seat/month), Pro ($25/seat/month, marked "Most Popular"), Enterprise ($30/seat/month).
- GIVEN the Pro tier card WHEN it renders THEN a yellow-amber badge reading "First 50 founding firms: 30% off Pro for year 1" appears at the top of the card, and a tooltip explains the offer in detail on hover (desktop) or tap (mobile).
- GIVEN any pricing card WHEN the visitor clicks the tier's primary CTA ("Start Free Trial") THEN they are routed to `/register` with query parameters `utm_source=pricing_table` and `plan={tier_slug}` where `tier_slug` is `starter`, `pro`, or `enterprise`.
- GIVEN any pricing card WHEN the visitor scrolls past it THEN a feature comparison matrix appears below the three cards (8 rows: Seats, Matters, Storage, AI requests/month, Audit log retention, Trust ledger, PDPL compliance, Frankfurt residency).
- GIVEN the pricing section WHEN viewed in Arabic locale THEN all numeric prices remain in Western Arabic numerals (20, 25, 30) and the currency symbol `$` remains, but a small JOD-equivalent line appears below each price reading "ما يعادل JOD ~14 / JOD ~18 / JOD ~21" (with rates fixed at the value present in `config/marketing.php` as of the deployment date).

**Edge Cases:**
- **Error:** If the founding-firm badge data (counter, end date) is controlled via a config flag in `config/marketing.php` and the flag is set to disabled, the badge does not render but the rest of the pricing card displays normally.
- **Empty:** Not applicable — pricing data is static configuration.
- **Loading:** All pricing content is server-rendered with no async data dependency.
- **Offline:** Static content remains readable; CTA buttons require network for the `/register` navigation, which fails with the browser's native offline page.
- **Boundary:** The "First 50" counter is informational only on the landing page — it does not reflect a live count of registrations. Actual founding-firm enforcement happens in the registration flow (out of scope for this Surge). A static config value `config('marketing.founding_firm_badge_enabled')` controls visibility.
- **RTL:** Pricing card order reverses (Enterprise on the left, Starter on the right). The "Most Popular" tag on the Pro tier remains visually elevated (slight scale transform) and centred. Feature matrix table flips so the first column (feature name) appears on the right and tier columns appear in reversed order.

## US-004: Demo request form captures qualifying lead data and routes to notification + Linear ticket

As a managing partner of a firm with 5+ lawyers, I want to request a personalised demo via a short form so that I can have a conversation with the founder before committing to a trial.

**Acceptance Criteria:**
- GIVEN a visitor clicks any "Book a Demo" CTA WHEN the click registers THEN they are routed to `/demo-request` which renders a single-page form containing: Full Name (text, required, max 120 chars), Firm Name (text, required, max 200 chars), Number of Lawyers (select: 1, 2-4, 5-10, 11-25, 26+; required), Work Email (email, required, valid email format, max 254 chars), Phone Number (text, optional, max 30 chars, accepting international format with `+` prefix), Country (select: Jordan, Lebanon, Palestine, Iraq, Other; required), Notes (textarea, optional, max 1000 chars).
- GIVEN the visitor fills the form correctly WHEN they click the "Submit Request" button THEN the form POSTs to `/api/v1/public/demo-requests`, the request is validated server-side via `StoreDemoRequestRequest` FormRequest, a `demo_requests` row is inserted, an email is dispatched to `abdullah@efirm.io` via the `demo_requests` queue, a Linear ticket is created via the Linear API in the "Leads" project, and the visitor is redirected to `/demo-request/thank-you`.
- GIVEN any required field is missing WHEN the form is submitted THEN the API returns HTTP 422 with field-specific error messages, the page re-renders with the invalid field outlined in red, and an inline error message appears below each invalid field in the visitor's selected locale.
- GIVEN the form is submitted with valid data WHEN the Linear ticket creation fails (network timeout or API error) THEN the `demo_requests` row is still inserted, the email notification still dispatches, the visitor still sees the thank-you page, and a Laravel log entry at level `error` with channel `linear-integration` is written for later reconciliation. The visitor experience is never blocked by Linear API availability.
- GIVEN a duplicate submission from the same email within 1 hour WHEN the form is submitted THEN the API returns HTTP 429 with a message in the visitor's locale, and no second row is inserted.
- GIVEN the thank-you page WHEN it renders THEN it displays a confirmation headline, a paragraph stating "Abdullah will respond within one business day", and a "Return to Home" link.

**Edge Cases:**
- **Error:** If the database write fails, return HTTP 500 with a generic message in the visitor's locale and log the exception with full stack trace to the `laravel.log` file at level `error`.
- **Empty:** Not applicable — form requires submission.
- **Loading:** Submit button shows a spinner icon and disables on click; text changes to "Submitting…" / "جاري الإرسال…" until the response resolves.
- **Offline:** If submission is attempted while offline, the browser-native error displays; no client-side offline queueing is implemented for v1.
- **Boundary:** All character limits enforced at the FormRequest layer; any input exceeding limits returns HTTP 422 with the exceeded-limit field named.
- **RTL:** Form labels right-align, inputs accept RTL text correctly, validation messages appear right-aligned, submit button retains primary position (right-most in RTL).

## US-005: PDPL-compliant cookie consent banner appears on first visit

As a visitor whose data is governed by Jordan's PDPL Law No. 24/2023, I want to be presented with a granular cookie consent banner that respects my choices so that I can use the eFirm landing page without unauthorised analytics tracking.

**Acceptance Criteria:**
- GIVEN a visitor has no `efirm_consent` cookie WHEN they load any page on `efirm.io` THEN a cookie banner renders as a fixed-position bottom-of-viewport overlay containing: a heading ("We use cookies"), a body paragraph explaining PDPL compliance, three toggles (Essential: locked on, Analytics: default off, Marketing: default off), a "Customise" button, an "Accept All" button, and a "Reject Non-Essential" button.
- GIVEN the visitor clicks "Accept All" WHEN the click is processed THEN a cookie `efirm_consent` is set with value `{"essential":true,"analytics":true,"marketing":true,"timestamp":"ISO8601"}` for 365 days, the banner dismisses, and GA4 loads in the next event-loop tick.
- GIVEN the visitor clicks "Reject Non-Essential" WHEN the click is processed THEN `efirm_consent` is set with value `{"essential":true,"analytics":false,"marketing":false,"timestamp":"ISO8601"}` for 365 days, the banner dismisses, and GA4 does NOT load on the current page or any subsequent page until consent changes.
- GIVEN the visitor clicks "Customise" WHEN the click is processed THEN a modal opens with the three toggles, descriptive text for each category, and a "Save Preferences" button; on save, the `efirm_consent` cookie reflects the selected toggles.
- GIVEN a visitor has previously set `efirm_consent` WHEN they return THEN the banner does NOT re-appear; instead, a small persistent link in the footer reading "Cookie Settings" / "إعدادات ملفات تعريف الارتباط" re-opens the customise modal on click.
- GIVEN GA4 has loaded WHEN the visitor browses any page THEN no personally identifying information (name, email, IP address) is transmitted to Google; IP anonymisation is enforced at the GA4 tag configuration level (`anonymize_ip: true`).

**Edge Cases:**
- **Error:** If the cookie write fails (browser cookie quota exceeded, Safari ITP edge case), the banner remains visible and a console warning is logged; no analytics scripts load.
- **Empty:** Not applicable.
- **Loading:** Banner is server-rendered HTML, visible on first paint.
- **Offline:** Banner displays regardless of network state; consent choices persist via cookies, which are local.
- **Boundary:** `efirm_consent` cookie value is JSON-encoded with maximum size 256 bytes; any larger payload is rejected and treated as no consent.
- **RTL:** Banner and modal flip directionality; toggle switches mirror (the "on" position is on the left in RTL); button order reverses per RTL reading order.

## US-006: FAQ accordion expands with single-open behaviour on mobile, multi-open on desktop

As a visitor with specific questions about trial terms, data residency, or jurisdictional coverage, I want to scan and expand FAQ items selectively so that I can find answers without scrolling through walls of text.

**Acceptance Criteria:**
- GIVEN the FAQ section renders WHEN it loads THEN 10 FAQ items display in collapsed state, each with a question heading and a caret icon indicating expandability.
- GIVEN a visitor on desktop (≥ 1024px viewport) WHEN they click an FAQ question THEN that item expands; previously-expanded items remain expanded; multiple items can be open simultaneously.
- GIVEN a visitor on mobile (≤ 640px viewport) WHEN they click an FAQ question THEN that item expands and any previously-expanded item collapses; only one item is open at a time.
- GIVEN any FAQ item is expanded WHEN the caret icon is inspected THEN it has rotated 180 degrees via a CSS transform with a 200ms transition.
- GIVEN a visitor with `prefers-reduced-motion` enabled WHEN they expand an FAQ item THEN the caret rotation and content reveal occur instantly with no animation.
- GIVEN the FAQ section WHEN inspected for accessibility THEN each question is a `<button>` element with `aria-expanded` attribute reflecting state, `aria-controls` pointing to the answer container, and the answer container has `role="region"` with an `aria-labelledby` attribute.

**Edge Cases:**
- **Error:** Not applicable — content is static.
- **Empty:** If the `marketing.faq.items` array in `resources/lang/{en,ar}/marketing.php` is empty, the entire FAQ section is hidden via a Blade `@if` check.
- **Loading:** Not applicable.
- **Offline:** Fully functional offline once page has loaded.
- **Boundary:** Maximum 15 FAQ items rendered per locale; if the localization array exceeds 15 entries, only the first 15 render and a Laravel log warning is written.
- **RTL:** Caret icons mirror direction; question text right-aligns; expanded answer text right-aligns; the caret animation rotates in the same screen direction (clockwise from collapsed to expanded) regardless of locale.

## US-007: Page is crawlable with complete SEO metadata and structured data

As a search engine crawler (Googlebot, Bingbot, or any compliant crawler) and as a future organic-search visitor, I want eFirm's landing page to expose complete SEO metadata so that the page can be discovered, accurately previewed in search results, and ranked for target keywords.

**Acceptance Criteria:**
- GIVEN any public page on `efirm.io` WHEN inspected via a curl or view-source request THEN the `<head>` contains: `<title>` tag (max 60 chars), meta description (max 160 chars), canonical link, Open Graph tags (og:title, og:description, og:image, og:url, og:type, og:locale), Twitter card tags (twitter:card, twitter:title, twitter:description, twitter:image), and a `hreflang` link for the alternate locale (en ↔ ar).
- GIVEN any public page WHEN inspected for JSON-LD THEN a `<script type="application/ld+json">` block contains a `LegalService` schema with `name`, `description`, `url`, `areaServed` (an array of Jordan, Lebanon, Palestine, Iraq country codes), `priceRange`, `address`, and `provider` fields.
- GIVEN a crawler requests `/sitemap.xml` WHEN the response is inspected THEN it returns HTTP 200 with valid XML listing all public routes with `lastmod`, `changefreq`, and `priority` for each.
- GIVEN a crawler requests `/robots.txt` WHEN the response is inspected THEN it returns HTTP 200 with content allowing all crawlers, disallowing `/app/*` (the Filament admin), and referencing the sitemap location.
- GIVEN a Lighthouse SEO audit runs against `efirm.io/` WHEN the audit completes THEN the SEO score is ≥ 95.
- GIVEN the page targets the keyword clusters specified in decision R-8 WHEN the rendered HTML is inspected THEN at least one `h2` in each language contains a primary keyword from each cluster: practice-management ("Legal Practice Management" / "إدارة مكتب المحاماة"), AI ("Arabic Legal AI" / "الذكاء الاصطناعي القانوني"), and PDPL/procedural ("PDPL Compliance" / "الامتثال لقانون حماية البيانات الشخصية").

**Edge Cases:**
- **Error:** If a metadata source value is missing from `config/seo.php`, a default fallback string applies and a Laravel log warning is written at level `warning` channel `seo`.
- **Empty:** Not applicable — metadata is required configuration.
- **Loading:** All SEO metadata is server-rendered in the initial response.
- **Offline:** Not applicable — crawlers and visitors require network.
- **Boundary:** `<title>` tag truncates at 60 chars at the Blade-rendering layer with a Pest test asserting the constraint; meta description truncates at 160 chars similarly.
- **RTL:** `hreflang` declarations correctly identify the alternate locale; the Arabic page declares `hreflang="ar"` and points to the English page as `hreflang="en"`; both pages additionally declare `hreflang="x-default"` pointing to the English version.

## US-008: Page is accessible (WCAG 2.1 AA) and respects reduced motion

As a visitor using assistive technology (screen reader, keyboard-only navigation) or with motion-sensitivity preferences, I want the eFirm landing page to be fully accessible so that I am not excluded from evaluating the product.

**Acceptance Criteria:**
- GIVEN a visitor using a screen reader (NVDA, VoiceOver, or TalkBack) WHEN they navigate the page THEN landmark roles (banner, navigation, main, contentinfo) are present, headings are in correct hierarchical order (one h1 per page, no skipped levels), and all interactive elements have accessible names.
- GIVEN any image on the page WHEN inspected THEN it has an `alt` attribute; decorative images use `alt=""` and content images describe their content in the page's current locale.
- GIVEN any button or link WHEN inspected THEN its accessible name conveys its action (e.g., "Start Free Trial", not "Click here").
- GIVEN any form input WHEN inspected THEN it has an associated `<label>` element via `for/id` or `aria-labelledby`, never a placeholder-as-label pattern.
- GIVEN a keyboard-only visitor WHEN they navigate with Tab THEN focus order follows visual reading order in the current locale, and every interactive element shows a visible focus indicator with ≥ 3:1 contrast against its background.
- GIVEN any text-background colour combination WHEN measured THEN contrast ratio is ≥ 4.5:1 for normal text and ≥ 3:1 for large text per WCAG AA.
- GIVEN a visitor with the `prefers-reduced-motion: reduce` media query active WHEN they interact with the page THEN all CSS transitions and animations of more than 200ms are disabled or reduced to instant transitions.
- GIVEN an axe-core automated accessibility audit runs against `efirm.io/` WHEN the audit completes THEN zero violations of WCAG 2.1 Level AA are reported.

**Edge Cases:**
- **Error:** Not applicable — accessibility is a rendering property.
- **Empty:** Not applicable.
- **Loading:** Loading spinners and skeleton states must include `aria-live="polite"` announcements for screen readers.
- **Offline:** Not applicable.
- **Boundary:** Touch targets are minimum 44x44 CSS pixels per WCAG 2.5.5; no interactive element falls below this.
- **RTL:** Focus order in RTL follows visual reading (right-to-left within rows, top-to-bottom across rows); skip-to-content link renders in the upper-right corner of the viewport when locale is `ar`.

---

# 3. Wireframes Documentation

## Pages in scope

| Page ID | Path | Description |
|---|---|---|
| P-1 | `/` (and `/ar`) | Landing page — single-scroll, 13 sections |
| P-2 | `/demo-request` (and `/ar/demo-request`) | Demo request form |
| P-3 | `/demo-request/thank-you` (and `/ar/...`) | Confirmation page |
| P-4 | `/terms` (and `/ar/terms`) | Terms of Service stub |
| P-5 | `/privacy` (and `/ar/privacy`) | Privacy Policy stub |
| P-6 | `/dpa` (and `/ar/dpa`) | Data Processing Addendum stub |
| P-7 | `/ai-disclaimer` (and `/ar/ai-disclaimer`) | AI Disclaimer stub |

## P-1: Landing page — 13 sections

Each section is a Blade component under `resources/views/components/marketing/`. Section order is fixed.

### S-1: Header / Navigation (sticky)

**File:** `resources/views/components/marketing/header.blade.php`

**Desktop (≥ 1024px):**
- Fixed-position bar, full width, height 72px, white background
- Adds `shadow-sm` after 100px scroll
- 12-column grid, padding-x 24px
- Left: eFirm logo wordmark (height 32px), links to `/`
- Centre: Nav links horizontally — "Features", "Pricing", "Security", "FAQ" (smooth-scroll anchors)
- Right: Language toggle, "Sign In" link (→ `/app/login`), Primary CTA button ("Start Free Trial" → `/register`)

**Tablet (640–1024px):** Logo + hamburger menu icon (right side); hamburger expands a full-width top drawer.

**Mobile (≤ 640px):** Same as tablet; drawer is full-viewport.

**RTL:** Logo → right; nav links and CTAs → left; hamburger → left on tablet/mobile; drawer slides from right.

### S-2: Hero

**File:** `resources/views/components/marketing/hero.blade.php`

**Desktop:**
- Full-width, padding-y 96px, padding-x 64px
- 12-column grid, 8-col text zone left, 4-col visual zone right (mirrored in RTL)
- Background: subtle gradient slate-50 → white
- Text zone (vertical): eyebrow tag, h1 headline (text-5xl/4xl/3xl responsive), sub-headline (text-xl), CTA row (primary filled + secondary outline), trust micro-line (text-sm slate-500)
- Visual zone: one product screenshot in custom browser-chrome frame with subtle drop shadow, aspect ratio 3:2

**Tablet/Mobile:** Stacks vertically (text first); headline reduces to text-4xl/text-3xl; padding-y → 64/48px.

**RTL:** Text zone moves right; visual moves left; text alignment right; CTA order reverses.

### S-3: Trust Strip

**File:** `resources/views/components/marketing/trust-strip.blade.php`

5 badges in a centred row on slate-100 background, padding-y 24px. Each: 24px icon + text-sm slate-700.

Badges: PDPL Article 13 · Frankfurt EU Data Residency · Bilingual AR/EN · Audit-Logged · Trust-Account-Grade Ledger.

**Tablet:** 5 cols → 3+2 grid. **Mobile:** vertical stack.

**RTL:** Order reverses; icons on right of text.

### S-4: The Problem

**File:** `resources/views/components/marketing/problem.blade.php`

Centred container max-w-3xl. Eyebrow + h2 + body intro, followed by 3 problem-cards in horizontal grid (icon 48px + title text-xl + body text-base slate-600).

**Tablet:** 3 cols → 2+1. **Mobile:** Stack vertically.

**RTL:** Card order reverses; content right-aligns.

### S-5: Single Workspace Claim

**File:** `resources/views/components/marketing/single-workspace.blade.php`

12-col grid: 5-col text + 7-col annotated screenshot. Text: eyebrow + h2 + body + 4 bullets. Visual: large annotated screenshot of Matter detail view with 4 numbered callout pins.

**Tablet/Mobile:** Stack; callouts become numbered list below screenshot.

**RTL:** Text right, visual left; callout numerals remain Western.

### S-6: Feature Pillars (6 cards)

**File:** `resources/views/components/marketing/feature-pillars.blade.php`

slate-50 background; centred header zone; 6-card grid (3 cols × 2 rows). Each card: padding 32px, white, rounded-lg, hover:shadow-md.

Cards: Matters & Cases · Documents & Contracts · Arabic-Native AI · Billing & Trust Accounts · Jordanian Procedural Accuracy · Team & Permissions.

**Tablet:** 3 → 2 cols × 3 rows. **Mobile:** 1 col × 6 rows.

**RTL:** Order reverses.

### S-7: Arabic AI Demo

**File:** `resources/views/components/marketing/arabic-ai-demo.blade.php`

Dark background (slate-900), white text. Centred container max-w-5xl. Eyebrow + h2 in white + body in slate-300. Below: side-by-side comparison card — left: Arabic legal prompt, right: AI's Arabic legal output. Subtle divider.

**Tablet:** Stacks (prompt above output). **Mobile:** Same.

**RTL:** When locale=ar, prompt appears on right naturally.

### S-8: Jordanian Procedural Accuracy

**File:** `resources/views/components/marketing/procedural-accuracy.blade.php`

12-col grid: 6-col text + 6-col visualisation. Text: eyebrow + h2 + body + 4 accuracy claims as bullets. Visual: stylised inline-SVG court hearing timeline (no real client data).

**Tablet:** Stack. **Mobile:** Text only; visual hidden via `hidden md:block`.

**RTL:** Text right, visual left; timeline flows right-to-left.

### S-9: Pricing Table

**File:** `resources/views/components/marketing/pricing.blade.php`

slate-50 background. Centred header zone. 3-card grid below. Pro card visually elevated (scale-105, shadow-lg, slate-900 top border 4px). Founding-firm badge on Pro tier: amber-100 background + amber-800 text + rounded-full, positioned with -translate-y-1/2 offset.

Each card: tier name (text-xl) + price ($XX, text-5xl 700) + unit + JOD line (AR only) + 6 feature bullets + CTA.

Below cards: 8-row feature matrix table.

**Mobile:** Cards stack; matrix horizontally scrolls with sticky first column.

**RTL:** Card order reverses (Enterprise, Pro, Starter); matrix flips column order.

### S-10: Security & Data

**File:** `resources/views/components/marketing/security.blade.php`

Centred max-w-5xl. Eyebrow + h2 + body intro. 6-cell grid (3 cols × 2 rows): PDPL · Frankfurt residency · Trust ledger · Audit log · SOC 2 II infrastructure · ISO 27001 2027 roadmap.

**Tablet:** 3 → 2 cols × 3 rows. **Mobile:** Stack.

**RTL:** Cell order reverses.

### S-11: FAQ

**File:** `resources/views/components/marketing/faq.blade.php`

slate-50 background. Centred max-w-3xl. Eyebrow + h2 ("Common Questions"). 10 accordion items vertically stacked, each with question (h3 styled as button) + caret icon on right (left in RTL) + expanded answer.

Mobile single-open, desktop multi-open per US-006.

**RTL:** Question text and caret mirror.

### S-12: Final CTA Band

**File:** `resources/views/components/marketing/final-cta.blade.php`

slate-900 background, white text. h2 (text-4xl) + body in slate-300 + primary CTA (white bg, slate-900 text, large) + trust micro-line.

### S-13: Footer

**File:** `resources/views/components/marketing/footer.blade.php`

slate-50, padding-y 64px. 12-col grid, 4 zones × 3 cols: Brand/Address · Product links · Legal links · Company/Contact. Bottom: copyright + legal-stub disclaimer.

**Tablet:** 4 zones → 2 cols × 2 rows. **Mobile:** Vertical stack.

**RTL:** Zone order reverses; disclaimer right-aligns.

## P-2: Demo Request

Centred max-w-2xl, padding-y 96px. h1 + intro paragraph. Vertical form: Full Name, Firm Name, Number of Lawyers (select), Work Email, Phone (optional, helper "Include country code"), Country (select), Notes (textarea, 4 rows, optional, max 1000). Submit button full-width filled slate-900. Privacy footer line.

## P-3: Thank You

Centred max-w-2xl, padding-y 128px. Vertical stack centred: 64px checkmark icon (green-600) + h1 + body confirming firm name (interpolated) + "Return to Home" link.

## P-4 / P-5 / P-6 / P-7: Legal Stubs

Centred max-w-3xl, padding-y 96px. Shared layout (`resources/views/marketing/legal/show.blade.php`) parameterised by slug. h1 + last-updated line + Markdown body rendered from `resources/markdown/legal/{slug}-{locale}.md`. Persistent disclaimer banner at top (amber-100 bg) reading "This document is a stub pending final legal review."

## Navigation Flow

Entry points: direct, search result, advertised link.

**From P-1:**
- Logo → P-1
- "Features"/"Pricing"/"Security"/"FAQ" → P-1 #anchor (smooth-scroll)
- Language toggle → `/ar` (302 redirect)
- "Sign In" → `/app/login`
- Primary CTA "Start Free Trial" → `/register?utm_source=...`
- Secondary CTA "Book a Demo" → P-2
- Pricing card CTAs → `/register?utm_source=pricing_table&plan={slug}`
- Footer Product links → corresponding section anchors
- Footer Legal links → P-4/P-5/P-6/P-7
- Footer "Cookie Settings" → opens cookie modal in-page

**From P-2:** Logo → P-1; Valid submit → P-3; Invalid → P-2 (re-render).
**From P-3:** "Return to Home" → P-1.
**From P-4–7:** Logo → P-1; footer links → corresponding pages.

## Responsive Notes — Global

Tailwind defaults:
- Mobile 0–639px (no prefix)
- Tablet 640–1023px (sm:, md:)
- Desktop ≥ 1024px (lg:, xl:)
- Wide ≥ 1280px (xl:)

Container max-widths: `max-w-7xl` content (1280px), `max-w-3xl` reading-flow (768px), `max-w-2xl` form (672px).

Vertical rhythm: section padding-y 96/64/48px (lg/md/sm); component internal padding 32/24/16px.

Typography: h1 text-4xl→5xl→6xl; h2 text-3xl→4xl→5xl; h3 text-xl→2xl; body text-base; hero sub text-lg.

## RTL Notes — Global

Activation: `locale=ar` triggers `<html dir="rtl" lang="ar">` via `SetPublicLocale` middleware.

Fonts:
- English: `'Inter', system-ui, sans-serif`
- Arabic: `'IBM Plex Sans Arabic', 'Tajawal', system-ui, sans-serif` (weights 400/500/600/700)

Per-component RTL behaviour:

| Component | LTR | RTL |
|---|---|---|
| Header logo | Left | Right |
| Header nav | Centre | Centre (order reversed) |
| Header CTAs | Right | Left |
| Hero text zone | Left half | Right half |
| Hero visual | Right half | Left half |
| Hero CTAs | Primary then secondary | Primary remains visually right |
| Card grids | First card left | First card right (`flex-row-reverse`) |
| Form labels | Left-aligned | Right-aligned |
| Validation errors | Left-aligned | Right-aligned |
| FAQ caret | Right of question | Left of question |
| Footer columns | 1, 2, 3, 4 visual | 4, 3, 2, 1 visual |
| Directional icons | Native | Mirror via `scale-x-[-1]` |
| Non-directional icons | Native | Native |
| Mobile drawer | Slides from left | Slides from right |
| Pricing cards | Starter, Pro, Enterprise | Enterprise, Pro, Starter |

Numbers always Western Arabic (0–9) in both locales. Mixed-content paragraphs use `dir="auto"`.

## Accessibility Notes — Global

Landmarks: `<header role="banner">`, `<nav role="navigation">`, `<main role="main">`, `<footer role="contentinfo">`.

Heading hierarchy: one h1 per page; h2 per top-level section; h3 within. No skipped levels.

Focus indicators: `focus:ring-2 focus:ring-slate-900 focus:ring-offset-2` (offset reverses to `ring-offset-white` on dark backgrounds).

Skip-to-content link: hidden until focus; anchors to `<main>`; positioned top-left LTR / top-right RTL.

Reduced motion: global CSS rule disables transitions/animations > 200ms.

## Stakeholder Sign-off

| Stakeholder | Role | Date | Method | Status |
|---|---|---|---|---|
| Abdullah Mohammed | Founder / Product Designer | 2026-06-24 | AODC chat session | Approved |
| Khaldoun Khater | Practitioner Advisor | PENDING | Monday walkthrough or post-walkthrough email | Pending — pre-launch gate ≥ 48h before public DNS cutover |

---

# 4. API Contracts

## Scope

Public landing page is predominantly server-rendered Blade with minimal API surface. Two endpoints:

- **API-001:** `POST /api/v1/public/demo-requests` — Demo lead capture
- **API-002:** `POST /api/v1/public/cookie-consent` — PDPL consent audit record

All other interactions (CTA clicks, language toggles, FAQ accordions, smooth-scroll) are client-side anchors or standard Laravel HTTP redirects.

**OpenAPI spec location:** `openapi/spec.yaml` (Engineer agent MUST update during implementation).

## API-001: Submit Demo Request

| Field | Value |
|---|---|
| **Endpoint** | `POST /api/v1/public/demo-requests` |
| **Description** | Create a new demo request lead. Triggers email to `abdullah@efirm.io` and auto-creates Linear ticket in "Leads" project. |
| **Authentication** | None (public) |
| **Middleware** | `throttle:5,60` (5 requests per IP per 60 minutes) |
| **Controller** | `app/Http/Controllers/Api/V1/Public/DemoRequestController@store` |
| **FormRequest** | `app/Http/Requests/Public/StoreDemoRequestRequest.php` |
| **Service** | `app/Services/DemoRequestService.php` |
| **Job** | `app/Jobs/CreateLinearLeadTicketJob.php` (queue `demo_requests`, retries 3× backoff 30/60/120s) |
| **Mailables** | `app/Mail/DemoRequestNotificationMail.php` (to Abdullah), `app/Mail/DemoRequestConfirmationMail.php` (to submitter, bilingual) |

### Request

**Headers:**
```
Content-Type: application/json
Accept: application/json
Accept-Language: en | ar
X-CSRF-TOKEN: {csrf_token}
```

**Body:**
```json
{
  "full_name":    "string - required - max:120 - validation: 'required|string|max:120'",
  "firm_name":    "string - required - max:200 - validation: 'required|string|max:200'",
  "lawyer_count": "string - required - in:1,2-4,5-10,11-25,26+",
  "email":        "string - required - email - max:254 - validation: 'required|email:rfc,dns|max:254'",
  "phone":        "string - optional - max:30 - validation: 'nullable|string|max:30|regex:/^[+0-9 \\-()]+$/'",
  "country":      "string - required - in:JO,LB,PS,IQ,OTHER",
  "notes":        "string - optional - max:1000 - validation: 'nullable|string|max:1000'",
  "locale":       "string - required - in:en,ar",
  "honeypot":     "string - must be empty - validation: 'present|size:0'"
}
```

**Notes:**
- Honeypot field rendered as hidden input `name="company_website"` with `aria-hidden="true"`.
- CSRF protection enforced via Laravel `VerifyCsrfToken` middleware.

### Response (201 Created)

```json
{
  "id":          "string (UUID)",
  "status":      "string - always 'received'",
  "message":     "string - localized confirmation",
  "redirect_to": "string - '/demo-request/thank-you' or '/ar/demo-request/thank-you'"
}
```

### Error Responses

| Code | Body |
|---|---|
| **400** | `{"error":"validation_error","message":"The request body could not be parsed."}` |
| **422** | `{"error":"unprocessable","errors":{"field":["localized message"]}}` |
| **429** | `{"error":"too_many_requests","message":"...","retry_after":integer}` |
| **500** | `{"error":"server_error","message":"..."}` |

### Database side-effects

Insert into `demo_requests`:

| Column | Type | Notes |
|---|---|---|
| `id` | UUID | PK |
| `full_name` | varchar(120) | |
| `firm_name` | varchar(200) | |
| `lawyer_count` | enum | `1`, `2-4`, `5-10`, `11-25`, `26+` |
| `email` | varchar(254) | indexed |
| `phone` | varchar(30) | nullable |
| `country` | char(5) | enum JO/LB/PS/IQ/OTHER |
| `notes` | text | nullable |
| `locale` | char(2) | `en`/`ar` |
| `ip_address` | varchar(45) | IPv4/IPv6 |
| `user_agent` | text | |
| `utm_source` | varchar(100) | nullable |
| `linear_ticket_id` | varchar(50) | nullable |
| `linear_ticket_url` | varchar(500) | nullable |
| `notification_sent_at` | timestamp | nullable |
| `created_at`, `updated_at` | timestamps | |

### Migration

```bash
php artisan make:migration create_demo_requests_table
```

**Up schema:**
```php
Schema::create('demo_requests', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('full_name', 120);
    $table->string('firm_name', 200);
    $table->enum('lawyer_count', ['1','2-4','5-10','11-25','26+']);
    $table->string('email', 254)->index();
    $table->string('phone', 30)->nullable();
    $table->enum('country', ['JO','LB','PS','IQ','OTHER']);
    $table->text('notes')->nullable();
    $table->char('locale', 2);
    $table->string('ip_address', 45);
    $table->text('user_agent');
    $table->string('utm_source', 100)->nullable();
    $table->string('linear_ticket_id', 50)->nullable();
    $table->string('linear_ticket_url', 500)->nullable();
    $table->timestamp('notification_sent_at')->nullable();
    $table->timestamps();
    $table->index('created_at');
});
```

**Down:** `Schema::dropIfExists('demo_requests');`

**Run:** `php artisan migrate`

### Duplicate-submission guard

Before insertion, `DemoRequestService::store()` queries for an existing row with same email and `created_at` within last 60 minutes. If found, return 429 with localised message.

### Linear API integration

**File:** `app/Services/Integrations/LinearClient.php`

**Config** in `config/services.php`:
```php
'linear' => [
    'api_key'    => env('LINEAR_API_KEY'),
    'team_id'    => env('LINEAR_TEAM_ID'),
    'project_id' => env('LINEAR_LEADS_PROJECT_ID'),
],
```

GraphQL endpoint: `https://api.linear.app/graphql`. Mutation: `issueCreate` with title, description (Markdown including all form fields), labels `["lead", "landing-page"]`, team_id, project_id. On success, capture `issue.id` and `issue.url` and persist.

### Test files (Pest 4.7)

- `tests/Feature/Api/V1/Public/DemoRequestApiTest.php`
- `tests/Unit/Services/DemoRequestServiceTest.php`
- `tests/Feature/Jobs/CreateLinearLeadTicketJobTest.php`
- `tests/Feature/Mail/DemoRequestNotificationMailTest.php`
- `tests/Feature/Mail/DemoRequestConfirmationMailTest.php`

## API-002: Record Cookie Consent

| Field | Value |
|---|---|
| **Endpoint** | `POST /api/v1/public/cookie-consent` |
| **Description** | Persist PDPL Article 13 cookie consent audit record. Cookie set client-side; this endpoint records server-side for compliance. |
| **Authentication** | None (public) |
| **Middleware** | `throttle:30,1` (30 requests per IP per minute) |
| **Controller** | `app/Http/Controllers/Api/V1/Public/CookieConsentController@store` |
| **FormRequest** | `app/Http/Requests/Public/StoreCookieConsentRequest.php` |
| **Service** | `app/Services/CookieConsentService.php` |

### Request

**Body:**
```json
{
  "consent_id": "UUID - required - validation: 'required|uuid'",
  "essential":  "boolean - always true - validation: 'required|boolean|accepted'",
  "analytics":  "boolean - required - validation: 'required|boolean'",
  "marketing":  "boolean - required - validation: 'required|boolean'",
  "locale":     "string - in:en,ar",
  "source":     "string - in:accept_all,reject_non_essential,customise,reconsent"
}
```

### Response (201 Created)

```json
{ "id": "UUID", "recorded": true }
```

### Error Responses

| Code | Notes |
|---|---|
| **422** | Field-level validation errors |
| **429** | Rate limit |
| **500** | Generic server error; cookie state local remains source of truth for UX |

### Database side-effects

Insert into `cookie_consent_records` (append-only):

| Column | Type |
|---|---|
| `id` | UUID PK |
| `consent_id` | UUID indexed |
| `essential` | boolean default true |
| `analytics` | boolean |
| `marketing` | boolean |
| `locale` | char(2) |
| `source` | varchar(30) |
| `ip_address` | varchar(45) |
| `user_agent` | text |
| `created_at` | timestamp |

**No updates, no deletes.** Each consent change creates a new row. Mirrors AODC append-only ledger convention used for `admin_activity_log` and `subscription_events` per CLAUDE.md.

### Migration

```bash
php artisan make:migration create_cookie_consent_records_table
```

**Up:**
```php
Schema::create('cookie_consent_records', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('consent_id')->index();
    $table->boolean('essential')->default(true);
    $table->boolean('analytics');
    $table->boolean('marketing');
    $table->char('locale', 2);
    $table->string('source', 30);
    $table->string('ip_address', 45);
    $table->text('user_agent');
    $table->timestamp('created_at')->useCurrent();
    $table->index('created_at');
});
```

**Down:** `Schema::dropIfExists('cookie_consent_records');`

### Test files

- `tests/Feature/Api/V1/Public/CookieConsentApiTest.php`
- `tests/Unit/Services/CookieConsentServiceTest.php`

## Route file additions

**File:** `routes/web.php` — add at TOP, before any authenticated/Filament routes:

```php
Route::middleware('public.locale')->group(function () {

    // Landing page (English default)
    Route::get('/', [LandingController::class, 'show'])->name('public.landing');

    // Arabic locale-prefixed routes
    Route::prefix('ar')->group(function () {
        Route::get('/', [LandingController::class, 'show'])
            ->defaults('forced_locale', 'ar')
            ->name('public.landing.ar');
        // Mirror every public route below with 'ar' prefix
    });

    // Demo request flow
    Route::get('/demo-request', [DemoRequestController::class, 'create'])
        ->name('public.demo.create');
    Route::get('/demo-request/thank-you', [DemoRequestController::class, 'thankYou'])
        ->name('public.demo.thanks');

    // Legal stub pages
    Route::get('/terms',         [LegalController::class, 'show'])->defaults('slug', 'terms')->name('public.legal.terms');
    Route::get('/privacy',       [LegalController::class, 'show'])->defaults('slug', 'privacy')->name('public.legal.privacy');
    Route::get('/dpa',           [LegalController::class, 'show'])->defaults('slug', 'dpa')->name('public.legal.dpa');
    Route::get('/ai-disclaimer', [LegalController::class, 'show'])->defaults('slug', 'ai-disclaimer')->name('public.legal.ai-disclaimer');

    // SEO assets
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
    Route::get('/robots.txt',  [SeoController::class, 'robots']);
});
```

**File:** `routes/api.php`:

```php
Route::prefix('v1/public')->middleware('throttle:public')->group(function () {
    Route::post('/demo-requests',  [DemoRequestController::class, 'store'])->middleware('throttle:5,60');
    Route::post('/cookie-consent', [CookieConsentController::class, 'store'])->middleware('throttle:30,1');
});
```

**Middleware:** `app/Http/Middleware/SetPublicLocale.php`, registered in `app/Http/Kernel.php` as alias `public.locale`.

**RateLimiter** in `app/Providers/RouteServiceProvider.php`: register `public` limiter as `Limit::perMinute(60)->by($request->ip())`.

**OpenAPI spec update:** After implementation, `openapi/spec.yaml` MUST be updated with both paths, request schemas, all response codes (201, 400, 422, 429, 500), tags `["Public", "Marketing"]`, `security: []`.

---

# 5. Content Specification

## Localization file structure

- `resources/lang/en/marketing.php`
- `resources/lang/ar/marketing.php`

Identical top-level key structure:
```php
return [
    'meta'       => [...],   // SEO per page
    'header'     => [...],
    'hero'       => [...],
    'trust'      => [...],
    'problem'    => [...],
    'solution'   => [...],
    'features'   => [...],
    'ai_demo'    => [...],
    'procedure'  => [...],
    'pricing'    => [...],
    'security'   => [...],
    'faq'        => [...],
    'final_cta'  => [...],
    'footer'     => [...],
    'demo_form'  => [...],
    'thank_you'  => [...],
    'legal'      => [...],
    'cookies'    => [...],
    'validation' => [...],
];
```

Invocation: `{{ __('marketing.hero.headline') }}` in Blade.

> **NOTE:** The full content specification — every EN/AR string pair for every section — is captured in the chat session that produced this WRP. Due to the volume (~600 unique string pairs across 13 sections + 4 stub pages + cookie banner + SEO meta + validation messages), the canonical source is the `marketing.php` files themselves once written. The Engineer agent and Claude Code should populate `resources/lang/en/marketing.php` and `resources/lang/ar/marketing.php` directly from the content blocks in the WRP chat output (Section 5).

### Critical content blocks reproduced here

#### Hero
- **Headline EN:** "Run your law firm on one workspace built for the Levant."
- **Headline AR:** "أدِر مكتب المحاماة الخاص بك على منصة واحدة مصممة للمحاميين في بلاد الشام."
- **Sub-headline EN:** "Matters, documents, billing, and Arabic-native AI on a single screen. PDPL-compliant. Built in Amman."
- **Sub-headline AR:** "ملفات القضايا والمستندات والفواتير وذكاء اصطناعي يفهم العربية، كل ذلك على شاشة واحدة. متوافق مع قانون حماية البيانات الشخصية. صُمِّم في عمّان."
- **Primary CTA EN/AR:** "Start Free Trial" / "ابدأ التجربة المجانية"
- **Secondary CTA EN/AR:** "Book a Demo" / "احجز عرضاً توضيحياً"
- **Trust micro-line EN:** "14-day trial · no credit card required · cancel anytime"
- **Trust micro-line AR:** "تجربة مدتها 14 يوماً · بدون بطاقة ائتمان · يمكنك الإلغاء في أي وقت"

#### Pricing tier names and prices
- Starter / ستارتر / $20 / "ما يعادل JOD ~14"
- Pro / برو / $25 / "ما يعادل JOD ~18" (Most Popular / الأكثر شعبية)
- Enterprise / إنتربرايز / $30 / "ما يعادل JOD ~21"
- **Founding badge EN:** "First 50 founding firms: 30% off Pro for year 1"
- **Founding badge AR:** "أول 50 مكتباً مؤسِّساً: خصم 30٪ على باقة برو للسنة الأولى"

#### Arabic AI Demo — exact prompt + output (renders as static content)

**Prompt label EN/AR:** "The lawyer asks" / "ما يطلبه المحامي"

**Prompt content (Arabic, both locales):**
> أعد صياغة بند السرية في عقد توزيع تجاري بحيث يلزم الطرف الثاني بسرية الأسرار التجارية لمدة خمس سنوات بعد انتهاء العقد، مع نص واضح على اختصاص محاكم عمّان وتطبيق القانون الأردني.

**Output label EN/AR:** "eFirm AI responds" / "رد إي فيرم الذكي"

**Output content (Arabic, both locales):**
> بند السرية:
> يلتزم الطرف الثاني بالحفاظ على سرية كافة الأسرار التجارية والمعلومات المالية والفنية التي يطّلع عليها بحكم تنفيذ هذا العقد، وذلك لمدة (خمس) سنوات تبدأ من تاريخ انتهاء أو إنهاء هذا العقد لأي سبب كان. وتختص محاكم عمّان دون سواها بالنظر في أي نزاع ينشأ عن تطبيق أو تفسير هذا البند، ويسري عليه القانون الأردني.

#### Cookie consent banner

- **Heading EN/AR:** "We use cookies" / "نستخدم ملفات تعريف الارتباط"
- **Accept All EN/AR:** "Accept All" / "قبول الكل"
- **Reject Non-Essential EN/AR:** "Reject Non-Essential" / "رفض غير الأساسية"
- **Customise EN/AR:** "Customise" / "تخصيص"
- **Footer link EN/AR:** "Cookie Settings" / "إعدادات ملفات تعريف الارتباط"

#### SEO meta — Landing page

- **Title EN:** "eFirm — Legal Practice Platform for the Levant"
- **Title AR:** "إي فيرم — منصة إدارة مكاتب المحاماة في بلاد الشام"
- **Description EN:** "Run your law firm on one workspace: matters, documents, billing, and Arabic-native AI. PDPL-compliant. From $20/seat/month."
- **Description AR:** "أدِر مكتبك على منصة واحدة: ملفات القضايا والمستندات والفواتير وذكاء اصطناعي يفهم العربية. متوافق مع قانون حماية البيانات الشخصية. ابتداءً من 20 دولاراً للمستخدم شهرياً."

> **Full content reference:** All other content strings (10 FAQ Q&A pairs, 6 feature pillar cards, 6 security cells, 3 problem cards, 4 callouts, demo form labels/placeholders/helpers/validation messages, thank-you page, legal-stub disclaimer banners, footer copy) are specified in the WRP chat output. Engineer agent transcribes directly into `marketing.php` localization files. Pest tests in `tests/Feature/Marketing/LandingCopyLimitsTest.php` enforce character limits (hero headline ≤ 80, sub-headline ≤ 160, SEO title ≤ 60, SEO description ≤ 160, notes ≤ 1000).

---

# 6. Edge Cases & Error Handling

Edge cases organised into 10 categories. Each row testable.

## Category 1 — Demo Request Form (P-2)

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| Empty required field | Blank full_name/firm_name/lawyer_count/email/country | FormRequest validation fails; HTTP 422 | Field red; localised error below |
| Invalid email format | `email = "abdullah@"` | `email:rfc,dns` rule rejects | Field red; "Please enter a valid email address." / "يرجى إدخال بريد إلكتروني صالح." |
| Invalid phone format | Contains chars outside `[+0-9 \-()]` | Regex fails | Field red; localised helper as error |
| Exceeding char limits | full_name > 120 / firm_name > 200 / notes > 1000 | `max` rule fails | "{Field} must not exceed {N} characters." |
| Duplicate within 1 hour | Same email submitted twice in 60min | Service queries prior row; HTTP 429 | Rate-limit toast at top of form; form remains filled |
| Rate limit (IP) | 6th submission from same IP in 60min | `throttle:5,60` blocks; HTTP 429 | Full-page 429 with mailto fallback |
| Honeypot non-empty | Bot fills `company_website` | `size:0` rule fails; HTTP 422; row NOT inserted | Generic validation message; no honeypot disclosure |
| Linear API timeout | Job times out (60s default) | Job marked failed; retries 3× backoff 30/60/120s; row remains inserted; email still dispatches | None — visitor on thank-you page; log channel `linear-integration` |
| Linear auth error (401/403) | API key invalid | Job fails immediately, no retries; level=error log | None for visitor; manual reconciliation |
| Email queue down | Worker stopped | Job remains queued; visitor sees thank-you; mail dispatches on resume | Normal success flow |
| Database write failure | INSERT fails | HTTP 500; exception logged with stack trace; transaction rolls back | Toast; form filled; visitor can retry |
| Offline submission | No network at submit | Browser-native error | No client-side offline queue v1 |
| JavaScript disabled | Classic POST | Form submits; controller responds with full-page redirect | Works identically to JS path |
| Missing CSRF | Session expired | `VerifyCsrfToken` returns HTTP 419 | Redirect to form; toast "Session expired" / "انتهت الجلسة" |

## Category 2 — Language / Locale (US-002)

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| First visit, no cookie, Arabic header | `Accept-Language: ar-JO` | Middleware → 302 redirect to `/ar` | Browser nav to `/ar`; RTL renders |
| First visit, no cookie, English header | `Accept-Language: en-US` | No `ar` match; English renders | LTR English |
| Visit `/ar` with existing `en` cookie | Direct URL nav | URL takes precedence; cookie updates to `ar` | RTL renders; cookie = ar |
| Toggle mid-page | Click "العربية" | 302 redirect to `/ar` with cookie set | Full reload to RTL; scroll lost (acceptable v1) |
| Cookie persistence | 6mo-old `ar` cookie | 365-day TTL; redirect to `/ar` | RTL on load |
| Cookie tampered | Cookie value = `fr` | Treated as unset; re-detect from header | Detected locale loads; cookie overwritten |
| Translation key missing | New key only in EN | `__()` falls back to EN; log warning channel `marketing-i18n` | Visitor sees EN string in AR page (log-detectable) |
| Malformed UTF-8 in lang file | Encoding error | Garbled chars render | Manual QA + Pest UTF-8 validity test |
| Mixed-content paragraph | AR with embedded brand EN | `dir="auto"` on container | Correct bidi rendering |

## Category 3 — Cookie Consent (US-005, API-002)

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| First visit, no cookie | Any page load | Banner renders; analytics NOT loaded | Bottom overlay visible above fold |
| Click "Accept All" | Button click | Cookie set all-true; API-002 records; GA4 loads next tick | Banner fades out 200ms (instant if reduced-motion) |
| Click "Reject Non-Essential" | Button click | Cookie set essential-only; API-002 records; GA4 NOT loaded | Banner dismisses |
| Click "Customise" + Save | Modal flow | Cookie reflects toggles; API-002 records with `source=customise` | Modal closes; banner dismisses |
| Re-consent via footer | "Cookie Settings" click | Modal reopens with current prefs | Modal opens |
| Cookie write fails | Safari ITP / quota | Banner persists; console warning; analytics NOT loaded | Banner remains |
| API-002 fails (network) | POST to `/cookie-consent` fails | Client retries once after 5s; on 2nd failure, log to console | UX uninterrupted — cookie state is source of truth |
| Visitor opts in then out | Subsequent rejection | New cookie overwrites; `window['ga-disable-{GA4_ID}'] = true` on next page nav; new API-002 row | Banner dismisses; analytics disables |

## Category 4 — Landing Page Rendering (US-001, US-007, US-008)

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| Hero screenshot 404 | Missing image | `onerror` handler swaps to CSS placeholder (slate-100 + SVG icon, 3:2 aspect ratio) | No broken-image icon |
| Slow image on 3G | LCP > 5s | LQIP blur-up; aspect-ratio reserved | Blurred → sharp; CLS = 0 |
| JS disabled | User has JS off | All sections server-render; FAQ uses `<details>/<summary>` fallback; smooth-scroll → instant | Functional, less polished |
| Reduced motion | `prefers-reduced-motion: reduce` | CSS rule disables transitions/animations > 200ms | Instant state changes |
| 200% text zoom | Browser zoom | Reflows up to 320px effective width (WCAG 1.4.10) | No horizontal scroll except data tables |
| IE11 / pre-2020 browser | Outdated UA | Static unsupported-browser notice above hero | "For best experience, please use a modern browser." / "للحصول على أفضل تجربة..." |
| Crawler with no JS | Googlebot | All meta + JSON-LD server-side; Lighthouse SEO ≥ 95 | n/a |
| Founding badge config OFF | `config('marketing.founding_firm_badge_enabled') = false` | Badge does not render | Tier card otherwise identical |
| Empty FAQ items | Array empty | `@if` hides section | Section absent; rest flows correctly |
| Traffic spike (1000 concurrent) | Press hit | Laravel `response_cache` middleware, 5min TTL | LCP target met |

## Category 5 — Pricing Table (US-003)

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| Click Starter CTA | Click | → `/register?utm_source=pricing_table&plan=starter` | Browser nav |
| Click Pro CTA | Click | `plan=pro` | Same |
| Click Enterprise CTA | Click | `plan=enterprise` | Same |
| Mobile feature matrix overflow | Table > 320px viewport | First column sticky via `position:sticky` | Horizontal scroll; feature names always visible |
| JOD line on EN locale | Visitor in EN | `@if (app()->getLocale() === 'ar')` suppresses | Only USD price |
| FX drift | Hardcoded rates in config | No auto-update; manual config change | Footnote: "JOD equivalents updated {date}" / "تُحدَّث المعادلات بالدينار بتاريخ {date}" |

## Category 6 — SEO and Crawlability (US-007)

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| Crawler GET `/sitemap.xml` | Bot request | `SeoController@sitemap` returns XML 200, `Content-Type: application/xml` | All 16 public routes with lastmod, changefreq, priority |
| Crawler GET `/robots.txt` | Bot request | Plain text 200 | `User-agent: *\nAllow: /\nDisallow: /app/\nSitemap: https://efirm.io/sitemap.xml` |
| hreflang validation | Both pages crawled | Each declares self + alternate + x-default | Search engines surface correct locale |
| OG image missing | `/img/og-image.jpg` 404 | Fallback at `/img/og-fallback.png` | Social shares show fallback |
| JSON-LD validation | Google Rich Results Test | Valid `LegalService` schema | No errors |
| Canonical URL conflict | `/index.html` legacy | 301 → `/` | Browser nav to canonical |

## Category 7 — Accessibility (US-008)

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| Screen reader on landing | NVDA/VO/TalkBack | Landmarks announced; heading hierarchy navigable; all interactive elements named | Logical reading order |
| Keyboard nav | Tab key only | Visual reading order; visible focus indicator ≥ 3:1 | Focus ring everywhere |
| Skip-to-content | First Tab from page | Skip link visible top-left (top-right RTL); Enter → `<main>` | Bypasses header nav |
| Form field without label | Guard | Pest asserts every `<input>` has associated `<label>` | CI build fails if violated |
| Insufficient contrast | < 4.5:1 | axe-core CI catches | Build fails on CI |
| Touch target < 44x44 | Mobile QA | Manual catch | Re-size before launch |
| Image without alt | New image added | axe-core flags; Pest browser test fails | Build fails on CI |
| RTL screen reader | VO on AR page | Reading order = DOM order (RTL-respecting); all aria localised | Same a11y quality as LTR |
| Reduced motion | Media query | All transitions/animations → 0.01ms | Instant state changes |

## Category 8 — Deployment, Caching, Ops

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| Cache busting on deploy | New deploy | Vite content-hashed filenames | No stale cache |
| HTTPS redirect | `http://` | Nginx 301 → `https://` | HTTPS |
| Naked vs www | `www.efirm.io` | 301 → apex `efirm.io` | Apex |
| Subpath `/app` no auth | Unauth visitor | Filament's normal auth redirect | Filament login |
| 404 unknown route | `/marketing` | Custom 404 view in current locale | "Return to Home" link |
| 500 on landing | Exception in `LandingController@show` | Custom 500 view; exception logged | demo@efirm.io contact |
| Maintenance mode | `php artisan down` | Custom maintenance view localised | "We'll be back shortly" / "سنعود قريباً" |
| MySQL down | Connection lost | Cookie endpoint logs to file as fallback; landing renders (static) | Static unaffected; demo form returns 500 |
| Redis down | Queue conn lost | Jobs fallback to DB driver | Visitor sees thank-you; jobs run on recovery |

## Category 9 — Legal & Compliance Edges

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| EU visitor (GDPR) | IP geolocated EU | Same banner satisfies GDPR (PDPL Art 13 more stringent in some respects) | Same banner |
| PDPL Art 11 deletion request | Email to legal@efirm.io | Out of v1 scope — manual deletion documented | Footer email contact |
| Stub legal cited in court | Edge but real | Persistent disclaimer banner is the mitigation | Banner is the legal cover |
| AI Disclaimer regulatory update | Bar issues new guidance | Update `resources/markdown/legal/ai-disclaimer-{locale}.md`; redeploy | Static stub, manually updatable |

## Category 10 — Cross-cutting Boundaries

| Scenario | Trigger | Expected | UI Response |
|---|---|---|---|
| 320px viewport (iPhone SE 1) | Smallest target | All text wraps; tap targets ≥ 44x44 | Usable down to 320px |
| 3840px (4K) viewport | Largest | `max-w-7xl` centres; backgrounds full-bleed | Centred reading column |
| Long firm names (200 chars) | Submitter input | FormRequest accepts; UI `overflow-x ellipsis` | Field shows beginning + ellipsis; full text submitted |
| Special chars (O'Brien, AR, emoji) | Submitter | All accepted; UTF-8 columns; Blade escapes by default | Stored and displayed verbatim |
| Print stylesheet | Ctrl+P | Hides nav/footer/CTAs; shows main content high contrast | Print-friendly |

---

# 7. Sign-Off Log

## Package metadata

| Field | Value |
|---|---|
| Surge ID | SURGE-LP-01 |
| Surge Name | eFirm Public Landing Page |
| Package Version | 1.0 |
| Date Assembled | 2026-06-24 |
| Product Designer | Abdullah Mohammed (Founder) |
| Methodology | AODC |
| Stack | Laravel 13.16, Filament v5.6, PHP 8.3, MySQL 8, Redis, Cloudways/DigitalOcean Frankfurt FRA1, Tailwind CSS, Blade |
| Estimated Effort | 14–18 hours of Wave work across 6 Waves; 2–3 days with Claude Code |

## Stakeholder approvals

| Stakeholder | Role | Date | Method | Status | Notes |
|---|---|---|---|---|---|
| Abdullah Mohammed | Founder / Product Designer | 2026-06-24 | AODC chat session | **Approved** | Locked all decisions in Clarifying Gates Round 1 + Round 2; reviewed all 7 PAC sections inline |
| Khaldoun Khater | Practitioner Advisor | PENDING | Monday café walkthrough OR post-walkthrough email | **Pending** | Must review rendered landing page on staging URL ≥ 48h before public launch. Sign-off in `docs/validation/02_advisor_meeting_log.md`. Specific check: procedural-accuracy claims (S-8) and Arabic legal-clause demo (S-7) are practitioner-accurate. |
| Tech-focused Lawyer (TBC) | External Legal Reviewer | DEFERRED | Email post-launch | **Not yet** | Pending Khaldoun's referral. Drafts final ToS, Privacy, DPA, AI Disclaimer to replace stubs. Launch with stubs + persistent disclaimer banners (decision R-9). |

## Decisions log (consolidated from Clarifying Gates R1 + R2)

| Ref | Decision | Rationale |
|---|---|---|
| LP-D-01 | Hybrid CTA: primary "Start Free Trial", secondary "Book a Demo" | Maximises conversion across 5 JTBD segments |
| LP-D-02 | English-first LTR default, Arabic RTL toggle | Investor/press/partner readability; AR experience via `/ar` |
| LP-D-03 | Public launch posture — trial open, full pricing visible | Aligns with existing Stripe + 14-day trial lifecycle |
| LP-D-04 | Full three-tier pricing visible ($20/$25/$30) | Strongest economic argument vs competitor AI-only $25 Starter |
| LP-D-05 | Competitor never named | Avoids legal risk; positioning earns rather than provokes |
| LP-D-06 | Apex domain serves landing at `efirm.io/`, product at `efirm.io/app` | Consistent with R-7; single TLS, single deploy |
| LP-D-07 (R-1) | GA4 + full PDPL consent banner with granular toggles | Best analytics power; PDPL-compliant via consent gating |
| LP-D-08 (R-2) | Email notification + auto-Linear ticket in "Leads" project | Captures leads in existing workflow tool |
| LP-D-09 (R-3) | "First 50 founding firms: 30% off Pro for year 1" badge | Urgency hook; founding-firm authenticity |
| LP-D-10 (R-4) | Generic "Built with practicing lawyers in Amman" copy band; no quotes | Honest positioning without fabricated testimonials |
| LP-D-11 (R-5) | Real Filament screens with light polish (frame, shadow, AR content) | Authentic representation; minimal design-pass cost |
| LP-D-12 (R-6) | Static screenshot only for v1 | Defer motion/video to v2 |
| LP-D-13 (R-7) | `efirm.io/` apex (covered in LP-D-06) | Duplicate of LP-D-06 |
| LP-D-14 (R-8) | All three keyword clusters (practice-mgmt + AI + PDPL/procedural) | Broad SEO net |
| LP-D-15 (R-9) | Generic SaaS template ToS/Privacy/DPA/AI Disclaimer now; replace post Khaldoun's lawyer referral | Speed-to-launch; mitigated by visible disclaimer banner |
| LP-D-16 (R-10) | Maximum security transparency: PDPL + Frankfurt + audit + trust ledger + ISO 27001 2027 roadmap + Cloudways/DO SOC 2 II infra ref | Truthful, defensible, doesn't overclaim |

## Known risks at handoff

| Risk ID | Description | Severity | Mitigation | Owner |
|---|---|---|---|---|
| LP-R-01 | Legal stub pages may not survive scrutiny if cited in dispute | **High** | Persistent visible disclaimer banner on every legal page; replacement pending tech-lawyer | Abdullah |
| LP-R-02 | Founding-firm badge has no live counter; may show after 50 sign-ups | Low | Manual config toggle `config('marketing.founding_firm_badge_enabled')` | Abdullah |
| LP-R-03 | Khaldoun's review on procedural-accuracy (S-8) not yet captured | Medium | Pre-launch gate: staging review ≥ 48h before public launch | Khaldoun + Abdullah |
| LP-R-04 | Arabic legal clause demo (S-7) sample drafted by Abdullah; needs practitioner sign-off | Medium | Same as LP-R-03 | Khaldoun + Abdullah |
| LP-R-05 | JOD-equivalent line uses hardcoded rates that drift with FX | Low | Footnote with last-updated date; quarterly manual review | Abdullah |
| LP-R-06 | GA4 banner must satisfy PDPL Art 13 + GDPR if EU traffic | Medium | Banner copy reviewed against both regimes; granular toggles | Tech-lawyer |
| LP-R-07 | No pilot logos/testimonials yet; thin vs competitor's "11,000+ firms" claim | Medium | LP-D-10 chosen explicitly to avoid fabricated proof | Abdullah |
| LP-R-08 | Linear API key rotation could break demo-request capture silently | Low | Retries 3×; failure logged channel `linear-integration`; manual reconciliation from `demo_requests` table | Engineer |
| LP-R-09 | Stub legal pages flagged by LP-D-15; visitor losing trust on careful read | Medium | Banner is clear and dated; visibility > concealment | Abdullah |

## Wave breakdown

| Wave | Scope | Est. | Depends |
|---|---|---|---|
| **W-1** | Routes, middleware, locale handling, base layout (`resources/views/public/layouts/marketing.blade.php`); empty page skeleton at `/` with header + footer; English-only stub | 3h | none |
| **W-2** | Sections S-1 through S-6 (Header, Hero, Trust Strip, Problem, Single Workspace, Feature Pillars) as Blade components with full English content | 3h | W-1 |
| **W-3** | Sections S-7 through S-13 (Arabic AI Demo, Procedural Accuracy, Pricing, Security, FAQ, Final CTA, Footer); founding-firm badge config flag | 3h | W-2 |
| **W-4** | Arabic localization: complete `resources/lang/ar/marketing.php`; Tailwind RTL utilities applied; `/ar` route group; locale toggle; cookie persistence; 5-device RTL visual check | 3h | W-3 |
| **W-5** | Demo request form (P-2), thank-you page (P-3), API-001 + service + Linear job + email mailables + migration + duplicate guard + honeypot + tests | 3h | W-1 |
| **W-6** | Cookie consent banner (US-005), API-002 + migration + tests; SEO meta + sitemap + robots + JSON-LD; accessibility audit pass (axe-core CI); legal stub pages (P-4 through P-7); polish + Lighthouse ≥ 95 verification | 3h | W-1..W-5 |

Total: ~18 hours of Wave work. With Claude Code, expect 8–12 hours of clock time across 2–3 working days. W-5 can run in parallel with W-2/W-3/W-4.

## Handoff checklist

- [x] Intent Definition complete and stakeholder-approved (Abdullah)
- [x] User Stories specified with GIVEN/WHEN/THEN
- [x] Wireframes Documentation written for all 7 pages
- [x] API Contracts for 2 endpoints with OpenAPI spec path referenced
- [x] Content Specification key blocks captured + full reference deferred to lang files
- [x] Edge Cases documented across 10 categories
- [x] Sign-Off Log captures all decisions and known risks
- [x] Wave breakdown suggested for Engineer agent / Claude Code planning
- [ ] Khaldoun's pre-launch staging review — SCHEDULED, not yet executed
- [ ] OpenAPI `spec.yaml` updated by Engineer during implementation
- [ ] CLAUDE.md updated with marketing-page conventions (see `CLAUDE.md-addendum-LP-01.md`)
- [ ] Linear "Leads" project created with `LINEAR_API_KEY`, `LINEAR_TEAM_ID`, `LINEAR_LEADS_PROJECT_ID` in `.env`
- [ ] GA4 property created with `anonymize_ip` enforced; measurement ID in `.env`

## Post-launch metrics tracking (M-1 through M-10)

| Metric | Target | Tool | Owner |
|---|---|---|---|
| M-1 | ≥ 50 sessions/wk by wk 4 | GA4 | Abdullah |
| M-2 | ≥ 5 trial signups / 30 days | GA4 UTM + Stripe webhooks | Abdullah |
| M-3 | ≥ 3 demo requests / 30 days | `demo_requests` table + Linear "Leads" | Abdullah |
| M-4 | LCP < 2.5s on 4G | PageSpeed Insights weekly | Engineer |
| M-5 | CLS < 0.10 | PageSpeed Insights | Engineer |
| M-6 | Lighthouse SEO ≥ 95 | Lighthouse CI on every deploy | Engineer |
| M-7 | Consent banner 100% first-visit | Pest browser test + manual | Engineer |
| M-8 | RTL zero regressions on 5 devices | Manual QA on launch + bi-weekly | Abdullah |
| M-9 | Zero JS console errors, zero broken links | Weekly automated link checker | Engineer |
| M-10 | ≥ 1 advisor positive feedback | `docs/validation/02_advisor_meeting_log.md` | Abdullah |

---

# 8. Engineer Handoff Appendix

## For the AODC Software Engineer agent

When you (the SE agent) consume this WRP to produce a Tech Task Package (TTP):

1. **Gate check first:** Confirm no Surge dependencies block. This Surge depends on nothing in the current SURGE-FIX-01, SURGE-FIX-02, or SURGE-14 tracks — they can run in parallel.
2. **Khaldoun's pre-launch gate (LP-R-03/LP-R-04):** Not a TTP blocker but a deployment blocker. Schedule for after W-5 ships to staging.
3. **CLAUDE.md addendum:** Apply the proposed conventions from `CLAUDE.md-addendum-LP-01.md` before generating TTPs that touch the marketing namespace.
4. **OpenAPI diff:** Section 9 of your TTP must include the exact YAML diff for `openapi/spec.yaml` adding the two public endpoints under tag `Public`.
5. **Test inventory:** Reference Section 4 here for the 7 test files (5 for demo request flow, 2 for cookie consent).
6. **Architectural violations to verify:** This Surge is a marketing/public surface and does not touch litigation, court records, trust accounting, or any of the CLAUDE.md non-negotiables. Pass-through.

## For Claude Code (direct path)

Use the companion file `SURGE-LP-01-claude-code-runbook.md` which contains 6 Wave-by-Wave execution prompts ready to paste.

## Suggested filename when committed to repo

```
docs/surges/SURGE-LP-01-landing-page.md
```

Sibling files:
- `docs/surges/SURGE-LP-01-claude-code-runbook.md`
- `docs/surges/SURGE-LP-01-CLAUDE.md-addendum.md`

---

**End of Wave-Ready Package SURGE-LP-01 v1.0**
