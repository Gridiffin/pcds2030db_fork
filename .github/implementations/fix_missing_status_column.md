# Fix: Fatal error due to missing 'status' column in program_submissions

## Problem
- The file `app/views/admin/programs/edit_program.php` throws a fatal error because it tries to select a non-existent `status` column from the `program_submissions` table.
- The actual status/rating is stored inside the `content_json` column as a JSON field (usually as `rating`).

## Solution Steps
- [x] Identify the problematic SQL query and PHP code.
- [x] Update the SQL query to remove `status` from the SELECT list.
- [x] Adjust the PHP code to extract the status from the decoded `content_json` field.
- [ ] Test the fix to ensure the program status is displayed correctly and the error is resolved.

## Notes
- This change will ensure compatibility with the current database schema and prevent runtime errors.
- If other files use a similar pattern, consider refactoring them as well.

---

**Mark each step as complete after implementation.**
