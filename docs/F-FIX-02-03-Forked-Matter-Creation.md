# F-FIX-02.3 — Forked Matter Creation Workflow

**Flow ID:** F-FIX-02.3
**Surge:** S-FIX-02
**Estimated effort:** ~2 days (multi-Wave)
**Source:** `validation/02_advisor_meeting_log.md` Conversation 3.5, Decision #26

## Khaldoun's framing (verbatim)

> "HAQQ built a generic, one-size-fits-all form. But in a real firm, M&A or Corporate Governance are transactional, not litigation. They don't have judges! When a user clicks '+ Create Matter' in your platform, give them a simple, high-level choice first: 1) Transactional/Corporate Matter (Drafting, Advisory, M&A) 2) Litigation/Dispute Matter (Court cases, Arbitration). If they select Litigation, then you reveal the fields for Court Type, Court Case Number, Judge Name, and the Opposition's Counsel. If they select Transactional, you hide all the court fields and show things like 'Target Closing Date' and 'Document Type.'"

## Goal

Replace the current flat Matter creation form (where all fields appear with conditional show/hide on is_litigation toggle) with a stepped wizard that forks the user into Transactional or Litigation track at the first step. Each track shows only its relevant fields. Eliminates the cognitive friction Khaldoun identified in HAQQ's design.

## Scope

### Schema (additive, backward-compatible)

Migration `add_matter_type_to_matters`:

```php
Schema::table('matters', function (Blueprint $table) {
    $table->string('matter_type', 50)->nullable()->after('is_litigation');
    $table->date('target_closing_date')->nullable();
    $table->string('deal_value_currency', 3)->nullable();
    $table->decimal('deal_value_amount', 15, 2)->nullable();
    $table->json('expected_document_types')->nullable(); // for transactional matters
    
    $table->index(['workspace_id', 'matter_type']);
});
```

### PHP Enum

`app/Enums/MatterTypeEnum.php`:

```php
enum MatterTypeEnum: string {
    // Transactional
    case CommercialContracts = 'commercial_contracts';
    case MnA = 'mna';
    case CorporateGovernance = 'corporate_governance';
    case Securities = 'securities';
    case GeneralCounsel = 'general_counsel';
    case Advisory = 'advisory';
    case RealEstateTransaction = 'real_estate_transaction';
    case EmploymentDrafting = 'employment_drafting';
    
    // Litigation
    case CommercialLitigation = 'commercial_litigation';
    case CivilLitigation = 'civil_litigation';
    case Enforcement = 'enforcement';
    case Arbitration = 'arbitration';
    case LaborDispute = 'labor_dispute';
    case AdministrativeDispute = 'administrative_dispute';
    
    public function isTransactional(): bool {
        return in_array($this, [
            self::CommercialContracts, self::MnA, self::CorporateGovernance,
            self::Securities, self::GeneralCounsel, self::Advisory,
            self::RealEstateTransaction, self::EmploymentDrafting,
        ]);
    }
    
    public function isLitigation(): bool {
        return !$this->isTransactional();
    }
    
    public function track(): string {
        return $this->isLitigation() ? 'litigation' : 'transactional';
    }
}
```

`[PROVISIONAL-ADVISOR-DECIDED]` — subtype list validated by Khaldoun before merge. Comment in enum docblock.

### Backfill migration

`backfill_matter_type_from_is_litigation`:
- For existing Matters where `matter_type IS NULL`:
  - If `is_litigation = true` → set `matter_type = 'commercial_litigation'` (safest default)
  - If `is_litigation = false` → set `matter_type = 'commercial_contracts'` (safest default)
- Audit CSV produced at `validation/fix-02_matter_type_backfill_audit.csv` listing every Matter assigned a default, flagged for founder review
- Do NOT auto-correct flagged rows; humans review and update via Filament UI

### Models

Update `app/Models/Matter.php`:
- Add cast: `'matter_type' => MatterTypeEnum::class`
- Add cast: `'expected_document_types' => 'array'`
- Add cast: `'deal_value_amount' => 'decimal:2'`
- Scope `transactional()` — filters where matter_type isTransactional
- Scope `litigation()` — filters where matter_type isLitigation
- Override `is_litigation` accessor to derive from matter_type (with fallback to legacy column for old rows during transition)

### Filament Resource — Wizard

Update `app/Filament/Resources/MatterResource.php` create page to use Filament's `Wizard` component:

```php
// Pseudocode structure:
Wizard::make([
    Step::make('Track Selection')
        ->schema([
            Radio::make('track')
                ->options([
                    'transactional' => 'Transactional / Corporate Matter',
                    'litigation' => 'Litigation / Dispute Matter',
                ])
                ->descriptions([
                    'transactional' => 'Drafting, Advisory, M&A, Corporate Governance',
                    'litigation' => 'Court cases, Arbitration, Enforcement',
                ])
                ->required()
                ->live(),
        ]),
    Step::make('Subtype')
        ->schema([
            Select::make('matter_type')
                ->options(fn (Get $get) => MatterTypeEnum::cases()
                    ->filter(fn ($e) => $e->track() === $get('track'))
                    ->mapWithKeys(fn ($e) => [$e->value => __("matter_types.{$e->value}")]))
                ->required(),
        ]),
    Step::make('Basic Information')
        ->schema([
            TextInput::make('title')->required()->maxLength(255),
            Select::make('client_contact_id')->relationship('clientContact', 'name_ar')->required(),
            DatePicker::make('opened_at')->default(now()),
            Textarea::make('description')->maxLength(5000),
        ]),
    Step::make('Track-Specific Fields')
        ->schema(fn (Get $get) => $get('track') === 'transactional'
            ? [
                DatePicker::make('target_closing_date'),
                Select::make('deal_value_currency')->options(['JOD', 'USD', 'EUR']),
                TextInput::make('deal_value_amount')->numeric(),
                CheckboxList::make('expected_document_types')->options([...]),
                Select::make('governing_law'),
            ]
            : [
                Select::make('court_id')->relationship('court', 'name_ar'),
                TextInput::make('case_number'),
                Select::make('judge_id')->relationship('judge', 'name_ar'),
                Select::make('opposing_counsel_id')->relationship('opposingCounsel', 'name_ar'),
                Select::make('representation_role'),
                DatePicker::make('initial_hearing_date'),
            ]
        ),
    Step::make('Team Assignment')
        ->schema([
            Select::make('lead_lawyer_id')->relationship('leadLawyer', 'name')->required(),
            Select::make('supporting_lawyer_ids')->multiple()->relationship('supportingLawyers', 'name'),
        ]),
])
->submitAction(...)
```

Edit page (post-creation) does NOT use wizard — uses standard form with all relevant fields visible based on persisted `matter_type`.

### Filament Resource — List page

Add filter:
```php
SelectFilter::make('track')
    ->options(['transactional' => __('matters.track.transactional'), 'litigation' => __('matters.track.litigation')])
    ->query(fn ($q, array $data) => /* filter by matter_type->track() */)
```

Add column:
```php
TextColumn::make('matter_type')
    ->badge()
    ->color(fn ($state) => $state?->isLitigation() ? 'warning' : 'info')
```

### API

Update `POST /api/v1/matters`:
- Accepts `matter_type` (required if creating; optional for backward compat with legacy clients passing `is_litigation` boolean)
- Server-side validation via FormRequest `StoreMatterRequest`:
  - If `matter_type->isTransactional()`, fields `court_id`, `case_number`, `judge_id`, `opposing_counsel_id` MUST NOT be present (422 if supplied)
  - If `matter_type->isLitigation()`, fields `target_closing_date`, `deal_value_amount`, `expected_document_types` MUST NOT be present (422 if supplied)
  - Legacy clients passing `is_litigation` boolean get auto-derived `matter_type` with deprecation warning header in response

New endpoint: `GET /api/v1/matters/types`:
- Returns enum cases grouped by track with bilingual labels
- Supports the wizard's Step 2 dropdown population

OpenAPI spec updated.

### Tests (minimum 10 Pest)

Located at `tests/Feature/Matters/ForkedCreationTest.php`:

1. `wizard_step_1_renders_both_track_cards_in_ar_locale`
2. `wizard_step_1_renders_both_track_cards_in_en_locale`
3. `wizard_step_4_shows_transactional_fields_when_track_is_transactional`
4. `wizard_step_4_shows_litigation_fields_when_track_is_litigation`
5. `api_post_matter_with_mna_type_succeeds_without_court_fields`
6. `api_post_matter_with_mna_type_and_judge_id_returns_422`
7. `api_post_matter_with_commercial_litigation_requires_court_id`
8. `legacy_api_post_with_is_litigation_boolean_succeeds_with_deprecation_warning`
9. `backfill_migration_populates_matter_type_with_audit_csv_for_ambiguous_rows`
10. `matter_types_endpoint_returns_grouped_enum_with_localized_labels`

### Content Specification

| Element | AR | EN |
|---|---|---|
| Wizard title | إنشاء ملف جديد | New Matter |
| Step 1 title | اختر نوع الملف | Choose Matter Type |
| Track card 1 title | ملف تجاري / تعاقدي | Transactional / Corporate Matter |
| Track card 1 desc | صياغة العقود، استشارات، اندماج واستحواذ، حوكمة الشركات | Drafting, Advisory, M&A, Corporate Governance |
| Track card 2 title | ملف تقاضي / نزاع | Litigation / Dispute Matter |
| Track card 2 desc | قضايا المحاكم، التحكيم، التنفيذ | Court cases, Arbitration, Enforcement |
| Step 2 title | الفئة الفرعية | Matter Subtype |
| Step 3 title | معلومات أساسية | Basic Information |
| Step 4 title (transactional) | تفاصيل المعاملة | Transaction Details |
| Step 4 title (litigation) | تفاصيل القضية | Litigation Details |
| Step 5 title | فريق العمل | Team Assignment |
| Field — target_closing_date | تاريخ الإغلاق المستهدف | Target Closing Date |
| Field — deal_value | قيمة الصفقة | Deal Value |
| Field — expected_document_types | أنواع المستندات المتوقعة | Expected Document Types |
| Field — governing_law | القانون الحاكم | Governing Law |
| Field — court | المحكمة | Court |
| Field — case_number | رقم القضية | Case Number |
| Field — judge | القاضي | Judge |
| Field — opposing_counsel | محامي الخصم | Opposing Counsel |
| Field — representation_role | دور التمثيل | Representation Role |
| Field — initial_hearing_date | تاريخ الجلسة الأولى | Initial Hearing Date |
| Back button | السابق | Back |
| Next button | التالي | Next |
| Create button | إنشاء الملف | Create Matter |
| Filter chip — Track | المسار | Track |
| Filter option All | الكل | All |
| Validation error — litigation fields on transactional | لا يمكن إضافة معلومات المحكمة لملف تعاقدي | Court information cannot be added to a transactional matter |
| Validation error — missing court | يجب تحديد المحكمة لملف تقاضي | Court is required for a litigation matter |
| Deprecation warning header | يرجى استخدام matter_type بدلاً من is_litigation | Please use matter_type instead of is_litigation |

Matter type subtypes (AR + EN):
- commercial_contracts: العقود التجارية / Commercial Contracts
- mna: اندماج واستحواذ / M&A
- corporate_governance: حوكمة الشركات / Corporate Governance
- securities: الأوراق المالية / Securities
- general_counsel: مستشار عام / General Counsel
- advisory: استشارات / Advisory
- real_estate_transaction: معاملة عقارية / Real Estate Transaction
- employment_drafting: صياغة عقود عمل / Employment Drafting
- commercial_litigation: قضية تجارية / Commercial Litigation
- civil_litigation: قضية مدنية / Civil Litigation
- enforcement: تنفيذ / Enforcement
- arbitration: تحكيم / Arbitration
- labor_dispute: نزاع عمالي / Labor Dispute
- administrative_dispute: نزاع إداري / Administrative Dispute

Laravel localization keys: extend `resources/lang/{ar,en}/matters.php` + new `resources/lang/{ar,en}/matter_types.php`.

## Wave decomposition

| Wave | Scope | Effort |
|---|---|---|
| W1 | Migration + MatterTypeEnum + Model updates + Backfill | 4h |
| W2 | API FormRequest + endpoints + OpenAPI | 4h |
| W3 | Filament Wizard + List filter + Edit page | 6h |
| W4 | Pest tests + verification | 3h |

## Acceptance criteria

- [ ] Wizard renders correctly in both AR and EN locales
- [ ] API rejects field/track mismatches with 422
- [ ] Backfill audit CSV produced for ambiguous legacy rows
- [ ] All 10 Pest tests pass
- [ ] Legacy `is_litigation` API requests still work with deprecation warning
- [ ] Code attribution cites Decision #26

## Code attribution comment

```php
/**
 * Forked Matter creation workflow per advisor input from Khaldoun Khater,
 * validation/02_advisor_meeting_log.md Conversation 3.5, Decision #26.
 *
 * Khaldoun's identification of HAQQ's weakness: their one-size-fits-all
 * form forces litigation fields onto transactional Matter types.
 * This Flow eliminates that friction.
 */
```

## Edge cases

| Scenario | Expected behavior |
|---|---|
| User navigates back from Step 4 to Step 1 and changes track | Confirmation modal: "Switching tracks will clear track-specific fields. Continue?" |
| Pre-migration Matter (NULL matter_type) opened in edit | Banner: "Choose a track to enable proper field handling" + track picker |
| Concurrent edit of matter_type | Optimistic locking via updated_at; 409 with reload option |
| Subtype enum value not in track | Validation error: "Subtype does not match selected track" |
| Transactional Matter has hearings (data inconsistency) | Allow but flag in audit log; surface in admin dashboard |
