
# Fix Unsubmit/Resubmit Buttons Visibility Issue

## Problem Statement
The unsubmit/resubmit buttons are not visible in the admin program list, even though the code exists and the period_id assignment issue has been resolved.

## Investigation Plan

- [ ] 1. Check if `get_admin_programs_list()` is returning the required fields (`submission_id`, `is_draft`, `status`)
- [ ] 2. Verify that programs have actual submissions in the `program_submissions` table for the current period
- [ ] 3. Check if the LEFT JOIN in the SQL query is working correctly
- [ ] 4. Examine the button display logic conditions in `programs.php`
- [ ] 5. Test with a known program that has submissions to see if buttons appear
- [ ] 6. Debug the data structure being passed to the view
- [ ] 7. Implement a fix to ensure buttons appear when appropriate

## Root Cause Analysis
The buttons depend on these conditions:
```php
<?php if (isset($program['submission_id'])): ?>
    <?php if (!empty($program['is_draft'])): ?>
        <!-- Show Resubmit button -->
    <?php elseif (isset($program['status']) && $program['status'] !== null): ?>
        <!-- Show Unsubmit button -->
    <?php endif; ?>
<?php endif; ?>
```

**Key Requirements:**
- `$program['submission_id']` must be set
- Either `$program['is_draft']` is not empty OR `$program['status']` is set and not null

## Debugging Steps
- [ ] Add debug output to see what data is being returned by `get_admin_programs_list()`
- [ ] Check if the SQL query is correctly joining with `program_submissions`
- [ ] Verify that the current period has program submissions
- [ ] Ensure the JSON_EXTRACT for status is working correctly

## Solution Approach
1. Fix the SQL query in `get_admin_programs_list()` if needed
2. Ensure proper data is returned for button logic
3. Add fallback logic if submissions don't exist but programs should still show buttons
4. Test thoroughly with different program states

## Testing Plan
- [ ] Create a test program with a submission
- [ ] Verify buttons appear for draft submissions
- [ ] Verify buttons appear for final submissions
- [ ] Test with programs that have no submissions
- [ ] Ensure buttons work correctly across different periods