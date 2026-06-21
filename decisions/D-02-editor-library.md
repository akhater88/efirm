# D-02 — Editor Library Choice

**Status:** DECIDED
**Date:** 2026-06-21
**Decided by:** Founder (Abdullah) based on F-03.1 spike results
**Supersedes:** None (first decision)

---

## Context

SURGE-03 requires a browser-based document editor capable of:
- Bilingual AR/EN content with per-paragraph RTL/LTR direction
- .docx import and export with formatting preservation (the non-negotiable wedge test)
- JSON-based document state for server storage and AI processing (S-04)
- Table, list, heading, bold/italic/underline support
- Integration with Livewire 3 (Laravel backend)

## Candidates evaluated

| Candidate | Evaluation method | License | Result |
|---|---|---|---|
| **TipTap 2 (ProseMirror)** | Full round-trip harness | MIT (core) | **SELECTED** |
| **Editor.js** | Desk research + community analysis | Apache 2.0 | Rejected — no .docx interop, experimental RTL |
| **CKEditor 5** | Dropped pre-evaluation | GPL/Commercial | Dropped — commercial license requirement (per CQG) |

## Round-trip test results

Pipeline: `.docx → mammoth (HTML) → TipTap generateJSON → TipTap JSON → docx library → .docx`

| Fixture | Type | Fidelity | Verdict |
|---|---|---|---|
| Bilateral NDA (AR+EN) | Bilingual | **100%** | PASS |
| Share Purchase Agreement (AR) | Arabic only | **100%** | PASS |
| Supply Agreement (EN) | English only | **100%** | PASS |
| **Average** | | **100%** | **GO** |

Threshold was 80%. All fixtures passed at 100%.

Detailed results: `spikes/editor-evaluation/results/tiptap-round-trip-report.md`
Comparison matrix: `spikes/editor-evaluation/results/comparison-matrix.md`

## Decision

**TipTap 2 (ProseMirror-based)** is the editor library for the document workspace.

## Rationale

1. **Round-trip fidelity proven.** 100% structural fidelity across bilingual, Arabic-only, and English-only contracts. Paragraphs, headings, lists, tables, bold, italic — all preserved through the full import → edit → export cycle.
2. **ProseMirror architecture fits the domain.** Tree-based document model (not flat blocks) supports the deep nesting legal contracts require. Per-paragraph `dir` attribute support is native. Transaction-based editing enables precise AI-driven clause replacement (S-04).
3. **Ecosystem maturity.** 19k GitHub stars, active maintenance, comprehensive extension library. MIT-licensed core covers all MVP needs.
4. **AI integration ergonomics.** TipTap JSON is a clean typed tree. Walking it for clause extraction (S-04) is straightforward. ProseMirror's programmatic content manipulation (insert, replace, delete at position) is purpose-built for AI suggestions.

## Consequences

### Frontend stack (F-03.4)

- **npm packages:** `@tiptap/core`, `@tiptap/starter-kit`, `@tiptap/extension-underline`, `@tiptap/extension-table` (+ row/cell/header), `@tiptap/extension-text-align`
- **Mount:** Inside a custom Livewire + Blade component at `/matters/{matter_id}/documents/{document_id}`
- **Not using:** TipTap Pro extensions (collaboration, drag-handle) — not needed at MVP

### Import pipeline (F-03.3)

```
.docx binary
  → mammoth.convertToHtml()  (preserves structure, strips fonts + RTL markers)
  → Post-process: inject dir="rtl" on paragraphs with ≥30% Arabic chars
  → TipTap generateJSON()    (HTML → TipTap JSON)
  → Store as document_versions.body (JSON column)
```

### Export pipeline (F-03.6)

```
TipTap JSON (from document_versions.body)
  → Walk JSON tree
  → For each node: detect Arabic text → set <w:bidi> + RTL alignment
  → Build .docx via `docx` npm library
  → Return binary for download
```

### AI integration (S-04)

```
TipTap JSON
  → ClauseExtractionService walks heading boundaries
  → Each clause = a JSON subtree with position + path
  → AI operates on clause-level JSON fragments
  → AI response → ProseMirror transaction → insert/replace at position
```

### Known limitation: RTL direction on import

mammoth does not preserve `<w:bidi>` RTL direction markers from .docx files. Workaround: Unicode-based heuristic post-processing after mammoth conversion. The heuristic detects Arabic Unicode characters (≥30% of alphabetic chars) and injects `dir="rtl"` + `textAlign: "right"` into the TipTap JSON nodes. This is reliable for Levant legal text where Arabic/English boundaries are clear at the paragraph level.

## Review

- [ ] Founder sign-off
- [x] Round-trip fidelity ≥ 80% on all 3 fixtures (actual: 100%)
- [x] Spike code committed to `spikes/editor-evaluation/`
