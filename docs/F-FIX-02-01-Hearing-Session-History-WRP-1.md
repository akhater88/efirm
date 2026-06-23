# WAVE-READY PACKAGE: F-FIX-02.1 Wave 1 — Hearing Session History Foundation

Version: 1.0 | Product Designer: Abdullah (via AODC Product Designer) | Date: 2026-06-23
Status: Ready for Dev

---

## 1. INTENT DEFINITION

**Problem:** The Hearing entity currently captures only metadata (date, type, status) about court sessions. A practicing Jordanian commercial litigator (Khaldoun Khater, Al-Dujani Office) reviewed the implementation and confirmed it does not match how lawyers actually use hearing records. Lawyers need to capture what the judge said, what was submitted, and what must be submitted next session. Without this, the Hearing view becomes "another chore" rather than the load-bearing case-narrative artifact litigators rely on.

**Target User:** Commercial litigation lawyers (Owner, Admin, Member roles) in Levant law firms managing active litigation Matters. Specifically, partners reviewing case progress and associates preparing for upcoming hearings.

**Outcome:** When a hearing's status changes to 'held', the user can record judge statements (AR + EN), outcome summary, submissions made by each side, and required follow-up actions. Each follow-up action automatically creates an Obligation in the existing Obligations system, threading the hearing's outputs into the firm's deadline-tracking infrastructure.

**Success Metrics:**
- Practitioner can capture a held session's full content in under 3 minutes
- 100% of action items recorded auto-create matching Obligations
- Zero data loss when a hearing transitions through scheduled → held → postponed states
- Sessions Timeline on Matter detail loads in under 2 seconds for a Matter with up to 20 hearings

**Business Value:** Transforms the Hearing surface from a calendar entry into a case narrative artifact. This directly addresses Khaldoun's stated #1 daily stress ("missing a court deadline"), supports the firm's institutional knowledge (associates can read past sessions), and matches Levant litigation practice where session-by-session context determines case trajectory.

---

## 2. USER STORIES

**US-001:** As a litigation lawyer who attended today's hearing, I want to record what the judge said and what I need to submit by next session, so that my associates and partners have an accurate record and I don't miss the follow-up deadline.

Acceptance Criteria:
- GIVEN a Hearing exists with status='scheduled' WHEN the user attempts to save session content fields THEN the API returns 422 with validation error "Session outcome can only be recorded for hearings that have been held"
- GIVEN a Hearing has status='held' AND the current user is in the hearing's session_attended_by array WHEN the user submits judge_statement_ar, outcome_summary_ar, and our_submissions_made THEN the Hearing is updated successfully with the new fields populated
- GIVEN a Hearing has status='held' AND the current user is NOT in session_attended_by AND is NOT the Matter's lead lawyer WHEN the user attempts to record session content THEN the Policy denies with 403 and error message "Only attendees or the lead lawyer can record session outcome"
- GIVEN a session content save succeeds WHEN the response is returned THEN it includes the updated Hearing with all session content fields visible

Edge Cases:
- Error: Network failure mid-save → autosave debounced, retry once on reconnect, surface to user only if 3 retries fail
- Empty: No session content fields populated yet → form renders with empty inputs, no validation errors until submit
- Loading: Save in progress → submit button disabled, spinner shown, no double-submit possible
- Offline: No save → form retains entered content in browser memory, banner shows "Offline — content will save when connection returns"
- Boundary: judge_statement_ar max 50000 characters; UI shows character counter when above 40000
- RTL: Arabic judge statement text rendered RTL automatically via CSS logical properties; English statement in same form renders LTR

**US-002:** As a litigation lawyer, I want to add follow-up action items to a held hearing (e.g., "submit witness list by July 5"), so that each item automatically becomes a tracked Obligation tied to this hearing.

Acceptance Criteria:
- GIVEN a Hearing has status='held' WHEN the user adds a HearingActionItem with description_ar="تقديم قائمة الشهود" and due_date="2026-07-05" AND responsible_user_id set THEN a new HearingActionItem record is created
- GIVEN a HearingActionItem is created WHEN the HearingActionItemObserver fires on creating event THEN a matching Obligation is auto-created with due_date matching the action item AND obligation_id is linked back to the HearingActionItem
- GIVEN a HearingActionItem exists with linked Obligation WHEN the action item status changes to 'completed' THEN the linked Obligation is marked completed via the observer
- GIVEN a HearingActionItem is soft-deleted WHEN the deletion completes THEN the linked Obligation is marked 'waived' with reason "Action item removed"

Edge Cases:
- Error: Obligation auto-creation fails → HearingActionItem creation rolled back via DB transaction
- Empty: HearingActionItem with no responsible_user_id → allowed; Obligation created without responsible user assigned
- Loading: Obligation creation pending → action item save shows "Saving..." state
- Offline: Cannot create action item → form disabled, banner shown
- Boundary: description_ar required and min 5 chars; due_date must be >= today (or admin-overridden)
- RTL: Description text editor respects per-paragraph direction

**US-003:** As a partner reviewing case progress, I want to see all held hearings on a Matter as a chronological timeline showing session content, so that I can understand case trajectory without opening each hearing individually.

Acceptance Criteria:
- GIVEN a Matter has 3 held Hearings dated 2026-04-10, 2026-05-15, 2026-06-20 WHEN the user opens the Matter detail page and clicks the "Sessions Timeline" tab THEN the timeline renders all 3 hearings in chronological order (oldest first)
- GIVEN a hearing on the timeline has session content WHEN the user expands its card THEN judge statement, outcome summary, submissions, and action items are visible in the current locale
- GIVEN a Matter has zero held hearings WHEN the user opens the Sessions Timeline tab THEN an empty state displays with text "No held sessions yet on this Matter"
- GIVEN a Matter has 20 held hearings WHEN the timeline endpoint is called THEN the response paginates with cursor-based pagination; default page size 10

Edge Cases:
- Error: Timeline fetch fails → show retry button with error message in current locale
- Empty: Matter has scheduled but no held hearings → empty state shown (timeline excludes scheduled)
- Loading: First page loads → skeleton timeline cards displayed
- Offline: Cannot fetch → cached last-known state shown with banner
- Boundary: Pagination next_cursor returns null at end
- RTL: Timeline cards order remains chronological (left-to-right or right-to-left depending on locale)

---

## 3. WIREFRAMES (FILAMENT RESOURCE SPECIFICATION)

Since the project operates Filament-everywhere (no Figma for non-wedge surfaces), this section specifies Filament Resource configurations.

Figma Link: N/A — Filament-rendered surfaces
Stakeholder Sign-off: Pending (founder confirms after F-FIX-02.1 W2 ships)

### HearingResource.php updates

Form schema additions (visible when `$record->status === 'held'`):

```
Tab: "Session Outcome" (نتيجة الجلسة)
  Section: Judge & Outcome
    Textarea: judge_statement_ar — label "تصريح القاضي" — rows 4 — max 50000 chars — visible/required when locale is AR
    Textarea: judge_statement_en — label "Judge Statement" — rows 4 — max 50000 chars — optional
    Textarea: outcome_summary_ar — label "ملخص النتيجة" — rows 3 — max 10000 chars
    Textarea: outcome_summary_en — label "Outcome Summary" — rows 3 — max 10000 chars
  Section: Submissions
    Textarea: our_submissions_made — label "مرافعاتنا في الجلسة" — rows 3
    Textarea: opposing_submissions_made — label "مرافعات الطرف الآخر" — rows 3
  Section: Attendance
    Multi-select: session_attended_by — label "الحضور من المكتب" — options from workspace_members where role in ['Owner', 'Admin', 'Member'] — searchable

Tab: "Required Submissions" (الإجراءات المطلوبة للجلسة القادمة)
  RelationManager: ActionItemsRelationManager (defined below)
```

Form schema validation:
- All session content fields nullable individually
- Tab visibility condition: `fn($record) => $record && $record->status === 'held'`
- Save action calls HearingSessionService::recordOutcome() rather than direct save

View page (read-only) renders session content in timeline-style layout with collapsible sections.

### ActionItemsRelationManager.php

Table columns:
- description (locale-aware accessor)
- responsible_user.name
- due_date (formatted per locale)
- status (badge: pending=yellow, in_progress=blue, completed=green, waived=gray)

Form schema:
- Textarea: description_ar — required min 5 max 2000 chars
- Textarea: description_en — optional max 2000 chars
- DatePicker: due_date — required min today (admin override allowed)
- Select: responsible_user_id — options from workspace members — optional
- Select: status — options [pending, in_progress, completed, waived] — default 'pending'

Bulk actions: mark_completed, mark_waived (both with confirmation)

### MatterResource.php updates

Add new tab to MatterResource view page: "Sessions Timeline" (تسلسل الجلسات)

Tab content: Custom Livewire component `App\Livewire\SessionsTimeline` that:
- Fetches held Hearings for this Matter via HearingSessionService::getSessionsTimelineForMatter()
- Renders each as a collapsible card with date, type badge, judge statement, outcome, submissions, action items
- Empty state shown when no held hearings exist
- Pagination via "Load more" button at bottom

Navigation Flow:
- Matter detail → "Sessions Timeline" tab → expand a card → see session content
- Card click → opens Hearing detail page in new tab
- "Add Hearing" action remains on the existing Hearings tab

Responsive Notes:
- Desktop: Cards display in full width with all sections expanded by default
- Tablet: Cards display full width with sections collapsed by default
- Mobile: Cards stack vertically with sections collapsed; tap to expand

RTL Notes:
- Cards arrange top-to-bottom regardless of locale (no horizontal timeline)
- Per-card content respects per-paragraph direction via CSS logical properties
- Date display uses locale-aware Carbon formatting

---

## 4. API CONTRACTS

### API-001: Update Hearing with Session Content

Endpoint: PUT /api/v1/hearings/{id}
Description: Updates a Hearing record, including new session-content fields when status='held'
Authentication: Bearer token (Laravel Sanctum)
OpenAPI Spec Path: openapi/spec.yaml (agent must update alongside implementation)

Request:
  Headers: { "Authorization": "Bearer {token}", "Content-Type": "application/json", "Accept-Language": "ar|en" }
  Body: {
    "status": "string (optional) - hearing status - in:scheduled,held,postponed,cancelled",
    "judge_statement_ar": "string (optional) - Judge's statement in Arabic - nullable|string|max:50000",
    "judge_statement_en": "string (optional) - Judge's statement in English - nullable|string|max:50000",
    "outcome_summary_ar": "string (optional) - Outcome summary in Arabic - nullable|string|max:10000",
    "outcome_summary_en": "string (optional) - Outcome summary in English - nullable|string|max:10000",
    "our_submissions_made": "string (optional) - Our side's submissions - nullable|string|max:20000",
    "opposing_submissions_made": "string (optional) - Opposing party submissions - nullable|string|max:20000",
    "session_attended_by": "array (optional) - Array of workspace_member ULIDs who attended - nullable|array",
    "session_attended_by.*": "string (required if array provided) - Workspace member ULID - exists:workspace_members,id"
  }

Response (200):
  {
    "data": {
      "id": "ULID string",
      "matter_id": "ULID string",
      "status": "string",
      "hearing_type": "string",
      "scheduled_at": "ISO 8601 datetime",
      "judge_statement_ar": "string or null",
      "judge_statement_en": "string or null",
      "outcome_summary_ar": "string or null",
      "outcome_summary_en": "string or null",
      "our_submissions_made": "string or null",
      "opposing_submissions_made": "string or null",
      "session_attended_by": "array of ULIDs or null",
      "is_held": "boolean",
      "updated_at": "ISO 8601 datetime"
    }
  }

Error Responses:
  401: { "error": "unauthorized", "message": "Authentication required" }
  403: { "error": "forbidden", "message": "Only attendees or the lead lawyer can record session outcome" }
  404: { "error": "not_found", "message": "Hearing not found" }
  409: { "error": "conflict", "message": "Hearing was modified by another user; please reload and try again" }
  422: { "error": "unprocessable", "errors": { "judge_statement_ar": ["Session outcome can only be recorded for hearings that have been held"] } }
  500: { "error": "server_error", "message": "An internal error occurred. Please try again or contact support." }

### API-002: Get Sessions Timeline for Matter

Endpoint: GET /api/v1/matters/{id}/sessions-timeline
Description: Returns held Hearings for a Matter in chronological order with full session content
Authentication: Bearer token (Laravel Sanctum)
OpenAPI Spec Path: openapi/spec.yaml

Request:
  Headers: { "Authorization": "Bearer {token}", "Accept-Language": "ar|en" }
  Query Parameters:
    cursor: "string (optional) - pagination cursor"
    per_page: "integer (optional, default 10, max 50)"

Response (200):
  {
    "data": [
      {
        "id": "ULID string",
        "scheduled_at": "ISO 8601 datetime",
        "hearing_type": "string",
        "hearing_type_label": "localized label",
        "judge_statement_ar": "string or null",
        "judge_statement_en": "string or null",
        "outcome_summary_ar": "string or null",
        "outcome_summary_en": "string or null",
        "our_submissions_made": "string or null",
        "opposing_submissions_made": "string or null",
        "action_items": [
          {
            "id": "ULID string",
            "description_ar": "string",
            "description_en": "string or null",
            "due_date": "ISO 8601 date",
            "responsible_user": { "id": "ULID string", "name": "string" },
            "status": "string"
          }
        ]
      }
    ],
    "next_cursor": "string or null",
    "prev_cursor": "string or null"
  }

Error Responses:
  401: { "error": "unauthorized", "message": "Authentication required" }
  403: { "error": "forbidden", "message": "Access denied to this Matter" }
  404: { "error": "not_found", "message": "Matter not found" }
  500: { "error": "server_error", "message": "An internal error occurred" }

### API-003: Create HearingActionItem

Endpoint: POST /api/v1/hearings/{hearing_id}/action-items
Description: Creates a follow-up action item for a held Hearing; auto-creates linked Obligation
Authentication: Bearer token (Laravel Sanctum)
OpenAPI Spec Path: openapi/spec.yaml

Request:
  Headers: { "Authorization": "Bearer {token}", "Content-Type": "application/json", "Accept-Language": "ar|en" }
  Body: {
    "description_ar": "string (required) - Action description in Arabic - required|string|min:5|max:2000",
    "description_en": "string (optional) - Action description in English - nullable|string|max:2000",
    "due_date": "date (required) - Due date YYYY-MM-DD - required|date|after_or_equal:today",
    "responsible_user_id": "string (optional) - Workspace member ULID - nullable|exists:workspace_members,id",
    "status": "string (optional) - default 'pending' - in:pending,in_progress,completed,waived"
  }

Response (201):
  {
    "data": {
      "id": "ULID string",
      "hearing_id": "ULID string",
      "description_ar": "string",
      "description_en": "string or null",
      "due_date": "ISO 8601 date",
      "responsible_user_id": "ULID string or null",
      "status": "string",
      "obligation_id": "ULID string - auto-created Obligation",
      "created_at": "ISO 8601 datetime"
    }
  }

Error Responses:
  401: { "error": "unauthorized" }
  403: { "error": "forbidden", "message": "Only attendees or the lead lawyer can add action items" }
  422: { "error": "unprocessable", "errors": { "description_ar": ["Description is required and must be at least 5 characters"] } }
  500: { "error": "server_error" }

### API-004: Update HearingActionItem

Endpoint: PUT /api/v1/hearing-action-items/{id}
Description: Updates an action item; status changes propagate to linked Obligation
Authentication: Bearer token (Laravel Sanctum)

Request Body:
  All fields from create are updatable; description_ar still required if provided.

Response (200): Same shape as create response

Error Responses: Same as create

### API-005: Delete HearingActionItem

Endpoint: DELETE /api/v1/hearing-action-items/{id}
Description: Soft-deletes action item; marks linked Obligation as waived
Authentication: Bearer token (Laravel Sanctum)

Response (204): No content

Error Responses:
  401, 403, 404, 500 as standard

---

## 5. CONTENT SPECIFICATION

### Hearing Session Outcome Tab

Page Title (EN): "Hearing Detail"
Page Title (AR): "تفاصيل الجلسة"

Section Header — Judge & Outcome (EN/AR): "Judge & Outcome" / "القاضي والنتيجة"
Section Header — Submissions (EN/AR): "Submissions" / "المرافعات"
Section Header — Attendance (EN/AR): "Attendance" / "الحضور"

Input Label: judge_statement_ar (EN/AR): "Judge Statement (Arabic)" / "تصريح القاضي"
Input Helper Text (EN/AR): "Capture what the judge said during this session." / "سجل ما قاله القاضي خلال هذه الجلسة"
Input Placeholder (EN/AR): "Example: The judge ordered both parties to submit final pleadings by..." / "مثال: أمر القاضي الطرفين بتقديم المرافعات الختامية بحلول..."

Input Label: judge_statement_en (EN/AR): "Judge Statement (English)" / "تصريح القاضي (إنجليزي)"
Input Helper Text (EN/AR): "Optional English translation for client reporting." / "ترجمة إنجليزية اختيارية للتقارير المرسلة للعميل"

Input Label: outcome_summary_ar (EN/AR): "Outcome Summary (Arabic)" / "ملخص النتيجة"
Input Label: outcome_summary_en (EN/AR): "Outcome Summary (English)" / "ملخص النتيجة (إنجليزي)"

Input Label: our_submissions_made (EN/AR): "Our Submissions" / "مرافعاتنا في الجلسة"
Input Label: opposing_submissions_made (EN/AR): "Opposing Submissions" / "مرافعات الطرف الآخر"

Input Label: session_attended_by (EN/AR): "Office attendees" / "الحضور من المكتب"

Button (Primary) (EN/AR): "Save Outcome" / "حفظ النتيجة"
Button (Secondary) (EN/AR): "Cancel" / "إلغاء"

Validation Error — Status (EN/AR): "Session outcome can only be recorded for hearings that have been held" / "لا يمكن تسجيل نتيجة جلسة لم تنعقد بعد"

Validation Error — Length (EN/AR): "Judge statement cannot exceed 50,000 characters" / "تصريح القاضي لا يمكن أن يتجاوز 50,000 حرف"

Success Message (EN/AR): "Session outcome saved" / "تم حفظ نتيجة الجلسة"

Empty State Title (EN/AR): "No session content yet" / "لا توجد بيانات للجلسة بعد"
Empty State Description (EN/AR): "Update hearing status to 'held' to record what happened." / "حدث حالة الجلسة إلى 'منعقدة' لتسجيل ما حدث"

Loading Text (EN/AR): "Saving..." / "جاري الحفظ..."

Tooltip — session_attended_by (EN/AR): "Only attendees and the lead lawyer can edit this hearing's outcome." / "فقط الحضور والمحامي الرئيسي يمكنهم تعديل نتيجة هذه الجلسة"

Laravel localization keys: `resources/lang/ar/hearings.php` and `resources/lang/en/hearings.php` — extend existing file with `session_outcome.*` keys.

### Sessions Timeline Tab

Page Title (EN/AR): "Sessions Timeline" / "تسلسل الجلسات"

Empty State Title (EN/AR): "No held sessions yet" / "لا توجد جلسات منعقدة بعد"
Empty State Description (EN/AR): "Once a hearing is held and outcome is recorded, it will appear here." / "ستظهر الجلسات هنا بعد انعقادها وتسجيل نتائجها"

Loading Text (EN/AR): "Loading sessions..." / "جاري تحميل الجلسات..."

Button — Load More (EN/AR): "Load more sessions" / "تحميل المزيد"

Laravel localization keys: `resources/lang/ar/sessions_timeline.php` and `resources/lang/en/sessions_timeline.php` — new files.

### Action Items Relation Manager

Section Header (EN/AR): "Required Submissions for Next Session" / "الإجراءات المطلوبة للجلسة القادمة"

Button (Primary) — Add (EN/AR): "Add Action Item" / "إضافة إجراء"
Button (Secondary) — Edit (EN/AR): "Edit" / "تعديل"
Button (Secondary) — Delete (EN/AR): "Delete" / "حذف"

Input Label — description_ar (EN/AR): "Description (Arabic)" / "الوصف"
Input Placeholder — description_ar (EN/AR): "Example: Submit witness list" / "مثال: تقديم قائمة الشهود"

Input Label — description_en (EN/AR): "Description (English)" / "الوصف (إنجليزي)"

Input Label — due_date (EN/AR): "Due Date" / "تاريخ الاستحقاق"
Input Helper — due_date (EN/AR): "When this action must be completed by." / "آخر موعد لإنجاز هذا الإجراء"

Input Label — responsible_user_id (EN/AR): "Responsible" / "المسؤول"

Input Label — status (EN/AR): "Status" / "الحالة"

Status Badges:
- pending (EN/AR): "Pending" / "قيد الانتظار"
- in_progress (EN/AR): "In Progress" / "قيد التنفيذ"
- completed (EN/AR): "Completed" / "مكتمل"
- waived (EN/AR): "Waived" / "ملغى"

Validation Error — description_ar required (EN/AR): "Description in Arabic is required" / "الوصف بالعربية مطلوب"
Validation Error — description_ar min length (EN/AR): "Description must be at least 5 characters" / "الوصف يجب أن يكون 5 أحرف على الأقل"
Validation Error — due_date past (EN/AR): "Due date cannot be in the past" / "تاريخ الاستحقاق لا يمكن أن يكون في الماضي"

Success Message — Created (EN/AR): "Action item added; obligation created automatically" / "تم إضافة الإجراء وإنشاء التزام تلقائياً"
Success Message — Updated (EN/AR): "Action item updated" / "تم تحديث الإجراء"
Success Message — Deleted (EN/AR): "Action item deleted; obligation waived" / "تم حذف الإجراء وإلغاء الالتزام"

Confirmation Dialog — Delete Title (EN/AR): "Delete this action item?" / "هل تريد حذف هذا الإجراء؟"
Confirmation Dialog — Delete Description (EN/AR): "The linked obligation will be marked as waived. This cannot be undone." / "سيتم تعليم الالتزام المرتبط كملغى. لا يمكن التراجع عن هذا الإجراء"

Laravel localization keys: `resources/lang/ar/hearing_action_items.php` and `resources/lang/en/hearing_action_items.php` — new files.

---

## 6. EDGE CASES & ERROR HANDLING

| Scenario | Trigger | Expected Behavior | UI Response |
|---|---|---|---|
| Session content save when status='scheduled' | User attempts PUT with judge_statement_ar but status is 'scheduled' | Validation error 422 via HearingRequest | Toast in current locale: "Session outcome can only be recorded for hearings that have been held" |
| Concurrent edit on Hearing | Two users edit same Hearing simultaneously | Optimistic lock via updated_at; 409 returned | Modal: "This hearing was modified by another user. Reload to see latest." with reload button |
| Action item due_date in past | User submits due_date < today | Validation warning, allowed for Admin/Owner roles, blocked for Member role | Yellow banner: "Due date is in the past. Confirm or change." (Admin/Owner) OR red error (Member) |
| HearingActionItem creation but Obligation creation fails | DB transaction rolls back | Both records absent | Toast: "Could not save action item. Please try again." |
| Sessions Timeline endpoint timeout (> 5s) | Slow query on Matter with 50+ Hearings | Pagination kicks in; default page returns | Skeleton cards shown during load; "Load more" appears |
| Empty session_attended_by + non-lead-lawyer tries to edit | Policy check | 403 returned | Toast: "Only attendees or the lead lawyer can edit this hearing's outcome." |
| RTL judge statement text overflow | Long AR text in narrow column | Word-wrap respects logical properties | Container scrolls vertically; horizontal scroll disabled |
| Action item with linked Obligation, user manually completes Obligation directly | Obligation marked complete via Obligation surface | HearingActionItem.status remains as-is (one-way sync) | UI shows mismatch indicator on action item card; user prompted to sync |
| HearingActionItem.status changed to 'waived' | Observer fires on update | Linked Obligation marked waived with reason "Action item waived by user" | No UI feedback needed beyond standard save confirmation |
| Hearing soft-deleted while it has action items | Cascade behavior | HearingActionItem records soft-deleted via observer; linked Obligations marked waived | Toast: "Hearing and {N} action items archived" |
| Bilingual character counter | User types >40000 chars in judge_statement_ar | Counter color changes yellow → red | Counter visible: "45,123 / 50,000" |
| Save on Hearing in non-editable status (cancelled) | User attempts to add session content to cancelled Hearing | Tab "Session Outcome" not visible (form schema condition) | Tab hidden; field never reachable from UI |
| Network failure mid-save | Save XHR fails | Form state preserved in browser memory; retry once on reconnect | Banner: "Connection lost. Will retry when online." |
| Filament locale mid-session change | User switches AR ↔ EN mid-form | Form rebinds with new locale labels; entered values preserved | Smooth transition; no data loss |

---

## 7. SIGN-OFF LOG

| Stakeholder | Role | Date | Method | Status |
|---|---|---|---|---|
| Abdullah | Founder | 2026-06-23 | This document | Approved |
| Khaldoun Khater | Informal advisor | Pending | Post-implementation walkthrough | Pending |
| Paid legal counsel | Formal compliance | N/A | Not required for this Flow (no compliance content) | N/A |
