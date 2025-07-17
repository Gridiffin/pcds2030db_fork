# Remove Agency Dashboard Period Selector Functionality

## Problem
The agency dashboard currently allows users to select reporting periods via a period selector (dropdown, view mode toggle, etc.). The requirement is to remove **all** period selector functionality from the agency dashboard, including:
- The period selector UI (dropdown, toggles, etc.)
- All related PHP logic (handling `period_id`, view modes, etc.)
- All related JS logic (period selector, AJAX, etc.)
- Any backend logic that is only relevant for period selection in the agency dashboard
- Any references to period selector assets/scripts

## Step-by-step Plan

- [x] 1. Remove the period selector UI from `app/views/agency/dashboard/dashboard.php` (remove the PHP include and any related markup)
- [x] 2. Remove all period selector JS logic from `assets/js/period_selector.js` and references to it in the dashboard
- [x] 3. Remove all period selector PHP logic from `app/lib/period_selector_dashboard.php` (delete file)
- [x] 4. Remove all period_id handling in the agency dashboard controller/view (`dashboard.php`), and always use the current period
- [x]5date backend logic in `DashboardController.php` and `app/views/agency/dashboard/ajax/agency_dashboard_data.php` to remove period_id as a filter (always use current period)
- [x] 6. Remove any period selector-related CSS if present
- [x] 7. Test the dashboard to ensure it loads and displays data for the current period only
- [x] 8Delete any test or obsolete files related to the period selector

## Additional Fix Applied
- [x] Fixed KPI tiles to use `status` column from `programs` table instead of program submissions
- [x] Updated `DashboardController.php` to map status values correctly: 'active =on-track, 'delayed' = delayed, 'completed = completed
- [x] Created `get_agency_program_status()` function in `app/lib/agencies/statistics.php` to use program status directly
- [x] Updated agency dashboard data endpoint to use the new function

## Notes
- Ensure that all dashboard data, charts, and stats now reflect only the current period (no historical selection)
- Remove any URL/query parameter handling for `period_id` and `view_mode` in the agency dashboard
- Remove any documentation or comments referencing the period selector in the agency dashboard
- Ensure no references to period selector JS/CSS remain in the dashboard
- KPI tiles now correctly display data based on program status: active (on-track), delayed, completed

---

**Mark each step as complete as you finish it.** 