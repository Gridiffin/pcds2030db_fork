# Refactor Plan: Admin Initiatives Management Page

## Target: `app/views/admin/initiatives/manage_initiatives.php`

---

## 1. Preparation & Analysis

- [x] Identify all related files (views, partials, CSS, JS, PHP logic, AJAX/API, tests)
- [x] Map data flow: controller/handler → model/helper → view → assets
- [x] List dynamic features (search/filter, table, status, actions)

## 2. Directory & File Structure Planning

- [ ] Main view: `app/views/admin/initiatives/manage_initiatives.php`
- [ ] Partials: `app/views/admin/initiatives/partials/`
  - `search_filter_form.php`
  - `initiatives_table.php`
- [ ] CSS: `assets/css/admin/initiatives/manage_initiatives.css`
- [ ] JS: `assets/js/admin/initiatives/manageInitiatives.js`
- [ ] Logic: Ensure all DB/business logic is in helpers/models
- [ ] AJAX/API (if needed): `app/ajax/admin_manage_initiatives.php`
- [ ] Unit tests: `tests/admin/manageInitiativesLogic.test.js`

## 3. Move & Refactor Files

- [x] Extract inline styles to CSS file
- [x] Extract search/filter form and initiatives table to partials
- [ ] Ensure all business logic is in helpers/models
- [x] Add AJAX endpoint and JS if dynamic search/filter is required

## 4. Vite Asset Bundling

- [x] Update `vite.config.js` for new CSS/JS
- [x] Import CSS in JS entry file
- [x] Reference bundled assets in the view via base layout

## 5. Base Layout & Asset Injection

- [ ] Use `layouts/base.php` for asset injection
- [ ] Set `$cssBundle`, `$jsBundle`, `$contentFile` in the page

## 6. Documentation & Bug Tracking

- [ ] Update this file after each change
- [ ] Log any bugs in `docs/bugs_tracker.md`

## 7. Unit Testing

- [x] Write/update unit tests for JS logic

---

## Progress Log

- 2024-06-18: Plan created, initial analysis complete. Awaiting next steps.
- 2024-06-18: Extracted all inline styles from manage_initiatives.php to assets/css/admin/initiatives/manage_initiatives.css and referenced the new CSS file in the PHP view.
- 2024-06-18: Extracted search/filter form and initiatives table to partials and updated manage_initiatives.php to use them.
- 2024-06-18: Added AJAX endpoint (app/ajax/admin_manage_initiatives.php) and modular JS (assets/js/admin/initiatives/manageInitiatives.js) for dynamic search/filter, and included the JS in the view.
- 2024-06-18: Updated Vite config and imported CSS in JS for admin initiatives management asset bundling.
- 2024-06-18: Updated manage_initiatives.php to use the base layout and dynamic asset injection, with all main content in a content partial.
- 2024-06-18: Created Jest unit tests for manageInitiatives.js logic (formatDate, renderTable) and made them exportable for testing.

---

## Notes

- Follow all best practices from `docs/project_structure_best_practices.md`
