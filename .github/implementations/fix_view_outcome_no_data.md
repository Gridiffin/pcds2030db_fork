# Bug: Outcome Data Not Retrieved in Admin View

## Problem

When viewing an outcome in `app/views/admin/outcomes/view_outcome.php`, the page does not retrieve or display the outcome data from the database. The table is empty or shows no data.

## Possible Causes

- The function `get_outcome_by_id` may not be fetching the correct data from the database.
- The outcome data may not be stored in the expected format (e.g., `data_json` field is empty or malformed).
- There may be a mismatch between the database schema and the code's expectations.
- The parsing of the JSON data may be failing.
- The outcome ID may not be passed or used correctly.

## Investigation Plan

- [x] 1. Review how `get_outcome_by_id` works and what it returns.
- [x] 2. Check the structure and content of the outcomes table in the database.
- [x] 3. Verify that the outcome data (especially `data_json`) is present and correctly formatted for the given outcome ID.
- [x] 4. Check how the data is parsed and used in `view_outcome.php`.
- [x] 5. Identify where the data retrieval or parsing fails.
- [x] 6. Implement a fix to ensure data is correctly fetched and displayed.
- [x] 7. Test the fix and mark this checklist as complete.

## Root Cause

- The admin `view_outcome.php` is trying to parse `$outcome['data_json']`, but the outcomes table only has a `data` field (which is already decoded by `get_outcome_by_id`).
- The agency side uses `$outcome['data']` directly as the flexible structure.

## Solution Plan

- [x] Update `view_outcome.php` to use `$outcome['data']` as the flexible structure, just like the agency side.
- [x] Remove all references to `data_json` in the admin view.
- [x] Ensure the flexible structure is parsed and displayed correctly.
- [x] Test the fix and update this file.

## Result

- The admin outcome view now uses the correct flexible structure and displays data as expected.
- The fatal error was fixed by ensuring the code loops through `$rows` and uses `$row['metrics']` or `$row['data']` for cell values, not `$data_array[$row_label]`.

## Additional Notes

- Ensure null safety and backward compatibility with legacy data formats.
- Suggest improvements if any code smells or inefficiencies are found.
