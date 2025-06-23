# Fix Focal Agency Dashboard Calculations

## Problem Description
The admin dashboard statistics have an inconsistency in how focal agencies are handled:
- **Total Agencies count** excludes focal agencies (`role = 'focal'`)
- **Agencies Reported count** includes focal agencies when they submit data
- This can cause completion percentages > 100% and inaccurate dashboard statistics

## Root Cause
In `app/lib/admins/statistics.php`, the `get_period_submission_stats()` function only counts users with `role = 'agency'` for total agencies, but counts submissions from both 'agency' and 'focal' users in agencies reported.

## Solution Steps

### Step 1: Update Total Agencies Query
- [x] Fix the total agencies count to include both 'agency' and 'focal' roles
- [x] Update the query in `get_period_submission_stats()` function

### Step 2: Verify Consistency Across Codebase
- [x] Check other admin statistics functions for similar issues
- [x] Ensure all agency-related counts are consistent
- [x] Review any other dashboard calculation functions
- [x] Update `get_admin_dashboard_stats()` function
- [x] Update `get_sector_data_for_period()` function  
- [x] Update report data API for sector leads

### Step 3: Test the Fix
- [x] Verify dashboard cards show correct percentages
- [x] Test with scenarios where focal agencies submit data  
- [x] Ensure no completion percentages exceed 100%

### Step 4: Documentation
- [x] Add comments explaining the focal agency inclusion
- [x] Update any relevant documentation

## Files Modified
- ✅ `app/lib/admins/statistics.php` - Fixed `get_period_submission_stats()`, `get_admin_dashboard_stats()`, and `get_sector_data_for_period()` functions
- ✅ `app/api/report_data.php` - Updated sector leads query to include focal agencies

## Changes Made
1. **Fixed Total Agencies Count**: Updated 3 instances in `app/lib/admins/statistics.php` where `role = 'agency'` was changed to `role IN ('agency', 'focal')`
2. **Consistent Sector Data**: Fixed sector agency count to include focal agencies
3. **Report Consistency**: Updated sector leads in reports to include focal agencies
4. **Added Comments**: Included explanatory comments about focal agency inclusion

## Expected Outcome
- ✅ Dashboard statistics accurately reflect both agency and focal agency participation
- ✅ Completion percentages are calculated correctly  
- ✅ Consistent treatment of focal agencies across all admin dashboard calculations
- ✅ No more possibility of completion percentages exceeding 100% due to focal agency mismatch
