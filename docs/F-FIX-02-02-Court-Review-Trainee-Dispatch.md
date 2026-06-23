# F-FIX-02.2 — Court Review Trainee Dispatch

**Flow ID:** F-FIX-02.2
**Surge:** S-FIX-02
**Estimated effort:** ~1 day (multi-Wave)
**Source:** `validation/02_advisor_meeting_log.md` Conversation 3.5, Decision #29

## Khaldoun's framing (verbatim)

> "Court Reviews (مراجعات المحكمة): This is brilliant, and most Western software misses it. A 'Review' is not a hearing. It's when I send a trainee associate physically to the Palace of Justice on a random Tuesday just to get a judge's signature, check if an expert dropped a report, or pay a fee."

## Goal

Make the Court Review surface usable for the "dispatch a trainee to the Palace of Justice" workflow. The existing CourtReview entity (S-08) is correctly architected; this Flow refines the workflow specifically around dispatch, mobile completion, and visibility for the trainee assigned.

## Scope

### Schema (additive)

Migration `add_dispatch_fields_to_court_reviews`:

```php
Schema::table('court_reviews', function (Blueprint $table) {
    $table->ulid('dispatched_to_user_id')->nullable()->after('review_type');
    $table->timestamp('dispatched_at')->nullable();
    $table->ulid('completed_by_user_id')->nullable();
    $table->string('location_in_courthouse_ar', 200)->nullable();
    $table->string('location_in_courthouse_en', 200)->nullable();
    $table->text('expected_outcome_ar')->nullable();
    $table->text('expected_outcome_en')->nullable();
    $table->text('completion_notes')->nullable();
    $table->ulid('evidence_document_id')->nullable(); // FK to documents table — photo/scan
    
    $table->foreign('dispatched_to_user_id')->references('id')->on('users');
    $table->foreign('completed_by_user_id')->references('id')->on('users');
    $table->foreign('evidence_document_id')->references('id')->on('documents');
    $table->index(['workspace_id', 'dispatched_to_user_id', 'status']);
});
```

### Models

Update `app/Models/CourtReview.php`:
- `belongsTo(User::class, 'dispatched_to_user_id')` as `dispatchedTo()`
- `belongsTo(User::class, 'completed_by_user_id')` as `completedBy()`
- `belongsTo(Document::class, 'evidence_document_id')` as `evidenceDocument()`
- Accessor `is_dispatched` — returns `dispatched_to_user_id !== null && status !== 'completed'`
- Accessor `is_overdue_dispatch` — returns true if dispatched > 7 days ago and not completed

### Services

`app/Services/CourtReviewDispatchService.php`:
- `dispatch(CourtReview $review, User $assignTo, array $context, User $dispatcher): CourtReview` — sets dispatched_to_user_id, dispatched_at, expected_outcome, location; creates audit log entry; sends notification to assignee
- `complete(CourtReview $review, array $data, ?Document $evidence, User $completer): CourtReview` — sets completed_by_user_id, completion_notes, evidence_document_id; transitions status to 'completed'; optional TimeEntry creation
- `getDispatchedToMe(User $user): Collection` — returns active dispatches for current user, ordered by dispatched_at

### Filament Resources

Update `app/Filament/Resources/CourtReviewResource.php`:
- New form section "Dispatch" containing dispatched_to_user_id (select from workspace members), expected_outcome_ar/en, location_in_courthouse_ar/en
- Table filters: "Dispatched to Me", "Awaiting Dispatch", "Completed Today"
- New action: "Dispatch" — opens modal with assignee + context fields
- New action: "Mark Complete" — opens modal with completion_notes + optional file upload for evidence
- Status badges: pending (gray), dispatched (yellow), completed (green), cancelled (red)

New: `app/Filament/Pages/MyCourtTasksToday.php`:
- Custom Filament page accessible to all workspace members
- Dashboard widget showing CourtReviews dispatched to current user, grouped by Court
- Quick "Mark Complete" action inline per row
- Mobile-optimized layout (single-column cards)

Update `app/Filament/Resources/MatterResource.php`:
- "Court Reviews" relation manager — already exists per S-08; ensure dispatch fields visible in table columns

### Mobile / PWA consideration

The MyCourtTasksToday page must render usably on mobile. Trainees use phones at the courthouse, not laptops.

- Single-column card layout at breakpoints < 768px
- Large tap targets (min 44x44px)
- Photo upload via native camera input (`<input type="file" accept="image/*" capture="environment">`)
- Offline-tolerant: cached version of "my tasks today" available from PWA service worker (static assets only per CLAUDE.md §4.19 — this means cached page shell with placeholder for data, not actual cached data)

### API

- New: `POST /api/v1/court-reviews/{id}/dispatch` — body: { dispatched_to_user_id, expected_outcome_ar, expected_outcome_en, location_in_courthouse_ar, location_in_courthouse_en }
- New: `POST /api/v1/court-reviews/{id}/complete` — multipart/form-data: { completion_notes, evidence_file (optional) }
- New: `GET /api/v1/court-reviews/dispatched-to-me` — returns active dispatches for authenticated user
- OpenAPI spec updated

### Policies

`app/Policies/CourtReviewPolicy.php` updates:
- `dispatch` — Matter lead lawyer OR Admin/Owner role
- `complete` — dispatched_to_user OR Matter lead lawyer
- `view` (existing) — any workspace member with Matter access

### Tests (minimum 8 Pest)

Located at `tests/Feature/Litigation/CourtReviewDispatchTest.php`:

1. `dispatch_assigns_court_review_to_user_and_sends_notification`
2. `dispatched_to_me_endpoint_returns_only_active_dispatches_for_current_user`
3. `complete_with_evidence_file_creates_document_and_links_it`
4. `complete_without_evidence_succeeds_but_logs_missing_evidence_warning`
5. `only_dispatched_user_or_lead_lawyer_can_complete`
6. `dispatch_overdue_indicator_shows_when_more_than_7_days_old`
7. `mobile_breakpoint_renders_single_column_layout` (Browser test)
8. `workspace_isolation_prevents_seeing_other_workspace_dispatches`

## Wave decomposition

| Wave | Scope | Effort |
|---|---|---|
| W1 | Migration + Models + Service + Observer | 3h |
| W2 | Filament Resource updates + MyCourtTasksToday page + Mobile layout | 4h |
| W3 | API endpoints + Pest tests + OpenAPI | 3h |

## Acceptance criteria

- [ ] Trainee can be dispatched to a specific Court Review with expected outcome
- [ ] Trainee sees "My Court Tasks Today" page on phone
- [ ] Trainee can complete the task with optional photo evidence
- [ ] Lead lawyer can see all dispatches across all Matters
- [ ] All 8 Pest tests pass
- [ ] Code attribution comment cites Decision #29

## Code attribution comment

```php
/**
 * Court Review trainee dispatch workflow per advisor input from
 * Khaldoun Khater, validation/02_advisor_meeting_log.md
 * Conversation 3.5, Decision #29.
 */
```

## Edge cases

| Scenario | Expected behavior |
|---|---|
| Trainee dispatched but completes task different day | dispatched_at remains original; completion_at = now; both visible in audit |
| Dispatched user becomes inactive workspace member before completion | UI shows "former member" badge; another user can reassign via dispatch action |
| Evidence photo upload fails (network) | Save retries 3x; if fails, completion succeeds without evidence with warning logged |
| Multiple court reviews dispatched to same trainee same day | All visible in MyCourtTasksToday, grouped by Court |
| Cancelled Court Review with active dispatch | Dispatch fields preserved for audit; dispatched user notified of cancellation |

## Open items requiring Khaldoun follow-up

- Should the dispatched user receive a calendar invite (when CalendarIntegration is configured)?
- What's the typical "expected outcome" content — free text or templated dropdown?
- Should completion require photo evidence for certain review types (e.g., fee payment)?
