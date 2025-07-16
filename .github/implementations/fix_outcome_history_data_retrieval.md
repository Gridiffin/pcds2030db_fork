# Fix Outcome History Data Retrieval in Admin

## Problem

- The file `app/views/admin/outcomes/outcome_history.php` calls `get_outcome_data()`, which is undefined, causing a fatal error.
- The correct function to retrieve outcome data by ID is `get_outcome_by_id($id)`, defined in `app/lib/admins/outcomes.php`.
- The returned data structure may use different keys (e.g., `title` instead of `table_name`).

## Solution

- Replace the call to `get_outcome_data($metric_id)` with `get_outcome_by_id($metric_id)`.
- Update all variable usages to match the structure returned by `get_outcome_by_id`.
- Test the page to ensure outcome details and history display correctly.

## Implementation Plan

- [x] 1. Analyze the cause of the fatal error and identify the correct function to use.
- [x] 2. Replace `get_outcome_data($metric_id)` with `get_outcome_by_id($metric_id)` in `outcome_history.php`.
- [x] 3. Update all references to outcome data fields to match the new structure (e.g., use `title` instead of `table_name`).
- [ ] 4. Test the page to ensure it works and displays correct data.
- [ ] 5. Mark this checklist as complete after implementation.
