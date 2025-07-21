# Implementation Plan: Dedicated KPI Edit Page for Admin Outcomes

## 1. Preparation & Analysis

- [x] Identify all files related to outcome editing (admin/agency views, JS, CSS, AJAX, PHP logic).
- [x] Determine how KPI outcomes are flagged (`type === 'kpi'`).
- [x] Review agency edit_outcome.php for KPI handling.

## 2. Directory & File Structure Planning

- [x] Create `app/views/admin/outcomes/edit_kpi.php` (new view for KPI editing).
- [x] Create `assets/js/admin/edit_kpi.js` (DOM/event logic for KPI editing).
- [x] Create `assets/js/admin/editKpiLogic.js` (pure logic/validation for KPI editing).
- [x] Create `assets/css/admin/edit_kpi.css` (styling for KPI edit page).
- [x] Add unit test: `tests/admin/editKpiLogic.test.js`.

## 3. Move & Refactor Files

- [ ] Replicate relevant logic from agency's edit_outcome.php for KPI section.
- [ ] Ensure only KPI outcomes can be edited in this page (error if not KPI).
- [ ] Modularize JS and CSS imports via Vite.
- [x] Update edit link in manage_outcomes.php to redirect KPI outcomes to edit_kpi.php.

## 4. Implement Vite for Asset Bundling

- [ ] Add entry for edit_kpi.js and edit_kpi.css in vite.config.js.
- [ ] Reference bundled assets in edit_kpi.php.

## 5. Backend Logic & Data Flow

- [ ] Fetch outcome by ID, ensure type is 'kpi'.
- [ ] On POST, update only KPI data fields.
- [ ] Use/update AJAX endpoint if needed for KPI updates.

## 6. AJAX & API Refactoring

- [ ] Ensure AJAX endpoint returns JSON only.
- [ ] Use modular JS for AJAX in edit_kpi.js.

## 7. Base Layout & Dynamic Asset Injection

- [ ] Use base layout for asset injection.
- [ ] Set `$cssBundle`, `$jsBundle`, `$contentFile` as needed.

## 8. Unit Testing

- [ ] Write/update unit tests for editKpiLogic.js.

## 9. Validation & QA

- [ ] Test all features and flows in the browser.
- [ ] Check for code duplication, unused files, and modularize further if needed.

## 10. Documentation & Bug Tracking

- [ ] Update docs/project_structure_best_practices.md if new patterns are used.
- [ ] Log any bugs found/fixed in docs/bugs_tracker.md.

## 11. Review & Optimize

- [ ] Review for maintainability, scalability, and performance.
- [ ] Optimize asset loading and follow security best practices.

---

### Progress

- [x] Plan created and approved by user.
- [x] Edit link in manage_outcomes.php updated for KPI outcomes.
- [x] KPI edit page and supporting files scaffolded.
- [ ] Implementation in progress.
- [ ] All steps completed and tested.
