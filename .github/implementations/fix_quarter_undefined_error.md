# Fix Undefined Array Key "quarter" Error

## Problem
The file `app/views/agency/outcomes/submit_outcomes.php` is showing a warning:
```
Warning: Undefined array key "quarter" in C:\laragon\www\pcds2030_dashboard\app\views\agency\outcomes\submit_outcomes.php on line 90
```

## Root Cause Analysis
1. The `get_current_reporting_period()` function returns data from the `reporting_periods` table
2. The database table has columns: `period_id`, `year`, `period_type`, `period_number`, `start_date`, `end_date`, `status`
3. The code is trying to access `$current_period['quarter']` but this key doesn't exist
4. The `quarter` value should be derived from `period_number` when `period_type` is 'quarter'

## Solution Plan

### Task 1: Analyze the current code usage
- [x] Identify where `$current_period['quarter']` is being used
- [x] Check if there are other similar issues in the codebase

### Task 2: Fix the immediate issue in submit_outcomes.php
- [x] Add proper array key checking before accessing `quarter`
- [x] Derive the quarter value from `period_number` when `period_type` is 'quarter'
- [x] Handle cases where `period_type` is not 'quarter' (could be 'half' or 'yearly')

### Task 3: Improve the get_current_reporting_period() function
- [x] Modify the function to include derived fields like `quarter` and `half`
- [x] Ensure backward compatibility
- [x] Add proper error handling

### Task 4: Check for similar issues across the codebase
- [x] Search for other files that might have the same issue
- [x] Fix any other instances found

### Task 5: Test the fix
- [x] Verify the fix works correctly
- [x] Test with different period types (quarter, half, yearly)
- [x] Ensure no regression issues

---

**Final Note:**
- The fix was implemented, tested, and verified using a custom test script.
- All usages of `$period['quarter']` and `$current_period['quarter']` now work as expected.
- The test script was deleted after successful verification.
- Implementation complete.

## Implementation Steps

1. **Immediate Fix**: Add proper array key checking in submit_outcomes.php
2. **Long-term Fix**: Enhance the get_current_reporting_period() function
3. **Code Review**: Check for similar patterns across the codebase
4. **Testing**: Verify the fix works in all scenarios

## Files to Modify
- `app/views/agency/outcomes/submit_outcomes.php` (immediate fix)
- `app/lib/functions.php` (enhance get_current_reporting_period function)
- Any other files found with similar issues 