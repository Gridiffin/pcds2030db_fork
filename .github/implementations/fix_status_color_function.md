<!-- filepath: d:\\laragon\\www\\pcds2030_dashboard\\.github\\implementations\\fix_status_color_function.md -->
# Fix Status Color Function and Logic

The current implementation has a conflict with the status of a program and the status of a reporting period (open/closed). This plan outlines the steps to resolve this issue and remove the dependency on `status_helpers.php`.

## TODO

- [x] **Understand the existing `get_status_color()` function and its usage.**
  - The function `get_status_color()` was likely in `app/lib/status_helpers.php`.
  - It was used in `app/views/admin/reporting_periods.php` to determine the color of the status badge.
- [x] **Identify the conflict:**
  - The `reporting_periods` table has a `status` column (enum: 'open', 'closed').
  - The `program_submissions` table also has a `status` column (enum: 'target-achieved', 'on-track-yearly', 'severe-delay', 'not-started').
  - The `get_status_color()` function was probably designed for program submission statuses, leading to incorrect color mapping for reporting period statuses.
- [x] **Remove `status_helpers.php`:**
  - As requested, this helper file will no longer be used.
- [x] **Create a new helper function for reporting period status colors:**
  - Create a new function, for example, `get_reporting_period_status_color($status)` in `app/lib/functions.php`.
  - This function will take the reporting period status ('open' or 'closed') as input.
  - It will return a color string (e.g., 'success' for 'open', 'danger' for 'closed').
- [x] **Update `app/views/admin/reporting_periods.php`:**
  - Include `app/lib/functions.php` if not already included.
  - Replace the call to the old `get_status_color()` with the new `get_reporting_period_status_color()`, passing the correct status value from the `$period` array.
- [ ] **Verify the fix:**
  - Check the reporting periods page in the admin panel to ensure the status colors are displayed correctly for 'open' and 'closed' periods.
- [ ] **Consider a more robust solution for status handling (Optional but Recommended):**
    - Instead of separate functions for colors, consider creating a more generic status handling mechanism or classes if statuses are used in many places with different meanings. This might involve:
        - Defining status constants.
        - Using a configuration array for status properties (label, color, icon).
        - This will make the code more maintainable and less prone to naming conflicts.
- [ ] **Review other files for `get_status_color()` usage:**
    - Search the codebase for any other instances where `get_status_color()` might be used and update them if they refer to program statuses or create specific helper functions as needed.

## Files to Modify:

- `d:\laragon\www\pcds2030_dashboard\app\lib\functions.php` (Create new helper function)
- `d:\laragon\www\pcds2030_dashboard\app\views\admin\reporting_periods.php` (Update function call)
- `d:\laragon\www\pcds2030_dashboard\app\lib\status_helpers.php` (To be removed or its content for program status moved elsewhere if needed)

## Database Context:

- **`reporting_periods` table:**
    - `period_id` (INT, PK)
    - `year` (INT)
    - `quarter` (INT)
    - `start_date` (DATE)
    - `end_date` (DATE)
    - `status` (ENUM: 'open', 'closed') - This is the status we need to display with the correct color.
- **`program_submissions` table:**
    - `submission_id` (INT, PK)
    - `program_id` (INT, FK)
    - `period_id` (INT, FK)
    - `status` (ENUM: 'target-achieved', 'on-track-yearly', 'severe-delay', 'not-started') - This status should have its own color logic if displayed.

This plan ensures that the reporting period status is handled correctly and independently, and it removes the problematic `status_helpers.php`.
