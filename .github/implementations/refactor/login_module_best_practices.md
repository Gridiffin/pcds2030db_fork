# Login Module Refactor - Best Practices Implementation Plan

**Last Updated:** 2025-07-18

## Overview
This document tracks the plan, progress, and decisions for refactoring the login module according to best practices, as outlined in `docs/project_structure_best_practices.md`, `docs/example_login_feature_workflow.md`, and the `refactortodo` rule.

---

## To-Do List

- [x] 1. **THINK:** Analyze current login implementation and compare with best practices
- [x] 2. **THINK:** Identify all files (PHP, JS, CSS, session, validation, etc.) involved in login
- [x] 3. **THINK:** Plan modular structure (views/partials, controller, model, API, JS, CSS)
- [x] 4. **REASON:** Weigh pros/cons of current vs. best-practice structure
- [x] 5. **SUGGEST:** Propose new structure and improvements
- [x] 6. **Approval:** Await user approval for plan
- [x] 7. **ACT:** Refactor backend (controller, model, API endpoint)
- [x] 8. **ACT:** Refactor frontend (views, partials, JS, CSS)
- [x] 9. **ACT:** Modularize JS (login.js, loginLogic.js) into shared folder
- [x] 10. **ACT:** Modularize CSS (login.css, form.css, messages.css, etc.) into shared folder
- [x] 11. **ACT:** Add/Update unit tests for login logic
- [x] 12. **ACT:** Update documentation and bug tracker if issues found
- [x] 13. **ACT:** Ensure all references and imports are correct (main.css, etc.)
- [x] 14. **ACT:** Test thoroughly (manual + automated)
- [x] 15. **ACT:** Mark each step as complete in this file

---

## Pros/Cons Analysis

### Current Structure
**Pros:**
- Simple, all-in-one file for login logic and view.
- Easy to find for small projects.

**Cons:**
- Mixes backend logic, session handling, and frontend rendering in one file (hard to maintain, not scalable).
- No separation of concerns (logic, view, validation, and assets are mixed).
- No modular JS or CSS (hard to test, reuse, or update styles/logic independently).
- No RESTful API endpoint for AJAX login (limits modern UX and security).
- No unit tests for login logic.
- No partials for form/messages/header/footer (harder to update UI or reuse components).
- Not ready for asset bundling (Vite) or scalable asset management.

### Best-Practice Modular Structure
**Pros:**
- Clear separation of concerns (controller, model, view, assets, API).
- Modular JS and CSS (easy to test, maintain, and scale).
- Supports AJAX login and modern UX.
- Unit tests for validation logic.
- Views use partials for easy UI updates and reuse.
- Asset bundling with Vite for performance and maintainability.
- Easier to debug, extend, and onboard new developers.

**Cons:**
- Requires initial setup and file reorganization.
- Slightly more complex structure (but justified for maintainability and scalability).

---

## Final Structure (as implemented)

### Backend
- `app/controllers/AuthController.php` — Handles login logic, form processing.
- `app/lib/UserModel.php` — User DB logic (find by username, verify password).
- `app/api/login.php` — RESTful API endpoint for AJAX login (returns JSON only).

### Frontend
- `app/views/admin/login.php` — Main login page (uses shared partials, sets asset bundles).
- `app/views/shared/login/login_form.php` — Login form HTML.
- `app/views/shared/login/login_messages.php` — Error/success messages.
- `app/views/shared/login/login_header.php` — Head/meta/CSS includes.
- `app/views/shared/login/login_footer.php` — Footer.

### JS
- `assets/js/shared/login.js` — Handles DOM, AJAX, uses loginLogic.js.
- `assets/js/shared/loginLogic.js` — Pure validation logic (testable, unit tested).

### CSS
- `assets/css/shared/login.css` — Main login CSS (imports submodules).
- `assets/css/shared/login/form.css` — Form-specific styles.
- `assets/css/shared/login/messages.css` — Message styles.
- `assets/css/shared/login/container.css` — Container/layout styles.

### Tests
- `tests/shared/loginLogic.test.js` — Unit tests for loginLogic.js.

### Asset Bundling
- Vite is used for asset bundling:
  - Entry: `assets/js/shared/login.js` (imports CSS)
  - Output: `/dist/js/login.bundle.js`, `/dist/js/login.bundle.css`
  - Bundles referenced in `login.php` via base layout.

### Other Improvements
- All AJAX endpoints return only JSON.
- All PHP logic is separated (no DB logic in views).
- All code is modular, maintainable, and secure.
- All changes and bugs are documented.

---

## Progress Log

- 2025-07-18: Complete refactor of login module to best practices. All logic, views, JS, and CSS are now modular and shared. Vite asset bundling and unit tests are in place. Old files and partials removed. All steps in the checklist are complete.

---

## Notes
- All changes follow the structure and workflow in the docs directory and the `refactortodo` rule.
- All code is modular, maintainable, and secure.
- All CSS/JS are in assets folders and imported via main.css/main.js or page-specific bundles.
- All PHP logic is separated (controller, model, view, API).
- All changes and bugs are documented. 

---

## Lessons Learned / Best Practices

1. **Always Use Dynamic Paths for Assets and AJAX**
   - Never hardcode asset or API paths; always use dynamic base paths or PHP variables to ensure compatibility with subdirectories and different environments.

2. **Modularize CSS and JS from the Start**
   - Organize all styles and scripts into logical, maintainable submodules. Use a main entry file that only imports submodules.

3. **Match HTML Classes/IDs to CSS/JS Selectors**
   - Ensure that all required classes and IDs in your CSS/JS are present in your HTML. Mismatches will break styling and functionality.

4. **Use ES Modules for All Modern JS**
   - Write all new JS as ES modules (`import`/`export`), and load with `<script type="module">`. Avoid UMD/global export patterns.

5. **Rebuild and Hard Refresh After Every Change**
   - After updating CSS/JS, always rebuild your Vite (or other) assets and hard refresh the browser to avoid caching issues.

6. **Validate Both Usernames and Emails if Supported**
   - If your login supports both, ensure your validation logic allows both, not just one.

7. **Set All Required Session Variables on Login**
   - Always set all session variables needed by your PHP views (e.g., `$_SESSION['username']`) after a successful login.

8. **Return All Required Data from APIs**
   - If frontend logic depends on user roles or other data, return them explicitly from your API endpoints.

9. **Redirect Based on Role Using Dynamic Paths**
   - Always redirect users based on their role, using dynamic base paths to avoid 404s in subdirectories.

10. **Remove All Old Asset References When Refactoring**
    - Clean up all references to deleted or moved files in HTML, PHP, and CSS to prevent 404 errors.

11. **Add Debug Logs When Troubleshooting**
    - Use `console.log` at key points (script load, event handlers, validation) to quickly identify where things break.

12. **Check for Disabled Buttons and Event Listeners**
    - Ensure buttons are not disabled by default and that event listeners are attached to the correct elements.

13. **Handle PHP Notices and Deprecated Warnings**
    - Use null coalescing (`?? ''`) and always check if session variables are set before using them in views.

14. **Document All Problems and Solutions**
    - Keep a running log of bugs and fixes in a shared file for future reference and onboarding. 