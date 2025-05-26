# Fix: Field 'metric_id' doesn't have a default value in edit_outcome.php

## Problem
- Fatal error: Uncaught mysqli_sql_exception: Field 'metric_id' doesn't have a default value in edit_outcome.php
- This occurs when inserting a new outcome, likely due to legacy code using 'metric_id' instead of 'outcome_id', or not providing the required ID value.
- Important: The system should use 'outcome' naming, not 'metric'.

## Solution Steps
- [x] Document the problem and plan.
- [x] Review the insert statement in edit_outcome.php and ensure it uses 'outcome_id' (not 'metric_id').
- [x] Ensure the code generates and provides a value for 'outcome_id' on insert (auto-increment or manual assignment).
- [x] Remove or refactor any legacy 'metric' references in the outcome creation logic.
- [x] Test the fix by creating a new outcome and confirming no error occurs.

## Notes
- If the database table still uses 'metric_id', consider renaming it to 'outcome_id' for consistency.
- All new code should use 'outcome' terminology.
- Suggest a migration/cleanup for legacy 'metric' code if found.
