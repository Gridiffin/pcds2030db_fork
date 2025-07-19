# Login Module Refactor - Problems & Solutions Log

**Date:** 2025-07-18

## Problems & Solutions During Login Refactor

### 1. Asset Loading and 404 Errors

- **Problem:** 404 errors for old CSS/JS files (`login.css`, `login.bundle.js`, etc.) after refactoring and switching to Vite.
- **Cause:** Outdated references in HTML/PHP and `main.css` to deleted or moved files.
- **Solution:** Removed all old references, updated to use Vite bundles, and ensured correct paths for assets.

### 2. Styles Not Applying

- **Problem:** Login and welcome sections appeared unstyled or partially styled.
- **Cause:** HTML elements were missing required classes, and not all CSS was included after modularization.
- **Solution:** Matched HTML classes to CSS, restored all original styles, and modularized CSS into subfiles.

### 3. Vite Module/ESM Issues

- **Problem:** JS errors like “export declarations may only appear at top level of a module” and `window.validateEmail is not a function`.
- **Cause:** Vite bundles are ES modules; old UMD/global export patterns don’t work.
- **Solution:** Converted all JS to ES module syntax, used named imports/exports, and loaded scripts with `type="module"`.

### 4. JS Not Running or No Response

- **Problem:** No console logs, no validation, or no AJAX when clicking “Sign In.”
- **Cause:** JS not running due to caching, wrong script path, or event listeners not attaching due to missing IDs/classes.
- **Solution:** Ensured correct script tag, hard refreshed, matched IDs, and added debug logs.

### 5. Validation Logic Too Strict

- **Problem:** Only valid emails were accepted; usernames were rejected.
- **Cause:** Validation function only allowed email format.
- **Solution:** Updated validation to allow both usernames and emails.

### 6. AJAX Path Incorrect

- **Problem:** AJAX requests went to `/app/api/login.php` (web root), causing 404s.
- **Cause:** Hardcoded path did not account for project subdirectory.
- **Solution:** Used dynamic base path logic in JS to always target the correct API endpoint.

### 7. Role-Based Redirection Incorrect

- **Problem:** All users were redirected to the admin dashboard, or to the wrong path.
- **Cause:** Redirection logic did not check user role or used hardcoded paths.
- **Solution:** API now returns user role; JS redirects based on role and uses dynamic base path.

### 8. PHP Warnings for Undefined Session Variables

- **Problem:** Warnings about undefined `$_SESSION['username']` and deprecated `htmlspecialchars()` usage.
- **Cause:** Session variable not set after login.
- **Solution:** API now sets `$_SESSION['username']` on successful login.

### 9. Modularization and Vite Integration

- **Problem:** Ensuring all CSS/JS is modular, imported, and bundled correctly.
- **Solution:** Broke CSS into logical submodules, updated `login.css` to import them, and rebuilt Vite assets after every change.

---

**Result:**

- The login process is now fully modular, secure, and works for both usernames and emails.
- All assets are loaded via Vite, and redirection works for both admin and agency users.
- The codebase is maintainable, scalable, and follows best practices.

---

---

# Initiatives Module Refactor - Problems & Solutions Log

**Date:** 2025-01-21

## Problems & Solutions During Agency Initiatives Refactor

### 1. Hardcoded Asset Paths

- **Problem:** CSS and JS files were hardcoded with relative paths in the original `initiatives.php` and `view_initiative.php` files, causing 404 errors when moving to modular structure.
- **Cause:** Inline `<link>` and `<script>` tags with hardcoded paths like `../../assets/css/initiative-view.css`.
- **Solution:** Created `base.php` layout with dynamic asset loading using Vite bundles and `asset_url()` helper function.

### 2. Monolithic File Structure

- **Problem:** `view_initiative.php` was 911 lines long with mixed HTML, PHP logic, and JavaScript all in one file.
- **Cause:** Original development approach without separation of concerns.
- **Solution:** Broke down into modular partials: `initiative_overview.php`, `initiative_metrics.php`, `initiative_info.php`, `rating_distribution.php`, `programs_list.php`, `activity_feed.php`, `status_grid.php`.

### 3. Inline JavaScript and CSS

- **Problem:** Large blocks of inline JavaScript (Chart.js configurations) and CSS styles embedded directly in HTML.
- **Cause:** Quick development without proper asset organization.
- **Solution:** Extracted all JavaScript to modular ES6 files (`initiatives/view.js`, `initiatives/logic.js`) and CSS to modular files (`initiatives/view.css`, `initiatives/base.css`).

### 4. Duplicate Database Query Logic

- **Problem:** Similar database queries repeated across multiple files for getting initiative data and program information.
- **Cause:** No centralized data access functions.
- **Solution:** Created helper functions in `activity_helpers.php` and existing `lib/agencies/initiatives.php` to centralize common queries.

### 5. Inconsistent Status Handling

- **Problem:** Program status values were inconsistent (e.g., 'not-started', 'not_started', 'on-hold', 'on_hold') causing health score calculation errors.
- **Cause:** Different parts of the system using different status naming conventions.
- **Solution:** Added status normalization logic in the health score calculation with proper mapping array.

### 6. Chart.js Configuration Scattered

- **Problem:** Chart.js configurations for rating distribution and status grids were embedded inline, making them hard to maintain.
- **Cause:** Direct embedding without modular JavaScript approach.
- **Solution:** Moved all Chart.js configurations to `initiatives/view.js` with proper ES6 module exports and dynamic data loading.

### 7. Missing Error Handling

- **Problem:** No proper error handling for missing initiatives or access denied scenarios in the view.
- **Cause:** Basic validation without comprehensive error checking.
- **Solution:** Added proper initiative existence checks, agency access validation, and graceful error messages with redirects.

### 8. Activity Feed Performance Issues

- **Problem:** Activity feed was querying audit logs without proper indexing or limiting, potentially causing slow page loads.
- **Cause:** Unoptimized database queries for activity history.
- **Solution:** Added proper LIMIT clauses and optimized queries in `activity_helpers.php` with pagination support.

### 9. Rating Distribution Data Inconsistency

- **Problem:** Rating distribution chart was using inconsistent data sources, sometimes showing outdated or incorrect program ratings.
- **Cause:** Multiple data sources without proper synchronization.
- **Solution:** Standardized rating data retrieval through centralized query and proper data validation before chart rendering.

### 10. Path Duplication in Base.php Include

- **Problem:** `require_once(C:\laragon\www\pcds2030_dashboard_fork\app\app/views/layouts/base.php): Failed to open stream: No such file or directory`
- **Cause:** `PROJECT_ROOT_PATH` definition was using `dirname(dirname(dirname(__DIR__)))` which resolved to `C:\laragon\www\pcds2030_dashboard_fork\app\` instead of the actual project root `C:\laragon\www\pcds2030_dashboard_fork\`.
- **Solution:** Updated PROJECT_ROOT_PATH definition to use `dirname(dirname(dirname(dirname(__DIR__))))` to go up one more directory level to reach the actual project root. Now `PROJECT_ROOT_PATH . 'app/views/layouts/base.php'` resolves correctly.

### 11. Incorrect File Path References

- **Problem:** `require_once(C:\laragon\www\pcds2030_dashboard_fork\config/config.php): Failed to open stream: No such file or directory`
- **Cause:** Include paths were missing the `app/` prefix. Files like `config.php`, `lib/` directory are located within the `app/` directory, not in project root.
- **Solution:** Updated all include paths in `initiatives.php`, `view_initiative.php`, and `base.php` to use `PROJECT_ROOT_PATH . 'app/config/config.php'` and `PROJECT_ROOT_PATH . 'app/lib/...'` instead of missing the `app/` directory prefix. Fixed multiple instances including `initiative_functions.php`, `rating_helpers.php`, `db_names_helper.php`, and `program_status_helpers.php`.

### 12. Incorrect Layout Element Ordering

- **Problem:** Page header was appearing twice and layout elements (header, content, footer) were not in the correct order. Content was rendering after the base layout finished instead of being properly integrated.
- **Cause:** Initiatives pages were including `page_header.php` both inside `base.php` (line 89) and again after the base layout include. Content was being rendered outside the base layout structure.
- **Solution:** Refactored to use proper content file pattern - created `initiatives_content.php` and `view_initiative_content.php` partials and set `$contentFile` variable before including `base.php`. This ensures proper order: navigation → header → content → footer.

---

**Result:**

- Agency initiatives module is now fully modular with clean separation of concerns
- All assets are properly bundled through Vite with no hardcoded paths
- Database queries are centralized and optimized
- JavaScript is organized in ES6 modules with proper Chart.js integration
- CSS follows modular architecture with component-based organization
- Error handling and validation are comprehensive
- Performance is improved through optimized queries and proper asset loading

---

# Previous Bugs Tracker (pre-initiatives refactor)

## [2024-07-15] Fatal error: Unknown column 'users_assigned' in 'where clause' (User Deletion)

- **File:** app/lib/admins/users.php
- **Line:** 421 (delete_user function)
- **Error:**
  - Fatal error: Uncaught mysqli_sql_exception: Unknown column 'users_assigned' in 'where clause'
- **Cause:**
  - The code checked for program ownership using a non-existent 'users_assigned' column in the 'programs' table.
  - The actual schema uses 'created_by' to track program ownership.
- **Fix:**
  - Updated the code to use 'created_by' instead of 'users_assigned' in the SQL query.
  - No database changes required.
- **Status:** Fixed in code, 2024-07-15.
