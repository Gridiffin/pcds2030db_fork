# Fix: Undefined 'draft_outcomes' and 'sectors_with_outcomes' Warnings in Admin Dashboard

## Problem

- Warning: Undefined array key "draft_outcomes" in `app/views/admin/dashboard/dashboard.php` on line 393.
- Warning: Undefined array key "sectors_with_outcomes" in `app/views/admin/dashboard/dashboard.php` on line 403.
- Warning: Undefined array key "submission_date" in `app/views/admin/dashboard/dashboard.php` on line 494.
- Deprecated: strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated in `app/views/admin/dashboard/dashboard.php` on line 494.
- This occurs because the `get_outcomes_statistics` function and recent submissions array do not always return these keys in their result arrays.

## Solution Plan (TODO List)

- [x] 1. Check the implementation of `get_outcomes_statistics` to ensure it always returns the `draft_outcomes` key (even if 0).
- [x] 2. Update the function if necessary to guarantee all expected keys are present.
- [x] 3. Update the dashboard view to use the null coalescing operator (`?? 0`) for `draft_outcomes` for extra safety.
- [x] 4. Check the implementation of `get_outcomes_statistics` to ensure it always returns the `sectors_with_outcomes` key (even if 0).
- [x] 5. Update the function if necessary to guarantee the `sectors_with_outcomes` key is present.
- [x] 6. Update the dashboard view to use the null coalescing operator (`?? 0`) for `sectors_with_outcomes` for extra safety.
- [ ] 7. Update the dashboard view to use the null coalescing operator (`?? ''`) for `submission_date` and display a placeholder if missing.
- [ ] 8. (Optional) Check the function that populates `$recent_submissions` to ensure it always provides a valid `submission_date`.
- [ ] 9. Test the dashboard to confirm the warnings are resolved and values display correctly.
- [ ] 10. Mark each step as done in this file as we complete them.
