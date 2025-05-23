# Fix for Undefined Function get_program_edit_history()

## Problem
The function `get_program_edit_history()` is called in edit_program.php but is not defined in the current codebase. This was causing a fatal error when accessing the edit program page.

## Solution
- [x] Identify where the function should be defined (found in deprecated/includes/agencies/programs.php)
- [x] Add the function to app/lib/agencies/programs.php
- [x] Ensure the function is compatible with the current database structure
- [x] Test the function to confirm it resolves the error

## Implementation Details
The function retrieves a program's edit history for display in the admin interface. It:
1. Fetches the current program details from the database
2. Gets all submissions related to the program, ordered by date
3. Processes the content_json data for each submission
4. Returns a formatted history array for display

This fix completes the migration from the old project structure to the new one by ensuring all required functions are available in the updated codebase.

## Dependencies
- app/lib/agencies/programs.php
- app/views/admin/edit_program.php
- Database tables: programs, program_submissions, users, reporting_periods
