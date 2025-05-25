# Fix PHP Errors in Admin Views

This plan outlines the steps to resolve the pending PHP errors in `edit_outcome.php` and `view_outcome.php`.

## Task Breakdown

1.  **[ ] Fix `Undefined function 'get_sector_by_id'` in `edit_outcome.php`:**
    *   [ ] Locate the definition of `get_sector_by_id`.
    *   [ ] Ensure the file containing the function is correctly included in `edit_outcome.php`.

2.  **[ ] Fix `Undefined constant 'ASSET_VERSION'` in `edit_outcome.php`:**
    *   [ ] Verify `ASSET_VERSION` definition in `config.php` or `asset_helpers.php`.
    *   [ ] Ensure the defining file is included before `ASSET_VERSION` is used in `edit_outcome.php`.

3.  **[ ] Fix `Undefined function 'display_submission_status_badge'` in `view_outcome.php`:**
    *   [ ] Confirm function definition in `status_helpers.php`.
    *   [ ] Verify `status_helpers.php` is correctly included in `view_outcome.php`.
    *   [ ] Check for typos or conditional declaration issues.

4.  **[ ] Fix `Undefined function 'display_overall_rating_badge'` in `view_outcome.php`:**
    *   [ ] Confirm function definition in `rating_helpers.php`.
    *   [ ] Verify `rating_helpers.php` is correctly included in `view_outcome.php`.
    *   [ ] Check for typos or conditional declaration issues.

5.  **[ ] Review and Test:**
    *   [ ] After applying fixes, review the affected files.
    *   [ ] Test the `edit_outcome.php` and `view_outcome.php` pages to ensure errors are resolved and functionality is intact.
