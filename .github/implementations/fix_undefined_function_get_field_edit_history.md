# Fix for Undefined Function get_field_edit_history()

## Problem
The function `get_field_edit_history()` is called in edit_program.php (line 395) but is not defined in the current codebase. This was causing a fatal error when accessing the edit program page.

## Solution
- [x] Identify where the function should be defined (found in deprecated/includes/agencies/programs.php)
- [x] Add the function to app/lib/agencies/programs.php
- [x] Ensure the function is compatible with the current database structure
- [x] Test the function to confirm it resolves the error

## Implementation Details
The `get_field_edit_history()` function is used to track changes to specific fields across program submissions, making it useful for displaying change history in the admin interface. It:

1. Takes an array of submissions, a field name to track, and optional settings
2. Processes submissions from oldest to newest (reversed order)
3. Handles both direct field access and nested fields within JSON content
4. Returns a history of changes to the specified field

This function is particularly important for the program history feature that shows how program details have changed over time.

## Dependencies
- app/lib/agencies/programs.php
- app/views/admin/edit_program.php
- get_program_edit_history() function which provides the submissions array
