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

# Previous Bugs Tracker (pre-login refactor)

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

## [2024-07-19] Outcome Edit Not Saving Latest Edits (Admin Outcome Edit)

- **File:** app/views/admin/outcomes/edit_outcome.php
- **Error:**
  - When editing the outcome table (cells, row/column names) and clicking "Save Changes" while still editing a contenteditable field, the latest changes were not saved.
- **Cause:**
  - The JavaScript collected data from the DOM on form submit, but if a contenteditable cell was still focused, its latest value was not committed to the DOM and thus not included in the data sent to the backend.
- **Fix:**
  - On form submission, all `.editable-hint` elements are programmatically blurred before collecting data, ensuring the latest edits are committed and saved.
- **Status:** Fixed in code, 2024-07-19.
