# Editor Evaluation Spike — F-03.1

**Purpose:** Validate that TipTap 2 (ProseMirror) can handle bilingual .docx round-trip with acceptable fidelity before committing to it as the editor library for SURGE-03.

**Status:** COMPLETE — GO. TipTap 2 passes at 100% fidelity.

**Decisions produced:**
- `decisions/D-02-editor-library.md` — TipTap 2 selected
- `decisions/D-04-storage-strategy.md` — MySQL JSON + S3 blobs

---

## How to run

```bash
cd spikes/editor-evaluation
npm install

# Generate .docx test fixtures
npm run generate-fixtures

# Run round-trip fidelity test
npm run test:roundtrip
```

## What it tests

1. **Import:** `.docx` → mammoth (HTML) → TipTap `generateJSON` → JSON
2. **Export:** TipTap JSON → walk nodes → `docx` library → `.docx`
3. **Round-trip:** Import → Export → Re-import → Compare structure counts

## Files

```
src/
  generate-fixtures.mjs   — Generates 3 synthetic .docx contracts
  tiptap-schema.mjs       — TipTap extension config + HTML↔JSON conversion
  import-docx.mjs         — .docx → TipTap JSON import pipeline
  export-docx.mjs         — TipTap JSON → .docx export pipeline
  roundtrip-test.mjs      — Round-trip test runner + report generator
fixtures/
  01-bilateral-nda-ar-en.docx   — Bilingual NDA (Arabic + English)
  02-spa-ar.docx                — Arabic-only Share Purchase Agreement
  03-supply-agreement-en.docx   — English-only Supply Agreement
results/
  tiptap-round-trip-report.md   — Detailed fidelity report
  comparison-matrix.md          — TipTap 2 vs Editor.js scoring
  summary.json                  — Machine-readable results
  exported-*.docx               — Round-tripped .docx files
  *-original.html/json          — Intermediate artifacts (debug)
  *-roundtripped.html/json      — Intermediate artifacts (debug)
```

## Lifecycle

This is throwaway spike code. It will be deleted after SURGE-03 F-03.2 is complete and the production document infrastructure is built. The decisions (`D-02`, `D-04`) and test fixtures are the durable outputs.
