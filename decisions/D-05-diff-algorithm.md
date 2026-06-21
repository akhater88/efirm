# D-05 — Diff Algorithm Choice

**Status:** DECIDED
**Date:** 2026-06-21
**Decided by:** Founder (Abdullah) based on F-03.5 implementation
**Supersedes:** None (first decision)

---

## Context

SURGE-03 F-03.5 requires comparing two document versions and rendering changes inline (redline view). Need a diff algorithm that works on bilingual (AR/EN) text.

## Decision

**Word-level diff using Longest Common Subsequence (LCS) algorithm**, implemented in PHP (`app/Services/VersionDiffService.php`).

### Pipeline

```
TipTap JSON (version A) → extractPlainText() → tokenize (words + whitespace)
TipTap JSON (version B) → extractPlainText() → tokenize (words + whitespace)
→ LCS computation → diff blocks (unchanged / added / removed)
→ merge adjacent blocks of same type → render inline
```

### Why this approach

1. **Works on Arabic text.** The tokenizer uses Unicode-aware regex (`/\s+/u`) so Arabic words are split correctly.
2. **Simple and fast.** LCS on word tokens handles typical legal documents (< 50 pages) well within the 2-second performance ceiling.
3. **No external dependencies needed.** The `jfcherng/php-diff` library is installed as a fallback, but the MVP uses the custom LCS implementation for simplicity.
4. **Text-level not structural.** Year-2 will add structural clause-level diff (compare by `clause_path`). MVP uses text-level which is good enough for redline review.

### Known limitations

- **Heading markers** are included as `#` prefixes in plain text extraction. This means heading-level changes appear as text changes.
- **Formatting changes** (bold added/removed) are NOT visible in the diff — only text content changes are shown.
- **Performance** on very large documents (> 10,000 words): LCS is O(n*m) in memory. For MVP-scale legal contracts (typically < 5,000 words), this is fine.

## Year-2 evolution

- Structural diff: compare clauses by `clause_path`, show which clauses changed
- Formatting-aware diff: detect bold/italic/underline changes
- Side-by-side view in addition to inline

## Review

- [x] Word-level diff correctly identifies changed sentences (tested)
- [x] Arabic text tokenization works (tested with bilingual content)
- [x] Performance acceptable for MVP document sizes
- [ ] Founder sign-off
