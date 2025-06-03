# Fixing Errors After Removing Status and Description Columns

## Problem Description
The user has deleted the `status` and `description` columns from the `programs submission` table and `programs` table, which is causing errors in the application. The specific error is:

```
[01-Jun-2025 07:09:52 UTC] Error in get_all_sectors_programs: Unknown column 'ps1.status' in 'field list'
```

## Steps to Fix

1. [x] Use DBCode to check the current structure of the `program_submissions` and `programs` tables to confirm the column removal
2. [x] Find the `get_all_sectors_programs` function in the codebase
3. [x] Modify the function to remove references to the deleted `status` and `description` columns
4. [x] Search for other functions that might be using these deleted columns
5. [x] Fix those functions as well to ensure the application works correctly
6. [ ] Test the changes to make sure all errors are resolved

## Implementation Progress

### Changes Made:
1. In `app/lib/agencies/statistics.php`:
   - Removed references to `description` column in the SELECT clause
   - Removed `ps1.status` column from the subquery
   - Updated search filter to only search by program name since description column was removed
   - Modified the status filter to use JSON_EXTRACT to get status from content_json
   - Fixed spacing in the GROUP BY clause

2. In `app/lib/admins/statistics.php`:
   - Removed references to `p.description` column in the SELECT clause
   - Updated search filter to only search by program name
   - Modified queries to use JSON_EXTRACT to get status from content_json instead of the removed status column

### Next Steps:
- Test the solution to ensure it works without errors
- Monitor the application for any other related issues that might arise
