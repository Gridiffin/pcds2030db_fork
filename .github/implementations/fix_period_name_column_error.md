# Fix 'Unknown column period_name' Error When Creating Reporting Periods

## Problem
When creating a new reporting period, the backend tries to insert a value into a non-existent `period_name` column in the `reporting_periods` table, causing the error:

    Error: Failed to create period: Unknown column 'period_name' in 'field list'

## Solution Plan
- [x] 1. Review the SQL insert statement in `app/ajax/save_period.php`.
- [x] 2. Remove any reference to `period_name` from the SQL and parameter binding.
- [x] 3. Ensure only valid columns are used: `year`, `quarter`, `start_date`, `end_date`, `status`, and (optionally) `is_standard_dates`.
- [x] 4. Test period creation to confirm the error is resolved.
- [x] 5. Mark this task as complete in this file.

## Notes
- The period name can be constructed dynamically in the UI or for display, but should not be stored in the database as a separate column.
- This change will align the backend with the actual database schema and prevent SQL errors.
