# docs/

All non-code project documentation lives here. Code lives in `app/`, `database/`, `resources/`, `routes/`, `tests/`. This folder is the source of truth for everything else.

## Subfolders

### `docs/planning/`
Surge plans, Flow specs, Wave-Ready Packages, the MVP roadmap. The active priority is always named in `CLAUDE.md` §11. Surge plans use the naming convention `SURGE-NN-Name.md` for primary surges and `SURGE-FIX-NN-Name.md` for correction surges. Flow specs use `F-FIX-NN-NN-Name.md`. Wave-Ready Packages use `F-FIX-NN-NN-Name-WRP-N.md`.

### `docs/validation/`
- `00_FOUNDER_WAIVER.md` — operating waiver
- `01_haqq_ai_test_protocol.md` — competitive testing
- `02_advisor_meeting_log.md` — **source of truth for advisor input**; append-only; cited by decision number from code comments
- `0N_*_lawyer_signoff.md` — formal lawyer attestation files (RED until paid lawyer signs)
- `0N_*_cpa_signoff.md` — formal CPA attestation
- `06_legal_docs/` — paid-lawyer-drafted ToS/Privacy/DPA/AI-Disclaimer drafts
- `*_audit.csv` — backfill audit CSVs produced by migrations (founder reviews manually; never auto-corrected)

### `docs/decisions/`
Architecture Decision Records (ADRs). Append-only. To change a decision, write a new ADR that supersedes the old one — never edit existing ADRs in place. Naming: `D-NN_short_topic.md`.

### `docs/prompts/`
AI prompt templates used by `LlmProvider` in production.
- `docs/prompts/*.md` — clause-level prompts (Lawyer-gated; require `[LEGAL-REVIEW-APPROVED]` header before production use)
- `docs/prompts/document_generation/*.md` — full-document generation templates (Lawyer-gated)

### `docs/spikes/`
Throwaway research notes — competitive analysis, library evaluations, performance investigations. Not load-bearing for production decisions; informational only.

## Why everything lives under `docs/`

Pre-2026-06-23, planning/validation/decision/prompt/spike folders sat at the repo root. This created friction in two ways:
1. The repo root was cluttered with non-code folders, making `ls` noisy and obscuring the actual app structure
2. Engineer agents reading the codebase had to mentally separate "real code" from "documentation" at every level

Consolidating under `docs/` makes the separation explicit at the filesystem level. Code lives at the root. Documentation lives under `docs/`. No mixing.

## Path convention enforcement

Every reference in code comments, engineer-agent prompts, and inter-document links MUST use the `docs/` prefix. Search the codebase for any reference to `planning/`, `validation/`, `decisions/`, `prompts/`, or `spikes/` that isn't prefixed with `docs/` — those are stale and should be updated.

## Reading order for a new contributor

1. `CLAUDE.md` (repo root) — the project memory
2. `docs/README.md` (this file)
3. `docs/planning/00_MVP_ROADMAP_v0.2.md` — strategic plan
4. `docs/validation/00_FOUNDER_WAIVER.md` — operating mode
5. `docs/validation/02_advisor_meeting_log.md` — what the advisor said
6. `docs/decisions/D-NN_*.md` — major design decisions in reverse chronological order
7. The active Surge plan named in `CLAUDE.md` §11
