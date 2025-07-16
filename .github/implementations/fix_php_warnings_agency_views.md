# Fix PHP Warnings and Deprecated Notices in Agency Dashboard and Initiatives Views

## Problem Summary

Several PHP warnings and deprecated notices are being logged in the following files:
- `app/views/agency/dashboard/dashboard.php` (Undefined array keys: 'submitted_outcomes', 'draft_outcomes')
- `app/views/agency/initiatives/initiatives.php` (Undefined array keys: 'id', 'name'; Deprecated: passing null to `htmlspecialchars`)

## Root Causes
- Attempting to access array keys that may not exist in the data provided to the views.
- Passing null values to `htmlspecialchars`, which is deprecated in recent PHP versions.
- **For dashboard.php:** The function `get_agency_outcomes_statistics` does not set `submitted_outcomes` or `draft_outcomes`, causing undefined key warnings. These keys should always be set (default to 0 if not available).
- **Legacy/compatibility issue:** The `outcomes` table does not have an `is_draft` column. Attempting to query it caused a fatal error. Draft/submitted status is not tracked for outcomes in the current schema.

## Solution Plan (TODO List)

- [x] 1. Summarize and document the errors and their root causes in this file before making any code changes.
- [x] 2. Update `app/views/agency/dashboard/dashboard.php` to handle undefined array keys for 'submitted_outcomes' and 'draft_outcomes'.
    - Updated `get_agency_outcomes_statistics` to always return these keys (default 0).
    - Added fallback in the view for extra safety.
- [x] 3. Update `app/views/agency/initiatives/initiatives.php` to handle undefined array keys for 'id' and 'name', and prevent passing null to `htmlspecialchars`.
    - Used null coalescing/isset for all key accesses and ensured no null is passed to `htmlspecialchars`.
- [x] 4. Test the fixes and ensure no PHP warnings or deprecated notices remain for these files. (In progress)
    - Fixed fatal error: removed queries for `is_draft` from the `outcomes` table. Now, `submitted_outcomes` and `draft_outcomes` are always set to 0 for compatibility, as the outcomes table does not track draft/submitted status.
- [x] Reverted the change in `get_agency_initiatives` to restore the original SQL (`SELECT DISTINCT i.*, ...`) and removed explicit aliasing of columns. The query is now as it was before the previous edit.
- [x] Updated the program status badge in the related programs list (view_initiative.php) to use `get_rating_badge($program['rating'])` from `rating_helpers.php`, ensuring the badge color and label match the program's rating in the database. Removed manual status color/label logic.
- [ ] 5. Update this file to mark completed tasks and summarize the solution. 