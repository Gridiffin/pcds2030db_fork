# Fix Admin Program List - Simple Logic & Remove Deleted Columns

## Problem Statement
The unsubmit/resubmit buttons are not showing in the admin program list. The issue is likely caused by:
1. References to deleted `status` and `description` columns
2. Overly complex button logic
3. Draft programs being shown when they shouldn't be

## New Simple Logic Requirements
- **No submissions**: No buttons needed
- **Draft submissions**: Don't show the program at all
- **Final submissions**: Show Unsubmit button only

## Investigation & Fix Plan

- [ ] 1. Remove all references to deleted `status` column from SQL queries
- [ ] 2. Remove all references to deleted `description` column from SQL queries  
- [ ] 3. Filter out draft programs from the admin list (is_draft = 1)
- [ ] 4. Simplify button logic: Only show Unsubmit button for final submissions
- [ ] 5. Update the admin programs view to handle the simplified logic
- [ ] 6. Test the changes
- [ ] 7. Clean up debug files

## Files to Check & Fix

### SQL Queries (Remove status/description references)
- [ ] `app/lib/admins/statistics.php` - `get_admin_programs_list()` function
- [ ] Any other admin functions that query programs

### View Files (Update button logic)
- [ ] `app/views/admin/programs/programs.php` - Main program list
- [ ] Remove complex button conditions
- [ ] Add filter for draft programs

### Logic Changes
- [ ] Filter: `WHERE ps.is_draft = 0 OR ps.is_draft IS NULL`
- [ ] Button: Only show Unsubmit for programs with final submissions

## Expected Outcome
- Admin sees only programs with final submissions or no submissions
- Unsubmit button appears only for programs with final submissions
- No more SQL errors from missing columns
- Cleaner, simpler logic