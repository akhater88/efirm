# F-FIX-02.5 — Hearing Postponement Chain UX

**Flow ID:** F-FIX-02.5
**Surge:** S-FIX-02
**Estimated effort:** ~0.5 day (single-Wave)
**Source:** Inferred from `validation/02_advisor_meeting_log.md` Decision #28 context + general Khaldoun emphasis on procedural delays
**Wave-Ready Package needed:** No (single Wave; specs fit in this Flow file)

## Goal

Surface the Hearing postponement chain in the UI so cases delayed by procedural issues (notification not served, party absence, judge unavailability) are visible at a glance. The schema already supports postponement chains via `hearings.postponement_of_hearing_id` self-reference; this Flow makes the chain visible and requires postponement reasons to be documented.

## Scope

### Schema (additive)

Migration `add_postponement_metadata_to_hearings`:

```php
Schema::table('hearings', function (Blueprint $table) {
    $table->text('postponement_reason_ar')->nullable()->after('postponement_of_hearing_id');
    $table->text('postponement_reason_en')->nullable();
    $table->string('postponement_initiated_by', 30)->nullable(); // 'our_side' | 'opposing_side' | 'court' | 'unknown'
});
```

### Models

Update `app/Models/Hearing.php`:
- `belongsTo(Hearing::class, 'postponement_of_hearing_id')` as `postponedFrom()`
- `hasMany(Hearing::class, 'postponement_of_hearing_id')` as `postponements()`
- Accessor `is_postponement` — returns `postponement_of_hearing_id !== null`
- Accessor `postponement_chain_root` — recursively walks `postponedFrom()` to find the original Hearing
- Method `getPostponementChain(): Collection` — returns the full chain (root + all postponements) ordered chronologically

### Filament Resource updates

`app/Filament/Resources/HearingResource.php`:

Detail page widget: "Postponement Chain" — rendered above the Session Outcome tab (from F-FIX-02.1) when `is_postponement === true` OR `postponements()->exists()`:

```
[Visualization: Vertical list]

Original Hearing
├── Date: 2026-04-10
├── Type: Notification Session
├── Status: Postponed
└── Reason: Opposing party not served properly

Postponement 1 (current view)
├── Date: 2026-05-15
├── Type: Notification Session
├── Status: Held
└── Reason: Counsel illness

Postponement 2 (next scheduled)
├── Date: 2026-06-20
├── Type: Notification Session
└── Status: Scheduled
```

Each item in the chain is clickable, navigating to that Hearing's detail page.

Form schema additions:
- When creating a new Hearing as postponement (`postponement_of_hearing_id` is set in form), `postponement_reason_ar` becomes REQUIRED with min 10 chars
- `postponement_initiated_by` select with 4 options
- These fields appear in their own form section "Postponement Details" visible only when postponement_of_hearing_id is set

Table column additions:
- New column "Postponement Chain Length" (hidden by default, visible via column toggle)
- New filter "Was Postponed" (filters to Hearings where `postponement_of_hearing_id IS NOT NULL`)

### Matter Sessions Timeline integration

The Sessions Timeline tab from F-FIX-02.1 should visually group hearings that are in the same postponement chain:

- Hearings in the same chain share a left-border accent color
- Chain root has a "↑ Original Hearing" badge
- Postponements have a "← Postponed from [date]" link

### API

Update `POST /api/v1/hearings`:
- When `postponement_of_hearing_id` is present, `postponement_reason_ar` is required (min 10 chars)
- FormRequest validation handles this conditional requirement

New endpoint: `GET /api/v1/hearings/{id}/postponement-chain`:
- Returns the full chain as an ordered array (chronologically)
- Each entry includes: id, scheduled_at, hearing_type, status, postponement_reason

OpenAPI spec updated.

### Tests (minimum 5 Pest)

Located at `tests/Feature/Litigation/HearingPostponementChainTest.php`:

1. `creating_postponement_hearing_without_reason_returns_422`
2. `creating_postponement_hearing_with_reason_succeeds_and_links_correctly`
3. `postponement_chain_endpoint_returns_full_chain_chronologically`
4. `chain_navigation_works_from_any_hearing_in_chain`
5. `was_postponed_filter_returns_only_postponed_hearings`

## Content Specification

| Element | AR | EN |
|---|---|---|
| Section title | تسلسل تأجيل الجلسة | Postponement Chain |
| Form section title | تفاصيل التأجيل | Postponement Details |
| Field — postponement_reason_ar | سبب التأجيل | Postponement Reason |
| Field — postponement_initiated_by | جهة التأجيل | Postponement Initiated By |
| Option — our_side | من جانبنا | Our Side |
| Option — opposing_side | من جانب الخصم | Opposing Side |
| Option — court | من المحكمة | By the Court |
| Option — unknown | غير محدد | Unknown |
| Validation — reason required | سبب التأجيل مطلوب لأي جلسة مؤجلة | Postponement reason is required for any postponed hearing |
| Chain visualization label — original | الجلسة الأصلية | Original Hearing |
| Chain visualization label — postponement | تأجيل {n} | Postponement {n} |
| Chain visualization label — current | الجلسة الحالية | Current Hearing |
| Filter chip — Was Postponed | تم تأجيلها | Was Postponed |

Laravel localization keys: extend `resources/lang/{ar,en}/hearings.php`.

## Acceptance criteria

- [ ] Postponement reason required when creating postponement hearing
- [ ] Chain visualization visible on Hearing detail page
- [ ] Sessions Timeline visually groups chain members
- [ ] All 5 Pest tests pass
- [ ] Code attribution cites Decision #28 context

## Code attribution comment

```php
/**
 * Hearing postponement chain UX per advisor emphasis from Khaldoun Khater
 * on procedural delays in Jordanian commercial litigation.
 * validation/02_advisor_meeting_log.md Conversation 1 (Service Log emphasis)
 * + Conversation 3.5 (Decision #28 context).
 */
```

## Edge cases

| Scenario | Expected behavior |
|---|---|
| User creates postponement of a postponement (chain of 3+) | Allowed; chain visualization shows all in order |
| Original Hearing soft-deleted while postponements exist | Postponements preserved; chain shows "Original hearing archived" placeholder |
| Hearing has both postponements AND is a postponement itself | Chain shows full path from root through self to descendants |
| Reason text contains line breaks | Preserved in display; rendered with `whitespace-pre-wrap` |
| Circular reference attempt (A postponed by B, B postponed by A) | Validation rejects with "Circular postponement reference detected" |
