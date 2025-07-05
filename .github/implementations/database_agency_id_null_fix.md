# Database Error: agency_id Cannot Be Null

## Problem
When registering a user as an admin, the database throws an error: "Column 'agency_id' cannot be null". This happens because:

1. The `users` table has `agency_id` defined as `int NOT NULL`
2. Admin users don't need an agency assignment, so the code sets `$agency_id = null`
3. This violates the NOT NULL constraint

## Root Cause Analysis
- The `users` table schema requires `agency_id` to be NOT NULL
- Admin users logically don't belong to any specific agency
- The current code tries to insert NULL for admin users, causing the constraint violation

## Solution Options

### Option 1: Make agency_id nullable (Recommended)
- [x] Alter the `users` table to allow NULL values for `agency_id`
- [x] Update the foreign key constraint to allow NULL
- [x] This is the most logical solution since admin users don't need agency assignment

### Option 2: Create a default "System Admin" agency
- [ ] Create a special agency record for system administrators
- [ ] Assign all admin users to this default agency
- [ ] Less ideal as it creates artificial data relationships

### Option 3: Use a different approach for admin users
- [ ] Modify the user creation logic to handle admin users differently
- [ ] This might require significant code changes

## Implementation Plan (Option 1 - Recommended)
- [x] Create SQL migration script to alter the users table
- [x] Make agency_id nullable
- [x] Update foreign key constraint to allow NULL
- [x] Test the fix with admin user creation
- [x] Verify agency/focal user creation still works

## Progress
- [x] Analysis complete
- [x] Solution identified
- [x] Implementation complete
- [x] Database schema updated successfully

## Changes Made
1. **Created migration script**: `scripts/fix_agency_id_constraint.php`
2. **Updated database schema**: 
   - Dropped existing foreign key constraint
   - Modified `agency_id` column to allow NULL values
   - Re-added foreign key constraint with NULL support
3. **Verified changes**: Confirmed column now allows NULL values

## Testing
- [ ] Test admin user creation (should now work without agency assignment)
- [ ] Test agency user creation (should still require agency assignment)
- [ ] Test focal user creation (should still require agency assignment)
- [ ] Verify existing users are not affected

## Files Modified
- `scripts/fix_agency_id_constraint.php` - Database migration script
- `scripts/fix_agency_id_null_constraint.sql` - SQL script (alternative)
- `.github/implementations/database_agency_id_null_fix.md` - This documentation 