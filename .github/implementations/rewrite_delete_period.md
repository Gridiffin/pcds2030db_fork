## Rewrite and Fix `delete_period.php`

**Problem:** The `app/ajax/delete_period.php` script has several issues, including incorrect include paths, non-JSON responses, and undefined function errors (e.g., `is_admin()`). This prevents the successful deletion of reporting periods.

**Goal:** Rewrite the script to be robust, ensure all dependencies are correctly loaded, and that it consistently returns valid JSON responses.

**Error Log (Latest):**
```
PHP Fatal error:  Uncaught Error: Call to undefined function is_admin() in D:\laragon\www\pcds2030_dashboard\app\ajax\delete_period.php on line 51
```

**Solution Steps:**

1.  [ ] **Verify `ROOT_PATH` Definition:**
    *   Ensure `app/config/config.php` is loaded first and correctly defines `ROOT_PATH`.
2.  [ ] **Identify and Include All Necessary Files:**
    *   Locate the definition of `is_admin()`. It's likely in `app/lib/functions.php` or `app/lib/admin_functions.php`.
    *   Ensure the file containing `is_admin()` is included.
    *   Ensure `app/lib/session.php` (which should handle `session_start()`) is included early.
    *   Ensure `app/lib/db_connect.php` is included for database operations.
3.  [ ] **Standardize Script Structure:**
    *   Place `header('Content-Type: application/json');` at the very beginning of the script, after `ob_start()` if used for full control.
    *   Use output buffering (`ob_start()`, `ob_clean()`, `ob_end_flush()`) carefully to prevent any non-JSON output.
    *   Implement a global try-catch block to handle exceptions and ensure a JSON error response.
    *   Define a helper function to send JSON responses (success or error) and exit, to avoid repetition and ensure consistency.
4.  [ ] **Rewrite `app/ajax/delete_period.php`:**
    *   Apply the structural changes from step 3.
    *   Verify user authentication (`is_logged_in()`) and authorization (`is_admin()`) correctly.
    *   Validate input parameters (`period_id`).
    *   Perform the database deletion operation.
    *   Return appropriate JSON responses for success, failure, errors, or unauthorized access.
5.  [ ] **Test Thoroughly:**
    *   Test deleting a period successfully.
    *   Test deleting with an invalid/missing `period_id`.
    *   Test deleting when not logged in or not an admin.
    *   Check browser console and PHP error logs for any issues.
