### Codebase Cleanup Plan: Dead Code Removal and Deduplication

This plan outlines how we will systematically identify and remove dead code and consolidate duplicate implementations across the project without disrupting production. It combines static analysis (fast, broad) with runtime verification (safe) and introduces guardrails to prevent regressions.

---

### Objectives
- Remove truly unused (dead) code safely.
- Consolidate duplicate functions into a single canonical implementation.
- Add guardrails (reports, CI checks) to prevent dead/duplicate code from reappearing.

---

### Guiding Constraints
- Live system with public users; prioritize safety and reversible changes.
- Central routing via `index.php?page=...`; avoid direct includes that bypass it.
- Use `PROJECT_ROOT_PATH` for cross-module references and `__DIR__` for local includes.
- No inline CSS/JS; assets live under `assets/` and are bundled with Vite.
- Follow project docs and best practices in `docs/`.

---

### Methodology (Two-Pronged)
1) Static analysis: Detect unused symbols, files, CSS selectors, and copy/paste duplicates across PHP/JS/CSS.
2) Runtime verification (staging): Capture function call traces during typical flows to build a “definitely used” set and avoid false positives.

We only delete code when it is unused by both static and runtime analysis, or after a deprecation window for uncertain cases.

---

### Tooling We Will Introduce (Dev-only)
- PHP: PHPStan (dead code rules), Psalm (unused code checks), Rector (dead-code sets), Deptrac (dependency graph).
- JS/CSS: ESLint (no-unused rules), jscpd (copy/paste detector), PurgeCSS-style report (report-only) for unused selectors.
- Runtime: Xdebug function trace (staging only, short windows) to log actually used functions.
- Automation: Scripts to generate all reports under `docs/reports/cleanup/YYYY-MM-DD/`.

No production impact: These are dev dependencies and reports only.

---

### Reports and Artifacts
Generated under `docs/reports/cleanup/<YYYY-MM-DD>/`:
- Dead code candidates (PHP, JS).
- Duplicate code map (jscpd) with clusters and percentages.
- Dependency graph (Deptrac) to highlight spaghetti boundaries.
- Unused selectors (CSS) – report only.
- Unreferenced assets (images/fonts) – based on reference scans.
- Consolidation action list (CSV/JSON) with decisions: delete/consolidate/defer.

---

### Process and Milestones
1) Baseline & Guardrails
- Create implementation tracking doc: `.github/implementations/cleanup/dead-code-cleanup.md` (checklist + progress log).
- Add Composer/NPM dev tools and config files (no prod impact).
- Add scripts to generate reports (Windows-friendly) into `docs/reports/cleanup/<date>/`.

2) Inventory & Detection
- Static: Index function/class definitions and references; run PHPStan/Psalm/Rector, ESLint, jscpd, CSS reports.
- Runtime: Enable short Xdebug traces in staging for key user flows (login, dashboards, CRUD paths) and merge with static results.
- Assets: Detect unreferenced files in `assets/`.

3) Triage & Categorize
- Delete now: No references (static + runtime) → remove.
- Consolidate: Duplicates → choose canonical implementation, plan replacements.
- Defer: Dynamically referenced/uncertain → mark deprecated, add telemetry/logging, revisit later.

4) Consolidation Strategy (Duplicates)
- Select canonical implementation (correctness, performance, coverage, path stability).
- Centralize:
  - PHP helpers in `lib/helpers/` grouped by domain (e.g., `strings.php`, `db.php`, `files.php`).
  - JS shared modules in `assets/js/shared/` (e.g., `ajax.js`, `formValidation.js`).
  - CSS utilities in `assets/css/shared/_utilities.css`.
- Replace call sites gradually:
  - Provide deprecated wrappers (old name → new function) with `@deprecated` and minimal telemetry (e.g., `error_log` throttled) to surface stragglers.
  - After a burn-in window (no calls observed), remove wrappers.

5) Safe Removal Protocol
- For “Delete now”: remove code, rebuild assets, rerun analyses to confirm no regressions.
- For “Defer”: keep deprecated shim + telemetry; schedule recheck after telemetry is clean.

6) Prevent Regressions
- CI thresholds: Fail build if new unused code or copy/paste duplicates exceed a small baseline.
- (Optional) Pre-commit hook for quick ESLint and jscpd diff check.
- Document “how to add helpers” and “do-not duplicate policy” in `docs/project_structure_best_practices.md`.

---

### Deliverables
- Implementation tracking doc: `.github/implementations/cleanup/dead-code-cleanup.md`.
- Config files for tools (PHPStan, Psalm, Rector, Deptrac, ESLint, jscpd, PurgeCSS setup).
- First report bundle in `docs/reports/cleanup/<date>/`.
- Consolidation action list (CSV/JSON) with owners and ETAs.
- CI rules to prevent regressions.

---

### Risks and Mitigations
- Dynamic includes/variable function calls: rely on runtime traces + deprecation shims before deletion.
- Similar-but-not-identical duplicates: choose canonical carefully; add thin adapters if needed.
- Performance of analysis: scope runs by directory initially; cache and schedule during low-traffic times.

---

### Roles and Environments
- All destructive changes occur via PRs and after report review.
- Runtime traces only in staging; never enable noisy tracing in production.
- Daily/weekly cadence to review reports and execute the action list in small batches.

---

### Suggested Timeline (adjustable)
- Week 1: Tooling setup, baseline reports, initial triage and action list.
- Week 2–3: Consolidate top duplicates, remove “delete now” items, add deprecation shims.
- Week 4+: Remove shims with zero usage, tighten CI thresholds, continue iterative cleanup by module.

---

### Success Criteria
- Measurable reduction in duplicate code (jscpd % down) and unused symbols.
- No production regressions related to cleanup.
- Clear centralized helpers with documented usage; CI enforces standards going forward.

---

### Next Steps
1) Create the implementation tracking doc and wire up configs and scripts.
2) Generate the first set of reports and produce an action list.
3) Execute the action list in small, safe batches with deprecation windows.


