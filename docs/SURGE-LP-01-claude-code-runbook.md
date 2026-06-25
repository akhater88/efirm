# SURGE-LP-01 — Claude Code Execution Runbook

**Companion to:** `SURGE-LP-01-landing-page.md` (Wave-Ready Package v1.0)
**Path to consume:** This file is intended to be run inside Claude Code from the eFirm Laravel repository root.
**Mode:** Sequential Wave execution. Wave 1 must complete and verify before Wave 2 starts.

---

## How to use this runbook

### Pre-flight (one-time, before Wave 1)

1. **Confirm you are in the eFirm repository root.** Run `pwd` and verify the path matches your local clone of the eFirm Laravel project.
2. **Confirm the WRP is accessible.** Place `SURGE-LP-01-landing-page.md` at `docs/surges/SURGE-LP-01-landing-page.md` in the repo. Claude Code will reference it during execution.
3. **Confirm CLAUDE.md is loaded.** Claude Code should have read `CLAUDE.md` v6 already. Verify it includes the marketing-page conventions from `SURGE-LP-01-CLAUDE.md-addendum.md`; if not, apply that addendum first.
4. **Confirm `.env` has the required variables.** Required for full execution but not for Waves 1–4:
   - `LINEAR_API_KEY=` (Linear personal API token for the workspace)
   - `LINEAR_TEAM_ID=` (Linear team UUID)
   - `LINEAR_LEADS_PROJECT_ID=` (Linear "Leads" project UUID)
   - `GA4_MEASUREMENT_ID=` (Google Analytics 4 measurement ID, format `G-XXXXXXXXXX`)
   - `MAIL_FROM_ADDRESS=hello@efirm.io`
   - `MAIL_LEAD_NOTIFICATION=abdullah@efirm.io`
5. **Branch off main:** `git checkout -b feat/surge-lp-01-landing-page`
6. **Run the full test suite to confirm a clean baseline:** `php artisan test --parallel`. All tests should pass before proceeding.

### Execution loop per Wave

For each Wave below:

1. **Paste the Wave prompt** into Claude Code.
2. Claude Code will read the WRP, execute the Wave scope, and run the Wave's verification commands.
3. **Review the diff** before committing. Look for: file paths matching spec exactly, FormRequest validation rules verbatim, AR strings preserved with correct UTF-8 encoding, RTL utility classes in the correct components.
4. **Commit the Wave** as a single commit with the message format: `SURGE-LP-01 W-{N}: {scope summary}`.
5. **Run the verification block** at the end of each Wave. If any check fails, fix before proceeding to the next Wave.

### How to abort a Wave

If a Wave goes wrong: `git reset --hard HEAD` (assuming you committed the previous Wave). Then re-paste the Wave prompt with any corrections.

---

## Wave 1: Routes, middleware, locale handling, base layout

**Estimated time:** 30–45 minutes with Claude Code
**Depends on:** none
**Files created/modified:**
- `routes/web.php` (additions only)
- `routes/api.php` (additions only)
- `app/Http/Middleware/SetPublicLocale.php` (new)
- `app/Http/Kernel.php` (middleware alias)
- `app/Providers/RouteServiceProvider.php` (rate limiter)
- `app/Http/Controllers/Public/LandingController.php` (new)
- `app/Http/Controllers/Public/DemoRequestController.php` (new — `create`, `thankYou` only; `store` deferred to W-5)
- `app/Http/Controllers/Public/LegalController.php` (new)
- `app/Http/Controllers/Public/SeoController.php` (new)
- `resources/views/public/layouts/marketing.blade.php` (new)
- `resources/views/public/landing.blade.php` (new — empty section markers only)
- `resources/views/components/marketing/header.blade.php` (skeleton)
- `resources/views/components/marketing/footer.blade.php` (skeleton)
- `resources/lang/en/marketing.php` (skeleton with `meta`, `header`, `footer` keys only)
- `config/marketing.php` (new — founding-firm flag default = true)

### Paste this prompt into Claude Code

```
Execute Wave 1 of SURGE-LP-01 per the Wave-Ready Package at docs/surges/SURGE-LP-01-landing-page.md.

Scope:
- Routes, middleware, locale handling, base layout
- Empty section markers in the landing page
- English-only stub (no AR yet)

Acceptance:
1. GET / returns HTTP 200 with the marketing layout, an empty header, an empty footer, and a placeholder body announcing "SURGE-LP-01 — Wave 1 skeleton" so we can verify routing works.
2. GET /ar returns HTTP 200 with the same layout but <html dir="rtl" lang="ar">.
3. GET /demo-request returns HTTP 200 (empty form placeholder is fine).
4. GET /demo-request/thank-you returns HTTP 200.
5. GET /terms, /privacy, /dpa, /ai-disclaimer all return HTTP 200 with the disclaimer banner.
6. GET /sitemap.xml returns valid XML with at least the 8 landing-page routes (EN + AR pairs).
7. GET /robots.txt returns the spec's exact text.
8. GET /app/login still resolves to Filament login (no regression).
9. A visitor with cookie efirm_locale=ar gets redirected from / to /ar.
10. A visitor with no cookie and Accept-Language: ar-JO gets redirected from / to /ar.
11. The cookie efirm_locale is set with TTL 365 days, SameSite=Lax, Secure=true in production.

Constraints:
- Use the EXACT file paths from the WRP. Do not invent new paths.
- Do not start on the demo-request POST endpoint, content sections (S-1 to S-13 are not in Wave 1), or AR translations beyond the locale switch infrastructure.
- Use Blade components for header and footer; landing.blade.php composes them.
- Do not modify CLAUDE.md.

Verification after implementation:
- Run: php artisan route:list --columns=Method,URI,Name | grep -E "(public|landing|demo|legal|sitemap|robots)"
- Run: php artisan test --filter=PublicLocaleMiddleware
- Run: curl -I http://efirm.test/ should return 200 with Set-Cookie: efirm_locale=en
- Run: curl -I -H "Accept-Language: ar-JO" http://efirm.test/ should return 302 with Location: /ar

Commit message: SURGE-LP-01 W-1: routes, middleware, locale, base layout
```

### Wave 1 verification

After Claude Code reports completion, run:

```bash
php artisan route:list --columns=Method,URI,Name | grep -i "public\|landing\|demo\|legal\|sitemap\|robots"
php artisan test --filter=Public
curl -sI http://efirm.test/ | head -20
curl -sI -H "Accept-Language: ar-JO" http://efirm.test/ | head -20
curl -s http://efirm.test/sitemap.xml | head -30
curl -s http://efirm.test/robots.txt
```

Expected: all routes resolve, locale redirect works, sitemap and robots are well-formed.

---

## Wave 2: Sections S-1 through S-6 (English content)

**Estimated time:** 45–60 minutes with Claude Code
**Depends on:** W-1
**Files created/modified:**
- `resources/views/components/marketing/header.blade.php` (full)
- `resources/views/components/marketing/hero.blade.php` (new)
- `resources/views/components/marketing/trust-strip.blade.php` (new)
- `resources/views/components/marketing/problem.blade.php` (new)
- `resources/views/components/marketing/single-workspace.blade.php` (new)
- `resources/views/components/marketing/feature-pillars.blade.php` (new)
- `resources/views/public/landing.blade.php` (composes S-1 through S-6)
- `resources/lang/en/marketing.php` (full content for these sections)
- `public/img/hero-screenshot.webp` (real polished Filament screen — placeholder OK if not yet available; CSS fallback handles 404)
- Tailwind config: ensure font stack `'Inter'` is preconnected in the layout `<head>`
- `tests/Feature/Marketing/LandingCopyLimitsTest.php` (new) — asserts hero headline ≤ 80 chars, sub-headline ≤ 160 chars

### Paste this prompt into Claude Code

```
Execute Wave 2 of SURGE-LP-01.

Scope: Build sections S-1 (Header), S-2 (Hero), S-3 (Trust Strip), S-4 (Problem), S-5 (Single Workspace), S-6 (Feature Pillars) per the WRP at docs/surges/SURGE-LP-01-landing-page.md sections 3 and 5.

For each section, follow the wireframe spec (Section 3) and use the exact English content (Section 5 — pull the precise strings from the WRP; do not paraphrase). Do not touch AR strings in this Wave — that is W-4.

Constraints:
- All text comes from resources/lang/en/marketing.php using __() helper.
- Each section is its own Blade component in resources/views/components/marketing/.
- Responsive breakpoints per WRP Section 3 "Responsive Notes" exactly.
- Hero product screenshot is /img/hero-screenshot.webp; if missing, CSS fallback renders a slate-100 block with abstract document SVG, aspect-ratio 3/2.
- Headline limit: 80 chars. Sub-headline limit: 160 chars. Enforce via Pest test in tests/Feature/Marketing/LandingCopyLimitsTest.php.
- Use Tailwind only — no inline styles, no custom CSS files except a minimal global.css for the prefers-reduced-motion rule and font preconnects.
- Primary CTA navigates to /register?utm_source=landing_hero
- Secondary CTA navigates to /demo-request?utm_source=landing_hero_demo

Verification:
- Run: php artisan test --filter=LandingCopyLimitsTest
- Visit / in browser. Verify S-1 through S-6 render with proper visual rhythm (96/64/48 padding-y).
- View source: confirm no hard-coded English strings outside __() calls.
- Verify the hero CTA buttons carry the correct UTM query params on hover (DevTools).

Commit message: SURGE-LP-01 W-2: S-1 through S-6 (English content)
```

### Wave 2 verification

```bash
php artisan test --filter=LandingCopy
# Manual browser checks:
# - http://efirm.test/ shows hero, trust strip, problem, solution, feature pillars in order
# - Primary CTA href = /register?utm_source=landing_hero
# - Hero headline visible, no broken-image icon if /img/hero-screenshot.webp is missing
```

---

## Wave 3: Sections S-7 through S-13 (English content)

**Estimated time:** 45–60 minutes
**Depends on:** W-2
**Files created/modified:**
- `resources/views/components/marketing/arabic-ai-demo.blade.php` (new)
- `resources/views/components/marketing/procedural-accuracy.blade.php` (new)
- `resources/views/components/marketing/pricing.blade.php` (new)
- `resources/views/components/marketing/security.blade.php` (new)
- `resources/views/components/marketing/faq.blade.php` (new)
- `resources/views/components/marketing/final-cta.blade.php` (new)
- `resources/views/components/marketing/footer.blade.php` (full)
- `resources/views/public/landing.blade.php` (composes S-7 through S-13)
- `resources/lang/en/marketing.php` (extended with pricing, FAQ, security, footer)
- `config/marketing.php` — pricing tiers + `founding_firm_badge_enabled` + JOD exchange rates

### Paste this prompt into Claude Code

```
Execute Wave 3 of SURGE-LP-01.

Scope: Build sections S-7 (Arabic AI Demo), S-8 (Jordanian Procedural Accuracy), S-9 (Pricing), S-10 (Security), S-11 (FAQ), S-12 (Final CTA), S-13 (Footer) per the WRP sections 3 and 5.

Key details:
- S-7 (Arabic AI Demo): The static Arabic prompt and Arabic legal output are reproduced VERBATIM from WRP Section 5 — do NOT paraphrase, translate, or alter the Arabic text. These strings render even in the English locale (this section shows the AI in action).
- S-9 (Pricing): Reads tier data from config/marketing.php (Starter $20, Pro $25, Enterprise $30). Founding-firm badge on Pro tier is controlled by config('marketing.founding_firm_badge_enabled') — default true. JOD-equivalent line is rendered only when app()->getLocale() === 'ar' (in this Wave we set up the conditional even though AR is not yet active).
- S-11 (FAQ): 10 questions per WRP Section 5. Each rendered as a <button aria-expanded="false" aria-controls="..."> element with <div role="region" aria-labelledby="..."> for the answer. Multi-open on desktop ≥ 1024px (CSS class .faq-multi-open); single-open on mobile ≤ 640px (use Alpine.js or vanilla JS minimal handler). Caret rotates 180deg via CSS transform with 200ms transition (disabled under prefers-reduced-motion).
- S-13 (Footer): All four legal links (Terms, Privacy, DPA, AI Disclaimer) plus "Cookie Settings" trigger that opens the consent modal (modal itself is W-6).

Constraints:
- Use the EXACT English content from WRP Section 5.
- All Arabic strings in S-7 are static content — they render in both locales.
- Pricing CTAs: /register?utm_source=pricing_table&plan={starter|pro|enterprise}.
- Smooth-scroll behaviour on nav links is CSS-only: html { scroll-behavior: smooth; } — disabled by prefers-reduced-motion.

Verification:
- Visit / and scroll through all 13 sections. Verify visual rhythm, RTL-pending hooks (no broken layout in EN yet).
- Click each pricing CTA — verify the URL includes correct plan param.
- Expand FAQ items: on desktop multiple can be open simultaneously; on mobile (Chrome DevTools mobile emulation) only one stays open.
- View page source: no English strings outside __() calls (except the S-7 Arabic content, which is intentionally static).
- Lighthouse: run a quick audit. Expect Performance ≥ 90, Accessibility ≥ 95 even at this stage.

Commit message: SURGE-LP-01 W-3: S-7 through S-13 (English content)
```

### Wave 3 verification

```bash
# Manual browser checks across all 13 sections
# Lighthouse audit: expect Perf ≥ 90, A11y ≥ 95
php artisan test --filter=Landing
```

---

## Wave 4: Arabic localization & RTL polish

**Estimated time:** 60–75 minutes
**Depends on:** W-3
**Files created/modified:**
- `resources/lang/ar/marketing.php` (full — every key from EN file populated)
- All marketing Blade components — add RTL utility classes (`rtl:flex-row-reverse`, `rtl:text-right`, etc.)
- `resources/views/public/layouts/marketing.blade.php` — IBM Plex Sans Arabic webfont preconnect; `dir` and `lang` attributes from middleware
- `tailwind.config.js` — install/configure `tailwindcss-rtl` plugin OR define manual RTL utilities
- `tests/Feature/Marketing/ArabicLocalizationTest.php` (new) — asserts every EN key has an AR counterpart

### Paste this prompt into Claude Code

```
Execute Wave 4 of SURGE-LP-01.

Scope: Full Arabic localization and RTL polish.

Files to populate:
- resources/lang/ar/marketing.php — every key from resources/lang/en/marketing.php must have an Arabic value. Use the EXACT Arabic strings from WRP Section 5 (do not retranslate; they are advisor-approved-ready and use Levant legal vocabulary).
- All Blade components in resources/views/components/marketing/* must respect RTL via Tailwind utility classes (rtl:* prefix) per the RTL table in WRP Section 3.

RTL behaviour rules (from WRP):
- Header: logo → right, nav links → left in RTL via rtl:flex-row-reverse
- Hero: text zone → right, visual → left
- Card grids: rtl:flex-row-reverse to reverse card order
- Form labels: rtl:text-right
- Icons that imply direction (carets, arrows, chevrons): add rtl:scale-x-[-1]
- Non-directional icons (search, mail, phone): no mirroring
- Pricing card order: Enterprise, Pro, Starter in RTL via rtl:flex-row-reverse
- Mobile drawer: slides from right in RTL
- Numbers remain Western Arabic (0-9) in both locales

Font stack:
- English: 'Inter', system-ui, sans-serif
- Arabic: 'IBM Plex Sans Arabic', 'Tajawal', system-ui, sans-serif
- Load both via <link rel="preconnect" href="https://fonts.googleapis.com"> in the layout <head>; pick weights 400/500/600/700.

Constraints:
- Every user-facing string MUST come from a lang file. No raw strings in Blade.
- The S-7 (Arabic AI Demo) Arabic prompt and output are static content and remain identical in both locales.
- Locale toggle in header: shows "العربية" when on EN page, "English" when on AR page. Click triggers HTTP 302 redirect to the alternate locale URL, setting efirm_locale cookie for 365 days.
- Pricing JOD-equivalent line appears ONLY in AR locale (Blade @if (app()->getLocale() === 'ar')).

Tests to add:
- tests/Feature/Marketing/ArabicLocalizationTest.php:
  - Asserts every key in resources/lang/en/marketing.php has a non-null AR counterpart in resources/lang/ar/marketing.php (recursive deep comparison).
  - Asserts no raw bracketed key strings appear in rendered /ar HTML (e.g., no [marketing.hero.headline]).
  - Asserts <html> attributes are lang="ar" dir="rtl" on /ar.
  - Asserts the locale cookie is set on first locale toggle.

Verification:
- php artisan test --filter=ArabicLocalization
- Visit /ar in browser:
  - Verify dir="rtl" on <html>
  - Verify font is IBM Plex Sans Arabic
  - Verify all sections render correctly: pricing cards reversed, footer columns reversed, header logo on right
  - Toggle to / via "English" link — verify cookie persists
- Manual RTL visual check on 5 devices listed in M-8 (iPhone 12 Safari, Galaxy S22 Chrome, iPad 10 Safari, MacBook Pro Chrome 1440px, Windows Firefox 1920px). Document any visual regressions in a comment on the PR.

Commit message: SURGE-LP-01 W-4: Arabic localization & RTL polish
```

### Wave 4 verification

```bash
php artisan test --filter=ArabicLocalization
# Manual RTL visual QA on 5 reference devices
# Spot-check that no English strings leak into /ar
```

---

## Wave 5: Demo request flow (form + API + email + Linear)

**Estimated time:** 60–75 minutes
**Depends on:** W-1 (NOT W-4 — can run in parallel with W-2/W-3/W-4)
**Files created/modified:**
- `database/migrations/2026_06_24_000001_create_demo_requests_table.php` (new)
- `app/Models/DemoRequest.php` (new)
- `app/Http/Requests/Public/StoreDemoRequestRequest.php` (new)
- `app/Http/Controllers/Public/DemoRequestController.php` (extended with `store`)
- `app/Http/Controllers/Api/V1/Public/DemoRequestController.php` (new — API counterpart for JSON POST)
- `app/Services/DemoRequestService.php` (new)
- `app/Services/Integrations/LinearClient.php` (new)
- `app/Jobs/CreateLinearLeadTicketJob.php` (new)
- `app/Mail/DemoRequestNotificationMail.php` + view (new)
- `app/Mail/DemoRequestConfirmationMail.php` + bilingual view (new)
- `resources/views/public/demo-request/create.blade.php` (new)
- `resources/views/public/demo-request/thank-you.blade.php` (new)
- `config/services.php` — Linear config
- `tests/Feature/Api/V1/Public/DemoRequestApiTest.php` (new)
- `tests/Unit/Services/DemoRequestServiceTest.php` (new)
- `tests/Feature/Jobs/CreateLinearLeadTicketJobTest.php` (new)
- `tests/Feature/Mail/DemoRequestNotificationMailTest.php` (new)
- `tests/Feature/Mail/DemoRequestConfirmationMailTest.php` (new)
- `openapi/spec.yaml` (extended with `/v1/public/demo-requests` path)

### Paste this prompt into Claude Code

```
Execute Wave 5 of SURGE-LP-01.

Scope: Complete the demo request flow (form, API endpoint, FormRequest, service, Linear job, email mailables, migration, tests, OpenAPI update).

Reference: WRP Section 4 (API-001) is the canonical spec. Match exactly:
- Endpoint: POST /api/v1/public/demo-requests
- Middleware: throttle:5,60 (5 per IP per 60min) + VerifyCsrfToken
- FormRequest validation rules per WRP exactly:
  - full_name: required|string|max:120
  - firm_name: required|string|max:200
  - lawyer_count: required|in:1,2-4,5-10,11-25,26+
  - email: required|email:rfc,dns|max:254
  - phone: nullable|string|max:30|regex:/^[+0-9 \-()]+$/
  - country: required|in:JO,LB,PS,IQ,OTHER
  - notes: nullable|string|max:1000
  - locale: required|in:en,ar
  - honeypot: present|size:0 (rendered as hidden input name="company_website" with aria-hidden="true")

- Migration: exactly the schema in WRP Section 4 (UUID PK, 15 columns including utm_source, linear_ticket_id, linear_ticket_url, notification_sent_at)
- Duplicate guard: DemoRequestService::store() queries for existing row same email + created_at within 60 minutes; if found, return 429
- Linear integration via app/Services/Integrations/LinearClient.php using GraphQL mutation issueCreate; failure logs to channel linear-integration but does NOT block visitor flow
- Email: DemoRequestNotificationMail to MAIL_LEAD_NOTIFICATION (Abdullah); DemoRequestConfirmationMail to submitter in their locale (en or ar)

Form UI (resources/views/public/demo-request/create.blade.php):
- All labels, placeholders, helpers, validation messages from resources/lang/{en,ar}/marketing.php under demo_form.* keys
- Honeypot hidden input
- Submit button: text changes to "Submitting…" / "جاري الإرسال…" while pending
- On 422, re-render with field-specific errors below each invalid field
- On 429, render rate-limit toast at top of form, keep form filled
- Use Livewire 3.x or vanilla JS minimal (your choice — Livewire is already in stack)

Test files (Pest 4.7) — all 5 from WRP Section 4 must exist and pass:
- tests/Feature/Api/V1/Public/DemoRequestApiTest.php (happy path, all validation errors, rate limit, duplicate, honeypot, CSRF)
- tests/Unit/Services/DemoRequestServiceTest.php (service logic, duplicate guard)
- tests/Feature/Jobs/CreateLinearLeadTicketJobTest.php (mocked Linear client; success and failure paths)
- tests/Feature/Mail/DemoRequestNotificationMailTest.php (renders correctly, EN + AR variants)
- tests/Feature/Mail/DemoRequestConfirmationMailTest.php (same)

OpenAPI update:
- Add path /v1/public/demo-requests POST to openapi/spec.yaml
- Schema for request body, response, all error codes (400, 422, 429, 500)
- Tags: ["Public", "Marketing"]
- Security: [] (empty — no auth)

Verification:
- php artisan migrate (dev env): table demo_requests is created with all 15 columns
- php artisan test --filter=DemoRequest (all 5 test files pass)
- Manual: submit a happy-path form via browser → confirms email job dispatches (queue:listen running) and Linear job dispatches → thank-you page renders with firm_name interpolated
- Manual: submit with blank required → 422 with field errors localised
- Manual: submit twice from same email within 60min → 429
- Manual: submit with company_website filled → 422, no row inserted
- Manual: submit via curl without CSRF → 419

Commit message: SURGE-LP-01 W-5: demo request flow (form + API + email + Linear)
```

### Wave 5 verification

```bash
php artisan migrate:fresh --seed   # only in dev/test
php artisan test --filter=DemoRequest
php artisan queue:work --once       # process one queued job to verify
# Manual: submit form in browser, verify email + Linear ticket + thank-you page
```

---

## Wave 6: Cookie consent + SEO + accessibility + legal stubs + polish

**Estimated time:** 60–90 minutes
**Depends on:** W-1 through W-5
**Files created/modified:**
- `database/migrations/2026_06_24_000002_create_cookie_consent_records_table.php` (new)
- `app/Models/CookieConsentRecord.php` (new — append-only)
- `app/Http/Requests/Public/StoreCookieConsentRequest.php` (new)
- `app/Http/Controllers/Api/V1/Public/CookieConsentController.php` (new)
- `app/Services/CookieConsentService.php` (new)
- `resources/views/components/marketing/cookie-banner.blade.php` (new)
- `resources/js/cookie-consent.js` (new) — vanilla JS handler for cookie state + GA4 gating
- `app/Http/Controllers/Public/SeoController.php` (extended with sitemap + robots)
- `resources/views/public/legal/show.blade.php` (new — Markdown renderer)
- `resources/markdown/legal/{terms,privacy,dpa,ai-disclaimer}-{en,ar}.md` (8 stub files with disclaimer)
- `config/seo.php` (new — title/description per page)
- `tests/Feature/Api/V1/Public/CookieConsentApiTest.php` (new)
- `tests/Unit/Services/CookieConsentServiceTest.php` (new)
- `tests/Browser/Marketing/CookieConsentTest.php` (new — Pest Browser Plugin)
- `tests/Browser/Marketing/AccessibilityTest.php` (new — axe-core CI integration)
- `openapi/spec.yaml` (extended with `/v1/public/cookie-consent`)
- `.github/workflows/ci.yml` (add Lighthouse CI + axe-core steps)

### Paste this prompt into Claude Code

```
Execute Wave 6 of SURGE-LP-01 — the final Wave.

Scope:
1. Cookie consent banner + API-002 endpoint + database audit trail
2. SEO: sitemap, robots.txt, JSON-LD LegalService, OpenGraph, Twitter cards, hreflang
3. Accessibility audit pass (axe-core in CI, zero WCAG 2.1 AA violations)
4. Legal stub pages (P-4 through P-7) with persistent disclaimer banner
5. Polish: Lighthouse SEO ≥ 95 verification, LCP < 2.5s, CLS < 0.10

Cookie consent (US-005 + API-002):
- Banner component renders ONLY when no efirm_consent cookie is present (server-side render-time check)
- Three buttons: Accept All, Reject Non-Essential, Customise (opens modal)
- Customise modal: three toggles (Essential locked on; Analytics default off; Marketing default off) + Save Preferences button
- On any choice, set efirm_consent cookie (JSON-encoded, max 256 bytes, 365-day TTL, SameSite=Lax, Secure in prod) AND POST to /api/v1/public/cookie-consent for the audit record
- API-002 implementation per WRP Section 4 exactly: append-only cookie_consent_records table, validation, throttle:30,1
- GA4 loading is gated: window['ga-disable-{GA4_MEASUREMENT_ID}'] = true unless analytics: true is in the consent cookie
- Footer link "Cookie Settings" / "إعدادات ملفات تعريف الارتباط" reopens the customise modal

SEO:
- /sitemap.xml lists 16 routes (8 EN + 8 AR equivalents) with lastmod, changefreq weekly, priority 1.0 for /, 0.8 for /pricing, 0.6 for others
- /robots.txt: User-agent: *, Allow: /, Disallow: /app/, Sitemap: https://efirm.io/sitemap.xml
- Every public page <head> includes:
  - <title> from config('seo.{page}.title.{locale}'), max 60 chars (enforced by Pest test)
  - meta description, max 160 chars (enforced)
  - canonical link
  - OG tags: og:title, og:description, og:image (defaults to /img/og-image.jpg, fallback /img/og-fallback.png), og:url, og:type=website, og:locale
  - Twitter card tags
  - hreflang: self + alternate + x-default (English is x-default)
  - JSON-LD <script type="application/ld+json"> with LegalService schema including name, description, url, areaServed: ["JO","LB","PS","IQ"], priceRange: "$20-$30", address (Amman, Jordan)

Accessibility:
- All landmarks present: <header role="banner">, <nav role="navigation">, <main role="main">, <footer role="contentinfo">
- One h1 per page, hierarchical h2/h3
- Skip-to-content link at top of <body>, visible on Tab focus, anchors to <main>
- All <img> have alt; decorative use alt=""
- All form inputs have associated <label> via for/id; no placeholder-as-label
- Focus indicators: focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 (offset:ring-offset-white on dark bg)
- Reduced motion media query: @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }}
- Touch targets ≥ 44x44 CSS pixels (Pest browser test asserts)
- Pest Browser Plugin test runs axe-core against / and /ar; expects ZERO violations of WCAG 2.1 AA

Legal stubs (P-4 to P-7):
- Each renders Markdown from resources/markdown/legal/{slug}-{locale}.md
- All 8 stub .md files contain generic SaaS template content (~500 words each) adapted to Jordan jurisdiction
- Persistent amber-100 disclaimer banner at top of each: "This document is a stub pending final legal review. For the binding version, contact legal@efirm.io." / "هذا المستند نسخة مؤقتة بانتظار المراجعة القانونية النهائية..."
- "Last updated: 2026-06-24" / "آخر تحديث: 2026-06-24"

Polish & verification:
- Lighthouse audit on http://efirm.test/ via Lighthouse CLI: Performance ≥ 90, Accessibility ≥ 95, Best Practices ≥ 95, SEO ≥ 95
- LCP < 2.5s, CLS < 0.10 (use PageSpeed Insights against staging URL)
- All Pest tests pass: php artisan test
- axe-core via Pest Browser Plugin: zero violations

CI integration:
- Add Lighthouse CI step to .github/workflows/ci.yml that fails the build if Performance < 90, SEO < 95, or Accessibility < 95
- Add Pest Browser test step that runs axe-core checks

OpenAPI update:
- Add /v1/public/cookie-consent POST path with full schema, tag "Public", security: []

Commit message: SURGE-LP-01 W-6: cookie consent + SEO + a11y + legal stubs + polish
```

### Wave 6 verification

```bash
php artisan migrate
php artisan test
# Lighthouse CLI on local:
npx lighthouse http://efirm.test/ --only-categories=performance,accessibility,best-practices,seo --chrome-flags="--headless"
# Expect: Performance ≥ 90, Accessibility ≥ 95, Best Practices ≥ 95, SEO ≥ 95
# Browser test:
php artisan test --filter=CookieConsent
php artisan test --filter=Accessibility
# Manual: visit /, dismiss banner with each of the three options, verify cookie + DB row + GA4 gating
```

---

## Post-execution checks (after all 6 Waves)

### Functional verification

```bash
# Full test suite
php artisan test --parallel

# Migration verification
php artisan migrate:status

# Route verification
php artisan route:list --columns=Method,URI,Name | grep -i "public\|marketing\|landing\|demo\|legal\|sitemap\|robots\|cookie"

# OpenAPI verification
php artisan openapi:validate openapi/spec.yaml

# Static analysis
./vendor/bin/phpstan analyse
./vendor/bin/pint --test

# Lighthouse
npx lighthouse http://efirm.test/ --view
npx lighthouse http://efirm.test/ar --view
```

### Manual QA pass

- [ ] EN landing renders all 13 sections in order
- [ ] AR landing renders RTL with correct font and section order reversal
- [ ] Pricing CTAs route to `/register` with correct UTM and plan params
- [ ] Demo request form submits successfully — email arrives, Linear ticket created, thank-you page interpolates firm name
- [ ] Demo request validation: each field shows correct error message in current locale
- [ ] Cookie banner appears on first visit; "Accept All" loads GA4, "Reject" does not
- [ ] Footer "Cookie Settings" reopens modal
- [ ] All 4 legal stub pages display with disclaimer banner
- [ ] FAQ accordion: desktop multi-open, mobile single-open
- [ ] Language toggle persists across sessions (cookie test)
- [ ] Lighthouse SEO ≥ 95 on both `/` and `/ar`
- [ ] axe-core: zero WCAG 2.1 AA violations
- [ ] 5 reference devices (M-8): RTL renders without regression

### Pre-launch gate (Khaldoun review)

- [ ] Deploy to staging at `staging.efirm.io`
- [ ] Share staging URL with Khaldoun by email; reference SURGE-LP-01-landing-page.md Section 7 for the specific review asks
- [ ] Specifically request review of S-7 (Arabic AI Demo prompt + output authenticity) and S-8 (procedural-accuracy claims)
- [ ] Wait for written sign-off recorded in `docs/validation/02_advisor_meeting_log.md`
- [ ] Allow ≥ 48 hours between Khaldoun's sign-off and public DNS cutover
- [ ] After sign-off, cutover DNS to point `efirm.io` apex to the Cloudways instance

### Known issues to flag (not blockers)

These are accepted per LP-D-15 and LP-R-01 / LP-R-09 — log them in the PR description:

1. **Legal stub pages** are placeholders. Replacement pending tech-lawyer referral from Khaldoun. Visible disclaimer banner mitigates.
2. **JOD exchange rates** are hardcoded in `config/marketing.php`. Quarterly manual review required.
3. **Founding-firm "First 50" badge** has no live counter. Manual toggle via `config('marketing.founding_firm_badge_enabled')` when threshold reached.
4. **No pilot testimonials yet.** Per LP-D-10, generic "Built with practicing lawyers in Amman" copy is in place.

---

## Pull Request template

```markdown
## SURGE-LP-01: eFirm Public Landing Page

**Wave-Ready Package:** `docs/surges/SURGE-LP-01-landing-page.md` v1.0
**Decisions:** LP-D-01 through LP-D-16 (see WRP Section 7)
**Waves shipped:** W-1, W-2, W-3, W-4, W-5, W-6

### What this PR includes

- Public landing page at `efirm.io/` with EN/AR locales (13 sections)
- Demo request form + API-001 with email + Linear integration
- Cookie consent banner + API-002 audit trail (PDPL Article 13)
- SEO: sitemap, robots, JSON-LD LegalService, hreflang
- Accessibility: WCAG 2.1 AA (axe-core zero violations)
- 4 legal stub pages with disclaimer banners
- 7 new test files; OpenAPI spec extended with 2 new endpoints

### What this PR explicitly does NOT do

- Replace stub legal documents (pending Khaldoun's tech-lawyer referral)
- Implement a live founding-firm counter (manual config flag for v1)
- Add motion / video to hero (LP-D-12; deferred to v2)

### Verification

- [x] All Pest tests pass
- [x] Lighthouse: Performance ≥ 90, SEO ≥ 95, A11y ≥ 95
- [x] axe-core: zero WCAG 2.1 AA violations
- [x] 5-device RTL visual QA passed
- [ ] Khaldoun pre-launch staging review (LP-R-03/04) — SCHEDULED

### Risks at deploy

- LP-R-01: Stub legal pages — mitigated by visible disclaimer banner
- LP-R-03/04: Pre-launch Khaldoun review pending — must complete before DNS cutover

### Deployment notes

- New env vars required: `LINEAR_API_KEY`, `LINEAR_TEAM_ID`, `LINEAR_LEADS_PROJECT_ID`, `GA4_MEASUREMENT_ID`, `MAIL_LEAD_NOTIFICATION`
- 2 new migrations: `create_demo_requests_table`, `create_cookie_consent_records_table`
- Cache clear required after deploy: `php artisan config:cache && php artisan route:cache && php artisan view:cache`
```

---

**End of Claude Code Runbook for SURGE-LP-01**
