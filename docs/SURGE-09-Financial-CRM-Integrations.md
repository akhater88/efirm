# SURGE-09 — Financial + CRM Modules

**Surge ID:** S-09
**Name:** Financial + CRM Modules
**Type:** BUILD Surge (post-MVP, breadth-pivot)
**Estimated duration:** 10–14 days (Claude Code-accelerated)
**Depends on:** SURGE-07 complete; SURGE-08 build complete (lawyer signoff for production not required to BUILD this surge)
**Enables:** SURGE-10 (production hardening + breadth launch)
**Pivot reference:** `decisions/D-09_breadth_pivot.md`

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | None |
| Legal domain | `[HARD-STOP-LAWYER-REQUIRED + CPA-REVIEW-REQUIRED]` for Trust Accounts (F-09.2). Other Flows lower-risk. |
| Sign-off | PENDING |

---

## Goal

Add the financial and CRM modules that complete the breadth coverage parity with HAQQ. Specifically: simplified Chart of Accounts, regulated Trust Accounts (with hard stop), Journal Entries, Client Invoicing, Receipts, and a basic CRM (Leads → Opportunities → Matter pipeline).

By the end of this Surge:

- A workspace has a Chart of Accounts (firm-level — not full GAAP, simplified)
- A workspace can run Trust Accounts (regulated — hard stop on production)
- Lawyers can post manual Journal Entries to accounts
- A workspace can invoice clients (Matter-linked, supports retainer drawdown from trust)
- A workspace can record received payments (Receipts)
- A workspace has a CRM Lead → Opportunity → Matter pipeline (pre-matter intake)

---

## Flows

### F-09.1 — Chart of Accounts (simplified)

**Goal:** Per-workspace Chart of Accounts. Simplified (not full GAAP — firm operating accounts + matter sub-ledgers).

**Scope:**

- `accounts` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `parent_account_id` ULID FK self-referential nullable (tree structure)
  - `code` VARCHAR(50) unique per workspace
  - `name_ar` VARCHAR(200)
  - `name_en` VARCHAR(200)
  - `account_type` ENUM('asset','liability','equity','income','expense','trust')
  - `is_active` BOOLEAN default true
  - `currency` CHAR(3) default 'USD'
  - `notes` TEXT nullable
  - `is_system` BOOLEAN default false (system accounts cannot be deleted)
  - audit timestamps + soft deletes + audit users
  - composite unique `(workspace_id, code)`

- `Account` model with `BelongsToWorkspace`, parent/child tree relations
- Default seeded accounts (system, per workspace on creation): "Cash on Hand", "Bank Account", "Accounts Receivable", "Office Supplies (Expense)", "Professional Fees Income", "Retainer Liability", "Trust Account" (special type)
- `AccountPolicy`: Admin/Owner manage; Member view only
- Filament resource with tree-view (Filament v5 supports nested set displays)

**`[CPA-REVIEW-RECOMMENDED]`:** the default seeded chart structure. A Levant CPA should validate it matches local accounting conventions.

**API:** Full CRUD + tree query

**Acceptance:**
- Pest: account CRUD with parent/child nesting up to 4 levels deep
- Pest: cannot delete system accounts (returns 422)
- Pest: workspace isolation
- Pest: code uniqueness enforced per workspace

---

### F-09.2 — Trust Accounts `[HARD-STOP-LAWYER-REQUIRED + CPA-REVIEW-REQUIRED]`

**Goal:** Regulated trust account handling — separate from operating accounts. Each Trust Account is per-Client with an append-only ledger.

> **HARD STOP:** Trust account handling is bar-association regulated in every Levant jurisdiction. Mistakes can result in firm discipline / disbarment. The Engineer agent MUST refuse production deployment of this Flow without:
> 1. Lawyer signoff on the model
> 2. CPA signoff on the ledger logic
> 3. A test suite that has been independently reviewed for regulatory compliance

**Scope:**

- `trust_accounts` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `client_contact_id` ULID FK (must be `is_client=true`)
  - `account_number` VARCHAR(100) (firm's bank trust account reference)
  - `bank_name` VARCHAR(200) nullable
  - `currency` CHAR(3)
  - `current_balance` DECIMAL(15,2) (computed, cached from ledger sum)
  - `is_active` BOOLEAN default true
  - audit timestamps + soft deletes + audit users
  - composite index `(workspace_id, client_contact_id)`

- `trust_ledger_entries` table — **APPEND-ONLY** (no UPDATE, no DELETE via app; DB-level trigger enforces):
  - `id` ULID
  - `workspace_id` ULID FK
  - `trust_account_id` ULID FK
  - `entry_date` DATE
  - `entry_type` ENUM('deposit','withdrawal','retainer_applied','refund','interest','adjustment')
  - `amount` DECIMAL(15,2) — positive numbers always; type defines direction
  - `balance_after` DECIMAL(15,2) — computed snapshot at entry time
  - `matter_id` ULID FK nullable (which matter this relates to)
  - `related_invoice_id` ULID FK nullable (when entry_type = 'retainer_applied')
  - `description` TEXT
  - `reference_number` VARCHAR(100) nullable (bank reference, cheque number)
  - `created_by_user_id` ULID FK (audit — who entered this)
  - `created_at` TIMESTAMP
  - **NO updated_at, NO deleted_at** (append-only)
  - composite index `(trust_account_id, entry_date)`

- `TrustAccount` and `TrustLedgerEntry` models
- DB trigger or model lifecycle hook **prevents UPDATE and DELETE** on `trust_ledger_entries`
- Corrections require offsetting entry (entry_type='adjustment')
- Filament resource (Admin/Owner only — Members cannot access trust accounts)
- Reconciliation report: monthly bank statement vs ledger balance

**`[HARD-STOP-LAWYER-REQUIRED]`** items:
- Bar association requirements per jurisdiction (Jordan / Lebanon / Iraq / Palestine differ on trust handling)
- Whether interest on trust accounts must accrue to client or to firm (varies)
- Required reporting frequency
- Whether commingling rules require separate physical bank accounts per client (most do)

**Acceptance (build):**
- Pest: deposit entry increases balance
- Pest: withdrawal entry decreases balance
- Pest: cannot UPDATE existing ledger entry (returns 405)
- Pest: cannot DELETE existing ledger entry (returns 405)
- Pest: adjustment entry pattern works as correction mechanism
- Pest: workspace isolation
- Pest: Member role cannot access trust accounts (Policy denies)

**Acceptance (production deployment):**
- [ ] Lawyer signoff at `validation/09_trust_account_lawyer_signoff.md`
- [ ] CPA review at `validation/09_trust_account_cpa_signoff.md`
- [ ] Reconciliation report verified against a real bank statement

---

### F-09.3 — Journal Entries

**Goal:** Manual double-entry journal posts against the Chart of Accounts.

**Scope:**

- `journal_entries` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `entry_date` DATE
  - `reference_number` VARCHAR(100) nullable
  - `description` TEXT
  - `matter_id` ULID FK nullable (optional matter linkage)
  - `posted` BOOLEAN default false
  - `posted_at` TIMESTAMP nullable
  - `posted_by_user_id` ULID FK nullable
  - audit timestamps + soft deletes + audit users

- `journal_entry_lines` table:
  - `id` ULID
  - `journal_entry_id` ULID FK
  - `account_id` ULID FK
  - `debit` DECIMAL(15,2) default 0
  - `credit` DECIMAL(15,2) default 0
  - `description` TEXT nullable
  - CHECK constraint: exactly one of debit or credit is non-zero
  - index `(journal_entry_id)`

- `JournalEntry` and `JournalEntryLine` models
- Service: `JournalEntryService::post($entry)` — validates that sum(debits) = sum(credits) before marking posted
- Filament resource with custom form (line-by-line debit/credit grid)
- Once posted, journal entry becomes effectively immutable (reverse via offsetting entry)

**Acceptance:**
- Pest: cannot post unbalanced entry (debits ≠ credits)
- Pest: posted entries can be reversed via new offsetting entry
- Pest: workspace isolation

---

### F-09.4 — Client Invoicing

**Goal:** Firm bills clients. Invoices link to Matters. Support retainer drawdown from trust accounts (when F-09.2 production-deployed).

**Scope:**

- `invoices` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `invoice_number` VARCHAR(100) (auto-generated, workspace-scoped sequence)
  - `client_contact_id` ULID FK
  - `matter_id` ULID FK nullable (one invoice can span multiple matters; matter linkage optional)
  - `issue_date` DATE
  - `due_date` DATE
  - `currency` CHAR(3)
  - `subtotal` DECIMAL(15,2)
  - `tax_amount` DECIMAL(15,2) default 0
  - `total_amount` DECIMAL(15,2)
  - `paid_amount` DECIMAL(15,2) default 0
  - `outstanding_amount` DECIMAL(15,2) (computed)
  - `status` ENUM('draft','sent','viewed','partially_paid','paid','overdue','cancelled','void')
  - `notes` TEXT nullable
  - `pdf_document_id` ULID FK nullable (the generated PDF)
  - audit timestamps + soft deletes + audit users
  - composite unique `(workspace_id, invoice_number)`

- `invoice_lines` table:
  - `id` ULID
  - `invoice_id` ULID FK
  - `description` TEXT
  - `quantity` DECIMAL(10,2)
  - `unit_price` DECIMAL(15,2)
  - `line_total` DECIMAL(15,2)
  - `time_entry_id` ULID FK nullable (link to F-07.2 time entry — bills time)
  - `matter_id` ULID FK nullable
  - `sort_order` INT

- `Invoice` and `InvoiceLine` models with `BelongsToWorkspace`
- Service: `InvoiceService::generatePdf($invoice)` — produces PDF with firm letterhead, AR/EN as required
- Service: `InvoiceService::applyRetainerDrawdown($invoice, $trustAccount, $amount)` — applies trust funds to invoice; creates trust ledger entry; updates invoice paid_amount
- Filament resource
- Email mailable: send invoice to client (PDF attached)

**Acceptance:**
- Pest: invoice CRUD + auto-numbering
- Pest: invoice totals computed correctly
- Pest: PDF generation produces valid file
- Pest: trust drawdown updates both invoice + trust ledger atomically
- Pest: workspace isolation

---

### F-09.5 — Receipts

**Goal:** Track client payments received. Apply to invoices.

**Scope:**

- `receipts` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `client_contact_id` ULID FK
  - `receipt_number` VARCHAR(100) auto-generated
  - `receipt_date` DATE
  - `amount` DECIMAL(15,2)
  - `currency` CHAR(3)
  - `payment_method` ENUM('cash','bank_transfer','cheque','card','online','other')
  - `reference_number` VARCHAR(100) nullable (cheque #, transaction ref)
  - `applied_to_invoice_id` ULID FK nullable
  - `deposited_to_account_id` ULID FK (Chart of Accounts — could be trust or operating)
  - `notes` TEXT nullable
  - audit timestamps + soft deletes + audit users

- `Receipt` model with `BelongsToWorkspace`
- When applied to invoice: updates `invoice.paid_amount`; if fully paid, status → 'paid'
- When deposited to trust account: creates `trust_ledger_entries` entry
- Filament resource

**Acceptance:**
- Pest: receipt CRUD
- Pest: applying to invoice updates invoice correctly
- Pest: depositing to trust creates ledger entry
- Pest: workspace isolation

---

### F-09.6 — CRM: Leads + Pipeline + Opportunities

**Goal:** Pre-Matter intake. A Lead is contact information from someone interested in becoming a client. A Pipeline organizes Leads. An Opportunity is a specific deal. Closed-won Opportunity converts to a Matter.

**Scope:**

- `pipelines` table (configurable per workspace):
  - `id` ULID
  - `workspace_id` ULID FK
  - `name` VARCHAR(100)
  - `is_default` BOOLEAN default false
  - `stages` JSON (array of stage objects: `{name, win_probability, sort_order}`)
  - audit timestamps + soft deletes

- `leads` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `pipeline_id` ULID FK
  - `current_stage` VARCHAR(100) (matches one of pipeline.stages)
  - `name` VARCHAR(200)
  - `email` VARCHAR(255) nullable
  - `phone` VARCHAR(50) nullable
  - `company_name` VARCHAR(200) nullable
  - `source` ENUM('referral','website','event','cold_outreach','existing_client','other')
  - `source_details` VARCHAR(255) nullable
  - `owner_user_id` ULID FK
  - `notes` TEXT nullable
  - `is_qualified` BOOLEAN default false
  - `status` ENUM('open','contacted','qualified','converted','lost')
  - `converted_to_contact_id` ULID FK nullable (on conversion)
  - audit timestamps + soft deletes + audit users

- `opportunities` table:
  - `id` ULID
  - `workspace_id` ULID FK
  - `lead_id` ULID FK
  - `title` VARCHAR(200)
  - `expected_matter_type` ENUM matches Matter.practice_area
  - `expected_value` DECIMAL(15,2) nullable
  - `currency` CHAR(3) nullable
  - `expected_close_date` DATE nullable
  - `win_probability` INT (0-100) — defaults from pipeline stage
  - `status` ENUM('open','won','lost','withdrawn')
  - `won_at` TIMESTAMP nullable
  - `converted_to_matter_id` ULID FK nullable (set on win → matter conversion)
  - audit timestamps + soft deletes + audit users

- Service: `LeadConversionService::convertToContact($lead)` — creates a Contact from Lead data
- Service: `OpportunityConversionService::convertToMatter($opportunity)` — creates a Matter from Opportunity data + the converted Contact
- Filament resources for all three
- Kanban view for Leads (by stage)
- Dashboard widget: pipeline rollup (sum of expected_value × win_probability per stage)

**Acceptance:**
- Pest: full lead → opportunity → matter conversion chain works
- Pest: converted entities preserve references (Lead remembers it converted to which Contact)
- Pest: workspace isolation
- Pest: closed-won opportunity creates a Matter with correct data
- Filament: kanban renders correctly in RTL (columns flow RTL)

---

## Surge acceptance criteria

- [ ] F-09.1 through F-09.6 all built and tested
- [ ] Trust Accounts (F-09.2) `[HARD-STOP]` items documented in `validation/09_financial_lawyer_signoff_pending.md`
- [ ] All Pest tests green
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated (~40 new endpoints)
- [ ] No regression in S-01 through S-08 tests
- [ ] Founder sign-off on BUILD (production deployment of trust accounts requires additional gates)

---

## Out of scope

- Multi-currency accounting (single-currency per account at MVP; FX conversion Year-2)
- Tax filing exports (Year-2 — varies per jurisdiction)
- Automated bank reconciliation via API (Year-2)
- Expense reports / employee reimbursements (Year-2)
- Vendor accounts payable (Year-2)
- Recurring invoice templates (Year-2)
- Email marketing / CRM email blasts (Year-2)
- Lead enrichment via external APIs (Year-2)
- Conflict-of-interest checking on lead conversion (Year-2)

---

## What the Software Engineer agent should produce

1. **F-09.2 Trust Accounts is the highest-risk module in the entire product.** TTPs for this Flow must include explicit append-only enforcement at multiple layers (DB trigger, model lifecycle hook, Policy denying update/delete, Pest tests verifying all three).
2. **Idempotency on financial mutations.** Every invoice payment, every retainer drawdown — these must be idempotent at the API layer (use Idempotency-Key headers). Tests must verify replaying a request does NOT double-post.
3. **Audit trail completeness.** Every financial mutation logs to `ai_interactions`-style audit table (separate `financial_audit_log` table — append-only, never purged).
4. **Decimal precision throughout.** No floating-point arithmetic on money. Use PHP `bcmath` or Laravel's casted `decimal` types.
5. **F-09.4 PDF generation:** use a battle-tested library (Spatie's `laravel-pdf` or `dompdf`). Test PDF outputs for both AR and EN templates with realistic amounts and Arabic client names.
