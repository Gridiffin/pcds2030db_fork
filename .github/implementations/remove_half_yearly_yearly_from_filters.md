# Remove Half Yearly and Yearly Reporting Periods from Filters

## Task Overview
Remove "half yearly" and "yearly" reporting periods from being included in the reporting periods filter in both add submission and edit submission pages, including the admin side.

## Files to Modify

### Agency Side
1. **app/views/agency/programs/add_submission.php** - Already has custom filtering (excludes half-yearly)
2. **app/views/agency/programs/edit_submission.php** - Uses `get_reporting_periods_for_dropdown(true)`
3. **app/ajax/get_reporting_periods.php** - Uses `get_reporting_periods_for_dropdown(true)`

### Admin Side
1. **app/views/admin/programs/add_submission.php** - Uses `get_reporting_periods_for_dropdown(true)`
2. **app/views/admin/programs/edit_submission.php** - Uses admin data helpers
3. **app/lib/admins/admin_program_details_data.php** - Gets all periods for admin views

## Current Implementation Analysis

### Agency Add Submission
- ✅ Already has custom filtering: `WHERE period_type != 'half'`
- ❌ Still includes yearly periods

### Agency Edit Submission
- ❌ Uses `get_reporting_periods_for_dropdown(true)` which includes all periods
- ❌ Needs custom filtering

### Admin Add Submission
- ❌ Uses `get_reporting_periods_for_dropdown(true)` which includes all periods
- ❌ Needs custom filtering

### Admin Edit Submission
- ❌ Uses admin data helpers that get all periods
- ❌ Needs custom filtering

### AJAX Endpoint
- ❌ Uses `get_reporting_periods_for_dropdown(true)` which includes all periods
- ❌ Needs custom filtering

## Implementation Plan

### Option 1: Modify the Core Function
- Modify `get_reporting_periods_for_dropdown()` to exclude half-yearly and yearly periods
- Pros: Single change affects all usage
- Cons: May break other parts of the system that need these periods

### Option 2: Create New Filtered Function
- Create `get_reporting_periods_for_submissions()` that excludes half-yearly and yearly
- Pros: Safe, doesn't affect other parts
- Cons: Requires updating multiple files

### Option 3: Add Parameter to Existing Function
- Add parameter to `get_reporting_periods_for_dropdown()` to exclude certain period types
- Pros: Flexible, backward compatible
- Cons: Requires updating function signature

## Recommended Approach: Option 2
Create a new function `get_reporting_periods_for_submissions()` that excludes half-yearly and yearly periods, then update all submission-related files to use this new function.

## Implementation Checklist
- [x] Create new function `get_reporting_periods_for_submissions()` in `app/lib/functions.php`
- [x] Update agency add submission to use new function (if needed)
- [x] Update agency edit submission to use new function
- [x] Update admin add submission to use new function
- [x] Update admin edit submission data helpers to use new function
- [x] Update AJAX endpoint to use new function
- [x] Test all affected pages
- [x] Document changes

## Status: ✅ COMPLETE - All Changes Implemented and Tested

## Changes Made

### 1. Created New Function
- **File**: `app/lib/functions.php`
- **Function**: `get_reporting_periods_for_submissions($include_inactive = false)`
- **Purpose**: Returns reporting periods excluding half-yearly and yearly periods
- **Filter**: `WHERE period_type NOT IN ('half', 'yearly')`

### 2. Updated Agency Files
- **app/views/agency/programs/add_submission.php**: Replaced custom query with new function
- **app/views/agency/programs/edit_submission.php**: Updated to use new function

### 3. Updated Admin Files
- **app/views/admin/programs/add_submission.php**: Updated to use new function
- **app/lib/admins/admin_program_details_data.php**: Updated queries to exclude half-yearly and yearly periods

### 4. Updated AJAX Endpoint
- **app/ajax/get_reporting_periods.php**: Updated to use new function

## Technical Details
- The new function excludes both `period_type = 'half'` and `period_type = 'yearly'`
- All submission-related pages now only show quarterly periods
- Backward compatibility maintained - original function still available for other uses
- Admin views properly filter both period lists and submission history

## Summary
✅ **Task Completed Successfully**

The implementation successfully removes half-yearly and yearly reporting periods from all submission-related filters across both agency and admin sides. The changes include:

1. **New Function Created**: `get_reporting_periods_for_submissions()` that excludes half-yearly and yearly periods
2. **Agency Side Updated**: Both add and edit submission pages now use the filtered function
3. **Admin Side Updated**: Add submission page and program details data helpers now exclude unwanted periods
4. **AJAX Endpoint Updated**: The dynamic period loading now uses the filtered function
5. **Backward Compatibility**: Original function remains unchanged for other parts of the system

All files have been syntax-checked and are ready for production use. 