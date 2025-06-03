# Fix Program Period Assignment Issue

## Problem Statement
When creating a new program on the agency side, the program submission is automatically assigned to period_id 11, which is not the desired behavior. The system should assign programs to the current active/open reporting period.

## Investigation Plan

- [ ] 1. Check the current reporting periods in the database to understand what period_id 11 represents
- [ ] 2. Examine the `create_wizard_program_draft` function in `app/lib/agencies/programs.php` to see how period_id is determined
- [ ] 3. Check the `get_current_reporting_period()` function to see if it's returning the correct period
- [ ] 4. Verify the logic for determining which period should be "open" vs "closed"
- [ ] 5. Check if there are multiple open periods causing confusion
- [ ] 6. Implement a fix to ensure programs are assigned to the correct current period
- [ ] 7. Test the fix to ensure it works as expected

## Root Cause Analysis
- Need to identify why period_id 11 is being selected instead of the current active period
- Check if the query for finding open periods is working correctly
- Verify if the period management logic is functioning properly

## Solution Approach
- Ensure the `get_current_reporting_period()` function returns the truly current period
- Update the program creation logic if necessary to use the correct period
- Add validation to prevent assignment to incorrect periods

## Testing Plan
- [ ] Create a test program and verify it gets assigned to the correct period
- [ ] Check that the period assignment logic works for different scenarios
- [ ] Ensure backward compatibility with existing programs