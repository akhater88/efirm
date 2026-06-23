# F-FIX-02.4 — Contextual Quick Timer

**Flow ID:** F-FIX-02.4
**Surge:** S-FIX-02
**Estimated effort:** ~1 day (multi-Wave)
**Source:** `validation/02_advisor_meeting_log.md` Conversation 3.5, Decision #31

## Khaldoun's framing (verbatim)

> "I see HAQQ has that prominent purple 'Start Timer' banner right at the top of the dashboard. It's smart because lawyers forget to log hours, but it's a bit aggressive. For your platform, keep the quick-timer functionality, but make sure it links directly to the specific Legal Matter or Hearing the user is working on. If I can click 'Start Timer' directly from inside a case file, it saves me the extra clicks of searching for the client's name later."

## Goal

Replace the global Start Timer with contextual timer buttons embedded inside Matter, Hearing, and Document detail pages. A floating active-timer indicator persists in the Filament panel chrome so the user can always see and stop the running timer.

## Scope

### Schema

The TimeEntry entity already exists (S-07). This Flow does not add columns to TimeEntry but uses existing fields:
- `started_at` (already exists)
- `ended_at` (already exists, nullable)
- `matter_id` (already exists)
- `taskable_type` / `taskable_id` (polymorphic — for linking to Hearing or Document)
- `billable` (already exists, default true)
- `description` (already exists, nullable)
- `user_id` (already exists)

Optional new field via migration `add_started_via_context_to_time_entries`:
- `started_via_context` ENUM('matter', 'hearing', 'document', 'global', 'manual') NULLABLE — tracks where the timer was started for analytics; defaults to 'manual' for non-timer entries

### Services

`app/Services/QuickTimerService.php`:

```php
class QuickTimerService {
    public function startForMatter(Matter $matter, User $user): TimeEntry;
    public function startForHearing(Hearing $hearing, User $user): TimeEntry;
    public function startForDocument(Document $document, User $user): TimeEntry;
    public function stop(TimeEntry $entry, ?string $description, User $user): TimeEntry;
    public function getActiveTimerForUser(User $user): ?TimeEntry;
}
```

Behavior:
- Starting a new timer when one is already active: returns 409 with message "You have an active timer; stop it before starting a new one"
- Stopping a timer: sets `ended_at = now()`, opens UI modal for description/adjustment
- Active timer = `TimeEntry` where `ended_at IS NULL AND user_id = current_user.id`

### Filament Resources

Update `app/Filament/Resources/MatterResource.php` (View page header actions):
- Add `Action::make('start_timer')` — visible if no active timer; calls QuickTimerService::startForMatter()
- Button label: "بدء المؤقت" / "Start Timer"

Update `app/Filament/Resources/HearingResource.php` (View page header actions):
- Same Action::make('start_timer') pattern but for Hearing context

Document editor (S-03 surface, custom Livewire) — out of scope for this Flow; defer to future iteration. Note: Khaldoun mentioned "case file" not "document editor" — primary surfaces are Matter and Hearing.

### Active Timer Indicator (Filament Panel Chrome)

New: `app/Filament/Widgets/ActiveTimerIndicator.php` — custom widget rendered in Filament's panel header via render hook:

```php
// In AppServiceProvider boot():
Filament::registerRenderHook(
    'panels::user-menu.before',
    fn () => view('filament.widgets.active-timer-indicator'),
);
```

Widget displays (when active timer exists):
- Matter title (truncated to 30 chars)
- Elapsed time (live-updating via Alpine.js)
- "Stop" button (calls QuickTimerService::stop())

Styling: floating pill, top-right corner of panel, distinct color (amber background) so always visible.

Widget displays nothing when no active timer.

### Mobile / PWA consideration

The active timer indicator must be visible on mobile breakpoints. Since the Filament panel adapts responsively, the widget should:
- Collapse to icon-only at < 768px (icon + elapsed time as tooltip)
- Tap opens the timer detail modal

### API

New: `POST /api/v1/time-entries/start`:
- Body: `{ matter_id?, hearing_id?, document_id?, billable? }`
- Exactly one of matter_id/hearing_id/document_id required
- Returns 201 with TimeEntry
- Returns 409 if user already has active timer

New: `POST /api/v1/time-entries/{id}/stop`:
- Body: `{ description?, adjusted_duration_minutes? }`
- Returns 200 with stopped TimeEntry
- Returns 422 if entry already stopped

New: `GET /api/v1/time-entries/active`:
- Returns user's currently-active TimeEntry or null

OpenAPI spec updated.

### Tests (minimum 8 Pest)

Located at `tests/Feature/TimeTracking/ContextualTimerTest.php`:

1. `start_timer_from_matter_creates_time_entry_linked_to_matter`
2. `start_timer_from_hearing_creates_time_entry_with_polymorphic_link`
3. `cannot_start_timer_when_user_already_has_active_timer`
4. `stop_timer_sets_ended_at_and_allows_description`
5. `active_timer_endpoint_returns_user_specific_entry`
6. `active_timer_indicator_renders_in_filament_chrome_when_active`
7. `active_timer_indicator_hidden_when_no_active_timer`
8. `workspace_isolation_active_timer_not_visible_across_workspaces`

## Content Specification

| Element | AR | EN |
|---|---|---|
| Action button — start | بدء المؤقت | Start Timer |
| Action button — stop | إيقاف المؤقت | Stop Timer |
| Indicator label format | المؤقت يعمل: {matter} ({elapsed}) | Timer running: {matter} ({elapsed}) |
| Stop modal title | إيقاف المؤقت | Stop Timer |
| Stop modal description label | الوصف (اختياري) | Description (optional) |
| Stop modal billable toggle | قابل للفوترة | Billable |
| Stop modal save | حفظ | Save |
| Active timer conflict error | لديك مؤقت نشط بالفعل. أوقفه قبل بدء واحد جديد | You have an active timer. Stop it before starting a new one |
| Success — timer started (toast) | تم بدء المؤقت | Timer started |
| Success — timer stopped (toast) | تم حفظ المدة | Time logged |

Laravel localization keys: `resources/lang/{ar,en}/quick_timer.php` — new files.

## Wave decomposition

| Wave | Scope | Effort |
|---|---|---|
| W1 | Service + Migration + API endpoints | 3h |
| W2 | Filament header actions on Matter and Hearing + Active timer widget | 4h |
| W3 | Pest tests + Mobile breakpoint verification | 3h |

## Acceptance criteria

- [ ] Timer can be started from Matter detail page header
- [ ] Timer can be started from Hearing detail page header
- [ ] Only one active timer per user at a time
- [ ] Active timer widget visible in Filament chrome on all pages
- [ ] Mobile breakpoint shows widget as collapsed icon
- [ ] All 8 Pest tests pass
- [ ] Code attribution cites Decision #31

## Code attribution comment

```php
/**
 * Contextual Quick Timer per advisor input from Khaldoun Khater,
 * validation/02_advisor_meeting_log.md Conversation 3.5, Decision #31.
 *
 * Khaldoun's framing: contextual to the Matter or Hearing being worked
 * on, not a global banner. Saves the lawyer the extra clicks of
 * searching for the client name later.
 */
```

## Edge cases

| Scenario | Expected behavior |
|---|---|
| User starts timer, closes browser, returns next day | Active timer still running; widget shows elapsed time > 24h with warning indicator; user prompted to stop or adjust |
| User stops timer with elapsed > 12 hours | Modal warns "Long duration detected"; user confirms or adjusts before save |
| Browser tab inactive for hours | Widget polls server every 60s when tab visible; on tab focus, re-fetches active timer state |
| Network failure on stop | Local state preserves intent; retries; widget shows "Saving..." with eventual success or surface error |
| User on Matter detail; another user stops their timer remotely (shouldn't happen but defensive) | Widget detects via poll; refreshes to "no active timer" state |
| Multiple browser tabs open same user | Widget syncs across tabs via storage event; stopping in one tab updates the other |
