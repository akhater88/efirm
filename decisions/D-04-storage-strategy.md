# D-04 — Document Storage Strategy

**Status:** DECIDED
**Date:** 2026-06-21
**Decided by:** Founder (Abdullah) based on F-03.1 spike results
**Supersedes:** None (first decision)

---

## Context

Documents need two storage layers:
1. **Editor state** — the JSON representation of document content (TipTap JSON per D-02)
2. **Binary blobs** — original `.docx` imports, exported `.docx` files, future PDF exports

## Decision

### Editor state storage

| Aspect | Decision |
|---|---|
| Column | `document_versions.body` |
| Column type | **MySQL JSON** |
| Rationale | MySQL 8.x JSON gives native validation, JSON path queries (useful for future clause extraction optimization), and explicit type safety. TipTap JSON is always valid JSON. |
| Tradeoff | Slightly larger storage overhead vs LONGTEXT; acceptable at MVP scale (legal contracts are small — typical 5–50 page document = 50–500KB JSON) |

### Body change detection

| Aspect | Decision |
|---|---|
| Column | `document_versions.body_hash` |
| Type | `CHAR(64)` — SHA-256 hex digest |
| Purpose | Skip no-op saves during autosave. If hash matches current version's hash, don't create a new version. |
| Computed | Server-side in PHP via `hash('sha256', json_encode($body))` |

### Binary blob storage

| Aspect | Decision |
|---|---|
| Backend | S3-compatible object storage |
| Local dev | Laravel's `local` disk (or MinIO if S3 APIs needed locally) |
| Production | Cloudways-adjacent S3 (DigitalOcean Spaces Frankfurt, same region as compute) |
| Laravel disk name | `documents` (configured in `config/filesystems.php`) |
| `.env` variable | `DOCUMENTS_DISK=local` (dev) / `DOCUMENTS_DISK=s3` (prod) |

### Path patterns

| File type | S3 path | When created |
|---|---|---|
| Original import | `{workspace_id}/documents/{document_id}/original.docx` | F-03.3 import |
| Export cache | `{workspace_id}/documents/{document_id}/exports/v{version_number}.docx` | F-03.6 export (cached; regenerated if body changes) |
| Share download | Same as export cache (served via signed URL or streamed) | F-03.7 share |

### Compression

| Aspect | Decision |
|---|---|
| JSON body | **No compression at MVP** |
| Rationale | Average legal contract JSON < 500KB. MySQL JSON column handles this fine. Revisit if p95 document size exceeds 2MB. |
| Future option | gzip before storage + decompress on read. Transparent to the application layer. |

### Version retention

| Aspect | Decision |
|---|---|
| Policy | **Keep all versions forever** `[PROVISIONAL-FOUNDER-DECIDED]` |
| Rationale | Storage is cheap (~$0.02/GB/month on S3). Legal audit trail value is high. Lawyers expect full history. |
| `[PENDING-LEGAL-REVIEW]` | Advisor should confirm: (a) retention is legally required/beneficial, (b) any jurisdiction-specific data deletion requirements (GDPR right-to-erasure vs legal hold) |

## Consequences

### F-03.2 migration impact

```php
// document_versions table
$table->json('body');                    // TipTap JSON — NOT longText
$table->char('body_hash', 64);           // SHA-256 for change detection
```

### F-03.3 import impact

```php
// config/filesystems.php
'documents' => [
    'driver' => env('DOCUMENTS_DISK', 'local'),
    'root' => storage_path('app/documents'),  // local dev
    // S3 config pulled from DOCUMENTS_* env vars in production
],
```

### Autosave optimization (F-03.4)

```
On autosave:
  1. Compute SHA-256 of new JSON body
  2. Compare with current_version.body_hash
  3. If identical → skip save (no new version created)
  4. If different → create new version
```

## Review

- [ ] Founder sign-off
- [ ] `[PENDING-LEGAL-REVIEW]` — version retention policy reviewed by legal advisor before paid launch
