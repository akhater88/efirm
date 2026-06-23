# F-FIX-02.6 — Case Management Navigation Refinement

**Flow ID:** F-FIX-02.6
**Surge:** S-FIX-02
**Estimated effort:** ~0.5 day (single-Wave)
**Source:** `validation/02_advisor_meeting_log.md` Conversation 3.5, Decision #27
**Wave-Ready Package needed:** No (single Wave; specs fit in this Flow file)

## Khaldoun's framing (verbatim)

> "Under Case Management, you absolutely must replicate these four distinct sub-features in your Laravel routes:
> - Legal Matters (الملفات القانونية)
> - Hearings (الجلسات)
> - Court Reviews (مراجعات المحكمة)
> - Service Log (سجل التبليغات)"

## Goal

Refine the Filament navigation structure so the Case Management section appears as a coherent group with these four sub-features clearly grouped. Currently they may be scattered across "Matters" / "Practice" / "Litigation" sidebar groups (per the handoff report); this Flow consolidates them under a unified "Case Management" group with consistent ordering, bilingual labels, and proper icons.

## Scope

### No schema changes

This Flow is purely Filament resource configuration. All entities (Matter, Hearing, CourtReview, ServiceLogEntry) already exist.

### Filament Resource updates

For each of the 4 resources, update navigation properties:

`app/Filament/Resources/MatterResource.php`:
```php
protected static ?string $navigationGroup = 'Case Management';
protected static ?int $navigationSort = 1;
protected static ?string $navigationIcon = 'heroicon-o-folder';
protected static ?string $navigationLabel = ''; // Resolved via getNavigationLabel() for locale

public static function getNavigationLabel(): string {
    return __('navigation.case_management.matters');
}
```

`app/Filament/Resources/HearingResource.php`:
```php
protected static ?string $navigationGroup = 'Case Management';
protected static ?int $navigationSort = 2;
protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

public static function getNavigationLabel(): string {
    return __('navigation.case_management.hearings');
}
```

`app/Filament/Resources/CourtReviewResource.php`:
```php
protected static ?string $navigationGroup = 'Case Management';
protected static ?int $navigationSort = 3;
protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

public static function getNavigationLabel(): string {
    return __('navigation.case_management.court_reviews');
}
```

`app/Filament/Resources/ServiceLogEntryResource.php`:
```php
protected static ?string $navigationGroup = 'Case Management';
protected static ?int $navigationSort = 4;
protected static ?string $navigationIcon = 'heroicon-o-document-check';

public static function getNavigationLabel(): string {
    return __('navigation.case_management.service_log');
}
```

### Filament Panel Provider update

`app/Providers/Filament/AppPanelProvider.php` (or equivalent):

```php
->navigationGroups([
    NavigationGroup::make()
        ->label(fn () => __('navigation.groups.case_management'))
        ->icon('heroicon-o-briefcase')
        ->collapsed(false),
    // Other existing groups (Contacts, Documents, Financial, etc.) preserved
])
```

Group ordering convention: Contacts → Case Management → Documents → Practice → Financial → Settings (existing groups preserved; Case Management slots in second after Contacts).

### Content Specification

Add to `resources/lang/en/navigation.php`:
```php
return [
    'groups' => [
        'case_management' => 'Case Management',
        // existing groups preserved
    ],
    'case_management' => [
        'matters' => 'Legal Matters',
        'hearings' => 'Hearings',
        'court_reviews' => 'Court Reviews',
        'service_log' => 'Service Log',
    ],
];
```

Add to `resources/lang/ar/navigation.php`:
```php
return [
    'groups' => [
        'case_management' => 'إدارة القضايا',
    ],
    'case_management' => [
        'matters' => 'الملفات القانونية',
        'hearings' => 'الجلسات',
        'court_reviews' => 'مراجعات المحكمة',
        'service_log' => 'سجل التبليغات',
    ],
];
```

### Tests (minimum 4 Pest)

Located at `tests/Feature/Filament/CaseManagementNavigationTest.php`:

1. `case_management_group_appears_in_filament_navigation_in_en_locale`
2. `case_management_group_appears_in_filament_navigation_in_ar_locale`
3. `four_resources_appear_under_case_management_in_correct_sort_order`
4. `navigation_labels_render_in_user_locale`

## Acceptance criteria

- [ ] Four resources grouped under "Case Management" in Filament sidebar
- [ ] Bilingual labels (AR + EN) render correctly per user locale
- [ ] Sort order: Legal Matters (1) → Hearings (2) → Court Reviews (3) → Service Log (4)
- [ ] Group icon matches navigation pattern
- [ ] All 4 Pest tests pass
- [ ] Code attribution cites Decision #27

## Code attribution comment

```php
/**
 * Case Management navigation taxonomy per advisor input from Khaldoun
 * Khater, validation/02_advisor_meeting_log.md Conversation 3.5,
 * Decision #27.
 *
 * Khaldoun's framing: four distinct sub-features (Matters, Hearings,
 * Court Reviews, Service Log) must be grouped together. This matches
 * how Levant commercial firms organize their case work.
 */
```

## Edge cases

| Scenario | Expected behavior |
|---|---|
| Existing user with bookmarked URLs to specific resources | URLs unchanged (only navigation grouping changes); bookmarks continue to work |
| User in locale=en switches to locale=ar mid-session | Navigation rebuilds with AR labels on next page load |
| New resource added later that should be in Case Management | Pattern documented for future additions: set $navigationGroup = 'Case Management' |
| Workspace member without policy access to some resources | Those resources hidden from navigation per existing Filament behavior; group still renders for accessible resources |
| Mobile viewport — collapsed sidebar | Case Management appears as accordion section; preserves grouping |

## Open items

- Should there be a unified "Case Management Dashboard" landing page that summarizes activity across all 4 sub-features? (Defer to feature backlog if Khaldoun expresses interest.)
