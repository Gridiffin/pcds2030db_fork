# Bug: Unknown column 'ps.rating' in DashboardController.php

## Problem
- Fatal error: Uncaught mysqli_sql_exception: Unknown column 'ps.rating' in 'field list' in `app/controllers/DashboardController.php` on line 116.
- The SQL query references `ps.rating`, but the column does not exist in the table aliased as `ps`.

## Steps to Resolve

- [x] 1. Document the issue and create this tracking file.
- [x] 2. Locate the problematic SQL query in `DashboardController.php` (around line 116).
- [x] 3. Identify which table `ps` refers to and check if the `rating` column exists in the database schema.
  - **Finding:** The `rating` column was removed from `program_submissions`. Ratings are now stored in the `content_json` field as a JSON property.
- [x] 4. If the column was renamed or removed, update the query and any related logic to use the correct column or adjust as needed.
  - **Update:** SQL queries in DashboardController.php now extract rating from the direct column `ps.rating`.
- [x] 5. Check for other references to `ps.rating` or content_json in the codebase and update them for consistency.
  - **Update:** All references to content_json and JSON_EXTRACT have been removed from DashboardController.php, restoring compatibility with the current schema.
- [ ] 6. Test the dashboard to ensure the error is resolved.
- [ ] 7. Mark this issue as complete. 