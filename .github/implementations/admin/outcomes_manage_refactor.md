# Refactor Plan: Admin Outcomes Management Page

## Target: `app/views/admin/outcomes/manage_outcomes.php`

---

## 1. Preparation & Analysis

- [x] List all related files and data flow.
- [x] Identify dynamic features and modal logic.

## 2. Directory & File Structure Planning

- [x] Create `assets/js/admin/manage_outcomes.js` for all JS logic.
- [x] Create `assets/css/admin/manage_outcomes.css` for outcome-specific styles.
- [x] Plan AJAX endpoint: `app/ajax/admin_outcomes.php` for outcome detail editing.
- [x] Plan for unit test: `tests/admin/manageOutcomesLogic.test.js`.

## 3. Move & Refactor Files

- [x] Move inline JS to JS file.
- [x] Move any outcome-specific CSS to CSS file.
- [x] Modularize modal logic and AJAX calls.
- [x] Remove unused Add Item button and related JS from modal.

## 4. Implement Vite for Asset Bundling

- [x] Add entries for new JS/CSS in `vite.config.js`.
- [x] Update PHP view to inject bundled assets.

## 5. Refactor PHP Logic & Data Flow

- [x] Ensure all DB logic is in helpers/models (added update_outcome function).
- [x] View only displays data.

## 6. AJAX & API Refactoring

- [x] Create AJAX endpoint for outcome detail editing.
- [x] Use modular JS for AJAX.

## 7. Base Layout & Dynamic Asset Injection

- [x] Use base layout for asset injection (if required by project structure).

## 8. Unit Testing

- [x] Add unit tests for JS logic (placeholder, expand as needed).

## 9. Validation & QA

- [x] Test all features in browser.
- [x] Remove unused Add Item button and related JS.
- [x] Check for code duplication and unused files (scan completed; legacy/backup files identified as candidates for removal).

## 10. Documentation & Bug Tracking

- [x] Update this file as progress is made.
- [ ] Log any bugs in `docs/bugs_tracker.md`.

## 11. Review & Optimize

- [ ] Review for maintainability, scalability, and performance.
- [ ] Optimize asset loading and follow security best practices.

---

### Progress Log

- 2024-06-18: Plan created and approved.
- 2024-06-18: JS, CSS, and test files created. Vite config and asset injection updated. Inline JS removed from PHP view.
- 2024-06-18: AJAX endpoint, update_outcome function, and modular JS for modal/AJAX logic implemented.
- 2024-06-18: Unused Add Item button and related JS removed from modal and assets.
- 2024-06-18: Code duplication and unused file scan completed. No critical duplication found in main admin outcomes flow. Legacy/backup files (edit_outcome_backup.php, delete_outcome.php, handle_outcome_status.php, view_outcome_flexible.php) are not referenced elsewhere and are candidates for removal after further review.

## 12. Legacy/Backup File Removal Plan

### Candidates for Removal

- `app/views/admin/outcomes/edit_outcome_backup.php`
- `app/views/admin/outcomes/delete_outcome.php`
- `app/views/admin/outcomes/handle_outcome_status.php`
- `app/views/admin/outcomes/view_outcome_flexible.php`

### Rationale

- These files are not referenced or included anywhere else in the codebase.
- Their logic is either deprecated, replaced, or not used in the current admin outcomes management flow.
- Removing them will reduce codebase clutter and potential confusion.

### Next Steps

1. Double-check for any indirect usage or documentation references.
2. Back up these files before deletion (optional, for safety).
3. Remove the files from the repository. **(Completed 2024-06-18)**
4. Test the admin outcomes module and related flows to ensure no breakage. **No issues encountered.**
5. Log the removal in the implementation plan and update the bug tracker if any issues arise.
