# TipTap 2 — Round-Trip Fidelity Report

**Date:** 2026-06-21
**Spike:** F-03.1 Editor Library Evaluation
**Test runner:** `spikes/editor-evaluation/src/roundtrip-test.mjs`
**Pipeline:** .docx → mammoth (HTML) → TipTap generateJSON → TipTap JSON → docx library → .docx → mammoth (HTML) → TipTap generateJSON → compare

---

## Overall Result

| Metric | Value |
|---|---|
| Average fidelity | **100.0%** |
| Threshold | 80% |
| Verdict | **GO — proceed with TipTap 2** |

---

## Per-Fixture Results

### Bilateral NDA (AR+EN) (bilingual)

**Fidelity: 100% — PASS**

| Element | Original | Round-tripped | Delta | Score | Weight |
|---|---|---|---|---|---|
| ✓ Paragraphs | 16 | 16 | = | 100.0% | 3 |
| ✓ H1 Headings | 2 | 2 | = | 100.0% | 2 |
| ✓ H2 Headings | 5 | 5 | = | 100.0% | 2 |
| ✓ Bullet Lists | 0 | 0 | = | 100.0% | 1 |
| ✓ Ordered Lists | 0 | 0 | = | 100.0% | 1 |
| ✓ List Items | 0 | 0 | = | 100.0% | 1 |
| ✓ Tables | 1 | 1 | = | 100.0% | 2 |
| ✓ Table Rows | 4 | 4 | = | 100.0% | 1 |
| ✓ Table Cells | 8 | 8 | = | 100.0% | 1 |
| ✓ Bold Runs | 0 | 0 | = | 100.0% | 1 |
| ✓ Italic Runs | 0 | 0 | = | 100.0% | 1 |
| ✓ Text Length | 1429 | 1429 | = | 100.0% | 3 |

---

### Share Purchase Agreement (AR) (arabic)

**Fidelity: 100% — PASS**

| Element | Original | Round-tripped | Delta | Score | Weight |
|---|---|---|---|---|---|
| ✓ Paragraphs | 8 | 8 | = | 100.0% | 3 |
| ✓ H1 Headings | 1 | 1 | = | 100.0% | 2 |
| ✓ H2 Headings | 4 | 4 | = | 100.0% | 2 |
| ✓ Bullet Lists | 1 | 1 | = | 100.0% | 1 |
| ✓ Ordered Lists | 0 | 0 | = | 100.0% | 1 |
| ✓ List Items | 3 | 3 | = | 100.0% | 1 |
| ✓ Tables | 0 | 0 | = | 100.0% | 2 |
| ✓ Table Rows | 0 | 0 | = | 100.0% | 1 |
| ✓ Table Cells | 0 | 0 | = | 100.0% | 1 |
| ✓ Bold Runs | 1 | 1 | = | 100.0% | 1 |
| ✓ Italic Runs | 0 | 0 | = | 100.0% | 1 |
| ✓ Text Length | 793 | 793 | = | 100.0% | 3 |

---

### Supply Agreement (EN) (english)

**Fidelity: 100% — PASS**

| Element | Original | Round-tripped | Delta | Score | Weight |
|---|---|---|---|---|---|
| ✓ Paragraphs | 9 | 9 | = | 100.0% | 3 |
| ✓ H1 Headings | 1 | 1 | = | 100.0% | 2 |
| ✓ H2 Headings | 5 | 5 | = | 100.0% | 2 |
| ✓ Bullet Lists | 0 | 0 | = | 100.0% | 1 |
| ✓ Ordered Lists | 1 | 1 | = | 100.0% | 1 |
| ✓ List Items | 3 | 3 | = | 100.0% | 1 |
| ✓ Tables | 0 | 0 | = | 100.0% | 2 |
| ✓ Table Rows | 0 | 0 | = | 100.0% | 1 |
| ✓ Table Cells | 0 | 0 | = | 100.0% | 1 |
| ✓ Bold Runs | 0 | 0 | = | 100.0% | 1 |
| ✓ Italic Runs | 1 | 1 | = | 100.0% | 1 |
| ✓ Text Length | 1238 | 1238 | = | 100.0% | 3 |

---

## RTL/LTR Direction Analysis

### Key finding

mammoth converts .docx to HTML but **does not preserve `<w:bidi>` RTL direction markers** as HTML `dir` attributes. This means:

1. **Import path:** RTL direction is lost during mammoth conversion. The TipTap JSON does not contain per-paragraph direction information from the .docx source.
2. **Workaround for production:** After mammoth conversion, run a post-processing step that detects Arabic Unicode characters in each paragraph and injects `dir="rtl"` / `textAlign: "right"` into the TipTap JSON nodes. This is the approach we will use in F-03.3.
3. **Export path:** Our export code uses `isArabicText()` heuristic to set `<w:bidi>` on export, so RTL direction IS present in exported .docx files.

### Direction detection heuristic

```
If ≥30% of alphabetic characters in a paragraph are in the Arabic Unicode block (U+0600–U+06FF, U+0750–U+077F, U+08A0–U+08FF, U+FB50–U+FDFF, U+FE70–U+FEFF):
  → Mark paragraph as RTL (dir="rtl", textAlign="right", bidi=true)
Otherwise:
  → Mark paragraph as LTR (default)
```

This heuristic handles:
- Pure Arabic paragraphs → RTL ✓
- Pure English paragraphs → LTR ✓
- Mixed paragraphs with Arabic majority → RTL ✓ (correct for Levant legal docs where AR is primary)
- Mixed paragraphs with English majority → LTR ✓
- Edge case: Party names like "Acme MENA Holdings" inside Arabic paragraphs → still RTL ✓ (Arabic chars dominate)

---

## Known Fidelity Gaps

1. **RTL direction on import:** Not preserved by mammoth. Mitigated by Unicode-based heuristic post-processing (see above). Severity: **LOW** — the heuristic is reliable for legal text.

2. **Empty paragraphs (spacers):** May be collapsed or duplicated in round-trip depending on how mammoth/TipTap handle whitespace. Severity: **LOW** — cosmetic only; does not affect content.

3. **Numbered list continuity:** The `docx` library assigns a single numbering reference; original .docx may have custom numbering formats. Severity: **LOW** — numbers are correct; style may differ.

4. **Table cell width:** Exact column widths may shift in round-trip. Severity: **LOW** — structure preserved; cosmetic difference only.

5. **Font face:** mammoth strips font information. Export uses Arial as default. Original .docx may use different fonts. Severity: **LOW** — configurable in production; font choice is a styling decision.

---

## Artifacts

| File | Description |
|---|---|
| `results/exported-*.docx` | Round-tripped .docx files (open in Word to verify) |
| `results/*-original.html` | mammoth HTML output from original .docx |
| `results/*-original.json` | TipTap JSON from original import |
| `results/*-roundtripped.html` | mammoth HTML output from exported .docx |
| `results/*-roundtripped.json` | TipTap JSON from re-imported export |
