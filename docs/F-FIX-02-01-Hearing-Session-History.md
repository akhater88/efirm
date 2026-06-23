# F-FIX-02.1 — Hearing Session History

**Flow ID:** F-FIX-02.1
**Surge:** S-FIX-02
**Estimated effort:** ~2 days (multi-Wave)
**Source:** `validation/02_advisor_meeting_log.md` Conversation 3.5, Decision #28

## Khaldoun's framing (verbatim)

> "Hearings (الجلسات): This cannot just be a generic calendar. In Jordan, a hearing view needs to show the session history—what happened on May 10, what the judge said on June 1, and what we need to submit on July 5."

## Goal

Extend the existing Hearing entity (S-08 shipped) so that the Hearing detail view captures and displays what actually happened at each session — not just metadata about when/where the session occurred. Currently flat (date/type/status only); needs session-content capture.

## Scope

### Schema (additive)

Migration `add_session_content_to_hearings`:

```php
Schema::table('hearings', function (Blueprint $table) {
    $table->text('judge_statement_ar')->nullable();
    $table->text('judge_statement_en')->nullable();
    $table->text('outcome_summary_ar')->nullable();
    $table->text('outcome_summary_en')->nullable();
    $table->text('our_submissions_made')->nullable();
    $table->text('opposing_submissions_made')->nullable();
    $table->text('next_session_required_actions_ar')->nullable();
    $table->text('next_session_required_actions_en')->nullable();
    $table->json('session_attended_by')->nullable(); // array of workspace_member IDs
});
```

Migration `create_hearing_action_items_table`:

```php
Schema::create('hearing_action_items', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->ulid('workspace_id');
    $table->ulid('hearing_id');
    $table->text('description_ar');
    $table->text('description_en')->nullable();
    $table->date('due_date');
    $table->ulid('responsible_user_id')->nullable();
    $table->string('status', 30)->default('pending'); // pending | in_progress | completed | waived
    $table->ulid('obligation_id')->nullable(); // FK to auto-created Obligation
    $table->timestamps();
    $table->softDeletes();
    $table->ulid('created_by')->nullable();
    $table->ulid('updated_by')->nullable();
    
    $table->foreign('workspace_id')->references('id')->on('workspaces');
    $table->foreign('hearing_id')->references('id')->on('hearings');
    $table->foreign('responsible_user_id')->references('id')->on('users');
    $table->foreign('obligation_id')->references('id')->on('obligations');
    $table->index(['workspace_id', 'due_date']);
    $table->index(['responsible_user_id', 'status']);
});
```

### Models

- `app/Models/HearingActionItem.php` with `BelongsToWorkspace`, `BelongsTo` Hearing, `BelongsTo` Obligation, soft deletes
- Update `app/Models/Hearing.php`:
  - `hasMany(HearingActionItem::class)` relation
  - Casts for `session_attended_by` as `array`
  - Accessor `is_held` returning `status === 'held'`

### Services

- `app/Services/HearingSessionService.php`:
  - `recordOutcome(Hearing $hearing, array $data, User $by): Hearing` — saves session content, validates status='held', creates audit log
  - `addActionItem(Hearing $hearing, array $data, User $by): HearingActionItem` — creates action item + auto-creates Obligation
  - `getSessionsTimelineForMatter(Matter $matter): Collection` — returns held hearings ordered by start_time with full session content

### Observers

- `app/Observers/HearingActionItemObserver.php`:
  - On `creating`: if no obligation_id, auto-create an Obligation with `due_date` matching the action item, link via `obligation_id`
  - On `updating`: if status changes to 'completed', mark linked Obligation as completed; if 'waived', mark Obligation waived
  - On `deleting` (soft): mark linked Obligation waived with reason "Action item removed"

### Filament Resources

- `app/Filament/Resources/HearingResource.php` updates:
  - Form tab "Session Outcome" — visible only when `status='held'`; contains all 7 session content fields with proper RTL/LTR per-field locale handling
  - Form tab "Required Submissions" — relation manager for HearingActionItem
  - View page (read-only) renders session content in timeline-style layout

- New: `app/Filament/Resources/HearingResource/RelationManagers/ActionItemsRelationManager.php`:
  - Table columns: description (locale-aware), responsible user, due date, status
  - Form: description AR + EN, responsible user picker, due date picker, status

- Update `app/Filament/Resources/MatterResource.php`:
  - New tab on Matter view page: "Sessions Timeline"
  - Renders all held Hearings chronologically with collapsible cards showing session content
  - Empty state when no held Hearings yet

### API

- Existing `PUT /api/v1/hearings/{id}` accepts new session-content fields when status='held' (validation rejects otherwise)
- New `GET /api/v1/matters/{id}/sessions-timeline` returns held Hearings with full session content, paginated
- New `POST /api/v1/hearings/{id}/action-items` creates a HearingActionItem
- New `PUT /api/v1/hearing-action-items/{id}` updates an action item
- New `DELETE /api/v1/hearing-action-items/{id}` soft-deletes
- All endpoints scoped by Sanctum auth + workspace
- OpenAPI spec at `openapi/spec.yaml` updated

### Policies

- `app/Policies/HearingActionItemPolicy.php`:
  - `viewAny`, `view` — any workspace member with Matter access
  - `create` — Matter lead lawyer OR users in `session_attended_by` array of the Hearing
  - `update` — same as create
  - `delete` — Matter lead lawyer only

### Tests (minimum 12 Pest)

Located at `tests/Feature/Litigation/HearingSessionHistoryTest.php`:

1. `it_saves_session_content_when_hearing_status_is_held`
2. `it_rejects_session_content_save_when_hearing_status_is_scheduled` (422)
3. `it_auto_creates_obligation_when_action_item_is_added`
4. `it_marks_linked_obligation_completed_when_action_item_completed`
5. `it_marks_linked_obligation_waived_when_action_item_soft_deleted`
6. `sessions_timeline_endpoint_returns_held_hearings_chronologically`
7. `sessions_timeline_endpoint_excludes_scheduled_hearings`
8. `bilingual_session_content_renders_correctly_in_each_locale`
9. `only_attended_users_or_lead_lawyer_can_record_outcome`
10. `workspace_isolation_prevents_cross_workspace_action_item_access`
11. `soft_deleted_hearing_preserves_session_content_for_audit`
12. `concurrent_edit_on_session_content_returns_409`

## Wave decomposition

This Flow ships in 3 Waves:

| Wave | Scope | Effort |
|---|---|---|
| W1 | Migrations + Models + Observer + Service | 5h |
| W2 | Filament Resource updates + Sessions Timeline tab on Matter | 6h |
| W3 | API endpoints + OpenAPI spec + 12 Pest tests | 5h |

Wave-Ready Packages produced per Wave at `planning/F-FIX-02-01-Hearing-Session-History-WRP-{1,2,3}.md`.

## Acceptance criteria

- [ ] Migrations applied without breaking existing data
- [ ] HearingActionItem auto-creates Obligation on save
- [ ] Session content fields editable only when hearing.status='held'
- [ ] Sessions Timeline tab on Matter renders chronologically
- [ ] All 12 Pest tests pass
- [ ] AR + EN locale verified
- [ ] OpenAPI spec updated
- [ ] Code comments cite Decision #28

## Code attribution comment template

```php
/**
 * Hearing session history per advisor input from Khaldoun Khater
 * (Al-Dujani Office, Amman), validation/02_advisor_meeting_log.md
 * Conversation 3.5, Decision #28.
 *
 * Khaldoun's framing: "a hearing view needs to show the session
 * history—what happened on May 10, what the judge said on June 1,
 * and what we need to submit on July 5."
 */
```

## Edge cases

| Scenario | Expected behavior |
|---|---|
| Hearing status changes from 'held' back to 'scheduled' (postponement after holding) | Preserve session content as historical record; new hearing created for postponement |
| Trainee attempts to edit outcome of partner-attended session | Policy denies; UI hides edit button |
| Multiple Hearings on same date for same Matter | Timeline orders by start_time, not just date |
| Action item created with due_date in past | Allowed; user warned via UI but not blocked |
| Workspace member removed mid-flow while listed in session_attended_by | Reference preserved; UI shows former member name with "(former member)" suffix |

## Open items requiring Khaldoun follow-up

- Should `judge_statement` field be a single shared field across locales (he writes in Arabic only) or genuinely paired bilingual?
- Are there additional structured fields (e.g., adjournment reason if status changes to postponed) worth capturing?
- Should completing an action item require uploading evidence (a Document) of completion?
