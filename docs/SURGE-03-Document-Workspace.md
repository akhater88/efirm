# SURGE-03 — Document Workspace

**Surge ID:** S-03
**Name:** Document Workspace
**Type:** BUILD Surge
**Estimated duration:** 8–12 days (with Claude Code); 2.5–3.5 weeks (manual)
**Depends on:** S-02 complete; D-02 (editor library) decided; D-04 (storage) decided; D-05 (diff algorithm) decided
**Enables:** S-04 (AI inline needs the document workspace to live in)

---

## Status flags

| Flag | Value |
|---|---|
| Wedge dependency | This Surge IS the wedge surface. Wedge-agnostic at structural level; **depth** of bilingual handling is `[PROVISIONAL]` until customer interviews + AI test results |
| Legal domain | `[PENDING-LEGAL-REVIEW]` for version-retention policy and document-deletion rules |
| Sign-off | PENDING — Founder + Legal Advisor + at least 1 pilot firm (per S-00 F-00.5) |

---

## Goal

Make the document the workspace. This is the single most important Surge in the product — it is where every Vol. 5 / Vol. 12 / Vol. 14 gap in HAQQ becomes our positioned advantage made physical.

By the end of this Surge:
- A lawyer can import a `.docx` contract into a Matter
- The contract opens in an in-browser editor (no SharePoint/Word punt — closes Vol. 12 finding)
- The editor handles mixed AR/EN content with correct RTL/LTR direction per paragraph
- Every save creates a new version
- Two versions can be visually diff'd / compared
- The contract can be exported back to `.docx` with formatting + structure preserved (the non-negotiable per Vol. 12)
- Clauses are addressable structural objects within the document (not just paragraphs — this enables S-04)
- A document can be shared with a counterparty via a tokenized link (download-only)
- The document detail surface lives INSIDE the Matter (one workspace, not HAQQ's two-app split)

The single hardest test of this Surge: a lawyer imports a real bilingual NDA from Word, edits a clause, exports back, opens in Word, and **sees their formatting intact**. If that round-trip is lossy, this Surge has failed and S-04 cannot ship on top of it.

---

## Flows

### F-03.1 — Spike: .docx round-trip + editor library decision (D-02, D-04)

**Goal:** Before building anything customer-facing, validate that the chosen editor + storage stack can preserve real-world contracts in a round-trip without losing formatting. This is a **time-boxed spike** (max 3 days). If it fails, the Surge gets re-planned.

**Scope:**
- Evaluate three editor candidates against the same 5 real Levant bilingual contracts (sourced via lawyer advisor):
  - **TipTap 2 (ProseMirror-based)** — has a `tiptap-docx` ecosystem; strong AR/RTL via ProseMirror
  - **CKEditor 5** — best-in-class .docx import/export (commercial license required)
  - **Editor.js** — block-based, modern UX, but weaker .docx interop
- For each candidate, build a throwaway test harness:
  1. Import contract.docx → editor JSON state
  2. Render in browser; check RTL handling, embedded tables, footnotes, comments, tracked changes
  3. Export editor JSON → contract.exported.docx
  4. Open exported file in Microsoft Word, LibreOffice, and Google Docs; compare visually + structurally
- Score each on: fidelity, AR/RTL support, license cost, complexity, AI integration ergonomics for S-04
- Pick a winner; document the decision in `decisions/D-02.md`

**Storage decision (D-04):**
- Document body: editor JSON in a `documents.body` LONGTEXT or JSON column
- Imported/exported binary: `.docx` blobs in S3-compatible object storage (Cloudways adjacent)
- Decision documented in `decisions/D-04.md`

**Entities touched:** None (spike output is a decision, not a migration).
**API surface:** None.
**UI surface:** Throwaway test pages only.

**Acceptance criteria:**
- Decision document committed
- Test fixtures (5 real bilingual contracts, anonymized) committed to a sealed test directory
- A go/no-go signal: round-trip is acceptable, OR Surge gets re-planned

**Dependencies:** S-02 complete; F-00.5 wireframes available; lawyer advisor for test fixtures.

**Failure mode:** If no editor achieves acceptable round-trip, options are: (a) accept lossy import as an MVP limitation and warn users (low-trust path), or (b) integrate Microsoft Word via Office.js (high-effort path — falls into HAQQ's SharePoint pattern, which we explicitly chose to avoid). The Founder must decide.

---

### F-03.2 — Document & Version entities; storage layer

**Goal:** Define the document data model. Documents belong to Matters. Every save creates a Version.

**Scope:**
- `documents` table:
  - `id` UUID
  - `workspace_id` UUID FK
  - `matter_id` UUID FK
  - `title` VARCHAR(255)
  - `document_type` ENUM('contract', 'memo', 'letter', 'amendment', 'other') default 'contract'
  - `language_primary` ENUM('ar', 'en', 'bilingual')
  - `status` ENUM('draft', 'under_review', 'with_counterparty', 'signed', 'archived') default 'draft'
  - `current_version_id` UUID FK to `document_versions` (set after first save)
  - `original_file_url` VARCHAR(500) nullable (S3 URL of the original imported .docx, if any)
  - `metadata` JSON (free-form: tags, sector, etc.)
  - audit timestamps + soft deletes + `created_by_id`, `updated_by_id`

- `document_versions` table:
  - `id` UUID
  - `workspace_id` UUID FK (denormalized for scoping perf)
  - `document_id` UUID FK
  - `version_number` INT auto-increment per document
  - `body` LONGTEXT (editor JSON state)
  - `body_hash` CHAR(64) (SHA-256 of body for change detection)
  - `change_summary` TEXT nullable (human-written "what changed" note)
  - `created_by_id` UUID FK to users
  - `created_at` TIMESTAMP
  - composite index: `(document_id, version_number)` unique

- `document_clauses` table (introduced HERE, exercised in S-04):
  - `id` UUID
  - `workspace_id` UUID FK
  - `document_version_id` UUID FK
  - `position` INT (order within document)
  - `clause_path` VARCHAR(255) (addressable path, e.g., `section-3.subsection-2`)
  - `title` VARCHAR(255) nullable
  - `body` LONGTEXT (the clause content as editor JSON fragment)
  - `language` ENUM('ar', 'en', 'mixed')
  - `clause_type` VARCHAR(100) nullable (e.g., "indemnification", "limitation_of_liability", "governing_law")
  - audit timestamps
  - composite index `(document_version_id, position)`

- Eloquent models: `Document`, `DocumentVersion`, `DocumentClause`
- `BelongsToWorkspace` trait on all three
- Service: `DocumentService::createVersion($document, $body, $user, $changeSummary = null)` — extracts clauses via configured parser, persists version + clauses atomically in a DB transaction
- Service: `ClauseExtractionService::parse($editorJson, $language)` — walks the editor JSON tree, identifies clause boundaries (heading-based at MVP; structural in Year-2), populates `document_clauses`

**Entities touched:** `documents`, `document_versions`, `document_clauses` (all new).

**API surface:** Defined fully in F-03.4.

**UI surface:** None in this Flow.

**Key decisions to make in Wave-Ready Package:**
- `[PENDING-LEGAL-REVIEW]` Version retention: keep all versions forever, or prune after N? (Default: keep all; storage is cheap; legal trail value is high.)
- Body storage: LONGTEXT vs JSON column — JSON gives indexability, LONGTEXT is simpler. Default JSON if MySQL 8.x.
- Body compression: store gzipped? (Default: no at MVP; revisit if size grows.)
- Clause extraction: heading-based (`<h1>`/`<h2>` boundaries) at MVP; structural in Year-2. Document the heuristic.
- Soft delete cascade: deleting a document soft-deletes its versions/clauses? (Default: yes, via Eloquent observer.)

**Acceptance criteria:**
- Pest: creating a Document also creates Version 1; `current_version_id` is set
- Pest: a save creates Version 2; Version 1 is preserved; `body_hash` differs
- Pest: `ClauseExtractionService` parses a 10-clause sample contract into 10 clauses with correct paths
- Pest: workspace isolation across all 3 tables
- Pest: cascading soft delete works

**Dependencies:** F-03.1 (need editor JSON format locked).

---

### F-03.3 — Document import (.docx → editor JSON)

**Goal:** A lawyer uploads a `.docx` file; it lands in the editor as faithful JSON; the original blob is archived in case of round-trip issues.

**Scope:**
- Upload UI: file picker on Matter detail page, "Import Document" CTA
- Backend: `DocumentImportService::importDocx(UploadedFile $file, Matter $matter, User $user): Document`
- Implementation: invokes the editor library's .docx → JSON converter (per F-03.1 decision)
- Persistence:
  - Original `.docx` to S3-compatible storage: `s3://{workspace_id}/documents/{document_id}/original.docx`
  - Editor JSON to Version 1 body
  - Auto-extract `title` from filename or first H1
  - Run `ClauseExtractionService`
- Mime-type validation, max-file-size validation, virus scan (basic — defer ClamAV to Year-2)
- Asynchronous processing: if file > 1MB or has > 50 clauses, queue a Job (`ImportDocumentJob`), show progress to the user via Livewire polling

**Entities touched:** `documents` (insert), `document_versions` (insert), `document_clauses` (bulk insert).

**API surface:**
- `POST /api/v1/matters/{matter_id}/documents/import` (multipart/form-data)
  - Form fields: `file` (.docx, max 25MB), `title` (optional, fallback to filename), `document_type` (optional, default 'contract'), `language_primary` (optional, auto-detect)
  - Response: `Document` resource with `import_status: completed | processing`

**UI surface:**
- Matter detail page → "Documents" tab → "Import" button → modal with file picker
- Progress indicator if async
- Bilingual labels

**Key decisions to make in Wave-Ready Package:**
- Async threshold (file size / clause count)
- Max file size (default 25MB)
- Language auto-detection algorithm: first-N-characters Unicode block sampling? Or library like `franc`? (Default: characters in Arabic Unicode block ≥ 10% → 'ar'; ≥ 90% → 'ar' only; mixed → 'bilingual')
- Failure UX: malformed file, oversized file, async timeout
- Virus scanning at MVP: skip (Year-2 with ClamAV daemon)

**Acceptance criteria:**
- Pest feature test: upload of a sample contract creates Document + Version 1 + N clauses; original blob accessible
- Pest: malformed file returns 422
- Pest: oversized file returns 413
- Pest: async path works (queue dispatched, status reflects "processing", then "completed")
- Pest: language auto-detection correctly classifies all 5 fixtures from F-03.1

**Dependencies:** F-03.1, F-03.2.

---

### F-03.4 — Document editor + version saves

**Goal:** A lawyer opens a Document; it loads in the editor; they edit; on save, a new Version is created with the new body and re-extracted clauses.

**Scope:**
- Frontend editor mount: Livewire component wrapping the chosen editor library
- Mixed RTL/LTR: the editor must support per-paragraph direction, and `Ctrl+Shift+X` (or equivalent) keyboard shortcut to flip direction at cursor
- Autosave: every 30 seconds OR on blur (whichever first); save is debounced
- Manual save: keyboard shortcut + button; prompts for optional `change_summary`
- Optimistic locking: editor sends `current_version_id` it's editing on save; server rejects with 409 if a newer version exists (concurrent edit)
- Server endpoint: `POST /api/v1/documents/{id}/save` → invokes `DocumentService::createVersion`
- Conflict UX: if 409, prompt user — keep my version (force-save creating Version N+2) or discard mine + reload

**Entities touched:** `document_versions` (insert), `document_clauses` (bulk insert), `documents.current_version_id` (update).

**API surface:**
- `GET /api/v1/documents/{id}` — returns Document with `current_version` eager-loaded (body included)
- `POST /api/v1/documents/{id}/save` — body: `{ "current_version_id": "uuid", "body": <editor JSON>, "change_summary": "optional" }` → 201 with new Version, OR 409 with conflict info
- `GET /api/v1/documents/{id}/versions` — paginated list of versions (metadata only, not body)
- `GET /api/v1/documents/{id}/versions/{version_id}` — single version with body

**UI surface:**
- `/matters/{matter_id}/documents/{document_id}` — editor page (full-screen, distraction-minimal layout)
- Toolbar: format, save, version history, share, export, AI panel (S-04 stub button)
- Status indicator: "Saved 12 seconds ago" / "Saving..." / "Unsaved changes" — fully bilingual

**Key decisions to make in Wave-Ready Package:**
- Autosave interval and debounce
- Editor toolbar exact buttons (defer detail to Wave-Ready Package with Figma)
- Conflict resolution UX wording (lawyer's tone — get advisor input)
- Local draft buffer in browser localStorage in case of network failure (default: yes, 5-minute buffer)

**Acceptance criteria:**
- Pest browser test: open document, type, save, reload — content persists
- Pest browser test: AR paragraph displays RTL, EN paragraph displays LTR, mixed paragraphs use isolate
- Pest: concurrent-save returns 409; conflict resolution path produces a new version
- Pest: every save invokes `ClauseExtractionService` and re-populates clauses for the new version
- Latency budget: save < 500ms p95 on a 5000-word document

**Dependencies:** F-03.3.

---

### F-03.5 — Version history + diff view

**Goal:** A lawyer can see the version history of a document, open any prior version read-only, and compare any two versions side-by-side with changes highlighted (redline view).

**Scope:**
- Version history panel: sidebar drawer listing all versions with timestamp, author, change_summary
- Click a version → opens read-only at that version
- "Compare" mode: select two versions → side-by-side or inline diff view
- Diff algorithm (D-05 decision):
  - Default: text-level diff (line-level or word-level) using PHP's `Diff` library or JS-side `diff-match-patch`
  - Year-2: structural diff via clauses (compare clauses by path, highlight which clauses changed)
- Diff rendering: insertions green, deletions red strikethrough, unchanged neutral
- "Restore this version" action: creates a new Version N+1 with the chosen older body as its content (preserves history; never overwrites)

**Entities touched:** Read-only on versions; `document_versions` insert on restore.

**API surface:**
- `GET /api/v1/documents/{id}/versions/{version_id}/diff?against={other_version_id}` — returns structured diff representation
- `POST /api/v1/documents/{id}/versions/{version_id}/restore` → 201 with new Version

**UI surface:**
- Version history drawer
- Diff view page
- Restore confirmation modal

**Key decisions to make in Wave-Ready Package:**
- Diff library choice (D-05 finalized here)
- Side-by-side vs inline diff default (lean inline; offer toggle)
- Performance ceiling: diff a 10,000-word document in < 2s

**Acceptance criteria:**
- Pest: diff between Version 1 and Version 2 of a sample document correctly identifies the 3 changed sentences
- Pest: restoring an older version creates a new latest version
- Pest browser test: diff view renders changes correctly in both LTR and RTL

**Dependencies:** F-03.4.

---

### F-03.6 — Document export (.docx)

**Goal:** The non-negotiable closing test: any document can be exported back to .docx such that it opens cleanly in Microsoft Word with formatting + structure preserved.

**Scope:**
- Export endpoint generates .docx from editor JSON (per F-03.1 library)
- File naming: `{Document.title}.docx`
- Returns the file as a download (Content-Disposition: attachment)
- Server-side validation: the produced .docx is openable by `php-zip` and contains a valid `document.xml`
- Background option: for large docs, queue an `ExportDocumentJob`, email link to user

**Entities touched:** None (read-only).

**API surface:**
- `GET /api/v1/documents/{id}/export?format=docx&version_id={version_id?}` — defaults to current version

**UI surface:**
- "Export to Word" button in editor toolbar
- "Download this version" in version history

**Acceptance criteria — these are the load-bearing tests for the Surge:**
- Pest: round-trip test (import a contract from F-03.1 fixtures, no edits, export, compare structure) — produced .docx contains the same number of paragraphs, headings, and runs as the original
- Pest: round-trip with one edit (change one clause) — exported .docx differs only in that clause when opened in Word
- Pest: bilingual round-trip — AR paragraphs come back AR-RTL in Word
- Manual QA: open exported file in Microsoft Word 365, LibreOffice 24, Google Docs — visual fidelity acceptable in all three
- **The lawyer advisor signs off on round-trip fidelity using their own real contracts.** Without this sign-off, the Surge does not ship.

**Dependencies:** F-03.4.

---

### F-03.7 — Document sharing (tokenized counterparty link)

**Goal:** A lawyer can share a specific version of a document with a counterparty (someone outside the workspace) via a tokenized URL — read-only download, no editor access.

**Scope:**
- `document_shares` table:
  - `id` UUID
  - `workspace_id` UUID FK
  - `document_id` UUID FK
  - `version_id` UUID FK (locked version, even if document evolves later)
  - `token` VARCHAR(64) unique
  - `recipient_email` VARCHAR(255) nullable (for audit; not required)
  - `expires_at` TIMESTAMP nullable (default: never)
  - `download_count` INT default 0
  - `last_accessed_at` TIMESTAMP nullable
  - `created_by_id` UUID FK
  - audit timestamps
- Public route `/share/{token}`: serves the locked version as a download (PDF or .docx — picker on share creation)
- Audit log entry on every access (IP, user-agent)

**Entities touched:** `document_shares` (new).

**API surface:**
- `POST /api/v1/documents/{id}/shares` — create a share link
- `GET /api/v1/documents/{id}/shares` — list active shares
- `DELETE /api/v1/documents/{id}/shares/{share_id}` — revoke
- Public: `GET /share/{token}` — public download (no auth, rate-limited)

**UI surface:**
- "Share" button in editor toolbar → modal
- Filament resource showing all shares with revoke action

**Key decisions to make in Wave-Ready Package:**
- Default expiry (7 days vs never — default never with manual revoke)
- Format: PDF vs .docx vs both (default: both, user picks)
- Rate limit on public endpoint (default: 60 req/IP/hour)

**Acceptance criteria:**
- Pest: share-link works; download_count increments; last_accessed_at updates
- Pest: revoked share returns 410
- Pest: expired share returns 410
- Pest: rate limit triggers at 61st request

**Dependencies:** F-03.4, F-03.6.

---

## Surge acceptance criteria

- [ ] F-03.1 spike complete; editor + storage decisions documented; round-trip viable
- [ ] F-03.2 schemas migrated; services tested
- [ ] F-03.3 .docx import working for AR, EN, and bilingual fixtures
- [ ] F-03.4 editor works; saves create versions; concurrent-edit handled
- [ ] F-03.5 version history + diff working
- [ ] F-03.6 export round-trip fidelity validated by lawyer advisor on their real contracts
- [ ] F-03.7 sharing works with public tokenized links
- [ ] All Pest tests green; bilingual smoke tests pass
- [ ] Larastan + Pint clean
- [ ] OpenAPI spec updated
- [ ] `[PENDING-LEGAL-REVIEW]` items (retention, deletion) signed off
- [ ] Sign-off by Founder + Legal Advisor + ≥ 1 pilot firm

---

## Out of scope for this Surge

- AI in the document (S-04 — this Surge intentionally stops one step short of AI)
- Clause library / reusable clauses across documents (S-04)
- Comments / suggestions inside the document (S-04 or Year-2)
- Real-time collaborative editing (out of MVP entirely)
- E-signature integration (Year-2)
- PDF as a first-class document type (only as an export format at MVP)
- Track-changes import from Word (parse Word's `<w:ins>` / `<w:del>` — Year-2)
- Document templates (deferred — easy to add Year-2 once we have multiple firms)
- Conflict-of-interest checking across documents (Year-2)

---

## What the Software Engineer agent should produce

Per Flow: migrations, models, services, controllers, FormRequests, policies, Filament resources (where applicable), Livewire components, blade views, Pest test inventory, OpenAPI diff, localization keys.

**Special attention for this Surge** — the Software Engineer agent should:

1. Generate the F-03.1 spike harness as throwaway code in a dedicated `spikes/editor-evaluation/` directory, NOT in `app/`. This is research code that gets deleted after the decision.
2. Refuse to begin F-03.2 onward until the F-03.1 decision is documented in `decisions/D-02.md` and `decisions/D-04.md`.
3. Treat F-03.6 round-trip fidelity tests as **the single most important test suite in the entire MVP**. These tests are the wedge made executable.
4. Set up a separate Pest browser test profile (`tests/Browser/DocumentRoundTrip.php`) that runs against real .docx fixtures and produces visual diffs. This must run in CI on every PR touching `app/Services/Document*`.
