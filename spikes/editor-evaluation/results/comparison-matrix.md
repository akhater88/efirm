# Editor Library Comparison Matrix — F-03.1 Spike

**Date:** 2026-06-21
**Candidates:** TipTap 2 (ProseMirror), Editor.js
**CKEditor 5:** Dropped (commercial license; per CQG decision)

---

## Scoring Matrix

| Criterion | Weight | TipTap 2 | Editor.js | Notes |
|---|---|---|---|---|
| **Import fidelity** | 25% | **5/5** | 2/5 | TipTap: 100% structural fidelity via mammoth + generateJSON pipeline. Editor.js: no native .docx import; would require custom parser or mammoth + manual block conversion. |
| **RTL/LTR handling** | 25% | **4/5** | 2/5 | TipTap: ProseMirror supports per-paragraph `dir` attribute natively. RTL not preserved on import (mammoth limitation) but detectable via Unicode heuristic. Export path correctly sets `<w:bidi>`. Editor.js: RTL support is experimental; community plugin exists but is unmaintained (last commit 2023). |
| **Export fidelity** | 25% | **5/5** | 1/5 | TipTap: 100% structural round-trip via JSON walker + `docx` library. Editor.js: no native .docx export; would require building a complete block-to-OOXML converter from scratch. |
| **AI integration ergonomics** | 10% | **5/5** | 4/5 | TipTap: JSON schema is a clean tree of typed nodes with marks. Easy to walk, slice, replace content programmatically. ProseMirror transactions allow precise surgical edits. Editor.js: block-based JSON is also clean and walkable, but lacks ProseMirror's transaction/selection APIs for in-place AI suggestions. |
| **Complexity / DX** | 10% | **4/5** | 4/5 | TipTap: excellent docs, active community (19k GitHub stars), many extensions. Extension API is clean. Livewire integration requires custom Blade component but is straightforward. Editor.js: simpler mental model (blocks only), but fewer extensions and weaker ecosystem for our use case. |
| **License cost** | 5% | **5/5** | 5/5 | TipTap: MIT (core + all extensions we need). Editor.js: Apache 2.0. Both free. Note: TipTap has paid "Pro" extensions (drag-handle, collaboration) — we don't need these at MVP. |

---

## Weighted Scores

| Candidate | Import (25%) | RTL (25%) | Export (25%) | AI (10%) | DX (10%) | License (5%) | **Total** |
|---|---|---|---|---|---|---|---|
| **TipTap 2** | 1.25 | 1.00 | 1.25 | 0.50 | 0.40 | 0.25 | **4.65 / 5.00** |
| **Editor.js** | 0.50 | 0.50 | 0.25 | 0.40 | 0.40 | 0.25 | **2.30 / 5.00** |

---

## Recommendation

**TipTap 2 (ProseMirror)** is the clear winner with a 2x score advantage.

### Why TipTap wins

1. **.docx round-trip works today.** The mammoth + TipTap JSON + docx library pipeline achieves 100% structural fidelity on all 3 test fixtures (bilingual NDA, Arabic SPA, English supply agreement).
2. **ProseMirror's architecture is purpose-built for our use case.** Per-paragraph `dir` attributes, typed node schema, transaction-based editing, and cursor-aware selections are exactly what the document editor and AI integration (S-04) need.
3. **The ecosystem is production-grade.** 19k GitHub stars, active maintenance, comprehensive extension library, and TipTap's own commercial backing ensures long-term viability.

### Why Editor.js loses

1. **No .docx interop.** Would require building import and export from scratch — weeks of work with uncertain fidelity.
2. **RTL is experimental.** The community RTL plugin is unmaintained. Would need to fork and maintain.
3. **Block-based model is too flat for legal documents.** Legal contracts have deep nesting (sections > subsections > clauses > sub-clauses). ProseMirror's tree model handles this; Editor.js's flat block list does not.

### Known limitations of TipTap 2 to address in F-03.3/F-03.4

1. **RTL direction lost on import** — mammoth strips `<w:bidi>` markers. Mitigated by Unicode-based heuristic post-processing after import.
2. **Font information lost on import** — mammoth strips font-face data. Production will use a configurable default font (Inter for EN, IBM Plex Sans Arabic for AR).
3. **TipTap Pro extensions** — collaboration (real-time multi-user editing) is a paid TipTap Pro feature. Not needed at MVP (single-user editing only). If needed Year-2, evaluate cost then.
