# Fix Unsubmit/Resubmit Buttons Visibility Issue - SOLUTION FOUND

## Problem Statement
The unsubmit/resubmit buttons are not visible in the admin program list, even though the code exists and the period_id assignment issue has been resolved.

## Investigation Results

- [x] 1. Check if `get_admin_programs_list()` is returning the required fields (`submission_id`, `is_draft`, `status`)
- [x] 2. Verify that programs have actual submissions in the `program_submissions` table for the current period
- [x] 3. Check if the LEFT JOIN in the SQL query is working correctly
- [x] 4. Examine the button display logic conditions in `programs.php`
- [x] 5. Test with a known program that has submissions to see if buttons appear
- [x] 6. Debug the data structure being passed to the view
- [ ] 7. Implement a fix to ensure buttons appear when appropriate

## Root Cause Analysis - CRITICAL ISSUE IDENTIFIED

**PROBLEM**: The `get_admin_programs_list()` function has a date filter that only shows programs created within the current period's date range:

```php
if ($period_info) {
    $where_clauses[] = "(p.created_at >= ? AND p.created_at <= ?)";
    $params[] = $period_info['start_date'] . ' 00:00:00';
    $params[] = $period_info['end_date'] . ' 23:59:59';
}
```

**This means:**
- Only programs created within the current period will be shown
- Programs created before the current period won't appear, even if they have submissions for the current period
- This is why the unsubmit/resubmit buttons aren't visible - the programs themselves aren't being returned!

## Solution Approach

**Option 1: Remove the date filter entirely (Recommended)**
- Show all programs regardless of creation date
- Filter by submissions for the current period instead

**Option 2: Modify the filter logic**
- Show programs that either:
  - Were created in the current period, OR
  - Have submissions for the current period

**Option 3: Add a toggle for date filtering**
- Allow admins to choose whether to filter by creation date

## Implementation Plan

- [ ] Remove the problematic date filter from `get_admin_programs_list()`
- [ ] Test that all programs now appear in the list
- [ ] Verify that unsubmit/resubmit buttons appear for programs with submissions
- [ ] Ensure the LEFT JOIN still works correctly for submission data
- [ ] Test with different scenarios (draft submissions, final submissions, no submissions)

## Code Changes Required

In `app/lib/admins/statistics.php`, in the `get_admin_programs_list()` function:

**Remove these lines:**
```php
// Add program creation date filtering based on the viewing_period_id's start and end dates
if ($period_info) {
    $where_clauses[] = "(p.created_at >= ? AND p.created_at <= ?)";
    $params[] = $period_info['start_date'] . ' 00:00:00';
    $params[] = $period_info['end_date'] . ' 23:59:59';
    $param_types .= 'ss';
}
```

This will allow all programs to be shown, and the LEFT JOIN will properly include submission data for the current period, making the unsubmit/resubmit buttons visible.