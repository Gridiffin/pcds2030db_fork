# Fix PHP Warnings in Admin View Program

## Problem Description

The admin view program page (`app/views/admin/programs/view_program.php`) is generating PHP warnings and deprecated function calls:

1. **Line 197**: Undefined array key "sector_name" - The program array doesn't have sector_name field
2. **Line 197**: Deprecated htmlspecialchars() warning - passing null to htmlspecialchars() is deprecated
3. **Line 176**: Undefined array key "is_assigned" - The program array doesn't have is_assigned field

## Additional Issue Found

4. **Fatal Error**: Table 'pcds2030_db.sectors' doesn't exist - The sectors functionality has been removed from the system but SQL query still references it

## Additional Issues Found (Continued)

5. **Line 349**: Undefined array key "rating" in related programs section
6. **Line 154**: Deprecated strtolower() warning - passing null to parameter in rating_helpers.php

## Root Cause Analysis

- [x] The `get_admin_program_details()` function in `app/lib/admins/statistics.php` is not including sector information in its SQL query
- [x] The `get_admin_program_details()` function is not setting the `is_assigned` field based on program data
- [x] The view is not properly checking if array keys exist before using them
- [x] The SQL query references a non-existent `sectors` table - sectors functionality has been removed/disabled
- [x] The view accesses undefined `rating` key in related programs without null checks
- [x] The `convert_legacy_rating()` function receives null values causing deprecated strtolower() warnings

## Implementation Steps

### Step 1: Fix get_admin_program_details function

- [x] Update the SQL query to include sector information
- [x] Add logic to set the `is_assigned` field based on program data
- [x] Ensure all necessary fields are properly populated

### Step 2: Fix view_program.php template

- [x] Add proper array key existence checks before accessing array elements
- [x] Use null coalescing operators to handle missing fields gracefully
- [x] Ensure htmlspecialchars() doesn't receive null values

### Step 3: Test the fixes

- [x] Verify that no PHP warnings are generated
- [x] Confirm that program information displays correctly
- [x] Test with both assigned and agency-created programs

### Step 4: Fix sectors table reference

- [x] Remove reference to non-existent sectors table from SQL query
- [x] Update view to show "Forestry" as default sector (as per system context)

### Step 5: Fix rating field warnings

- [x] Add null coalescing operator for related programs rating field
- [x] Update convert_legacy_rating() function to handle null values properly
- [x] Fix deprecated strtolower() warning with proper type casting

## Files to Modify

1. `app/lib/admins/statistics.php` - Fix get_admin_program_details function ✅
2. `app/views/admin/programs/view_program.php` - Add proper error handling ✅
3. `app/views/admin/programs/delete_program.php` - Remove sectors table reference ✅
4. `app/views/admin/programs/edit_program_backup.php` - Remove sectors table reference ✅
5. `app/lib/rating_helpers.php` - Fix null value handling in convert_legacy_rating function ✅
6. `app/views/agency/initiatives/view_initiative.php` - Add null coalescing operators for rating fields ✅
7. `app/lib/status_helpers.php` - Add null coalescing operator for status parameter ✅

## Expected Outcome

- No PHP warnings or deprecated function calls ✅
- No fatal errors from missing database tables ✅
- Proper display of sector information (hardcoded to "Forestry") ✅
- Correct identification of assigned vs agency-created programs ✅
- Robust error handling for missing data ✅

## Changes Made

### app/lib/admins/statistics.php

- ~~Added `s.sector_name` to the SELECT query with proper LEFT JOIN to sectors table~~ REVERTED
- Removed reference to non-existent `sectors` table from SQL query
- Added logic to set `is_assigned` field based on the presence of edit_permissions

### app/views/admin/programs/view_program.php

- Fixed line 176: Added proper check for `is_assigned` key existence
- ~~Fixed line 197: Added null coalescing operator for `sector_name`~~ UPDATED
- Fixed line 197: Changed to show "Forestry" as default sector (sectors functionality removed)
- Fixed line 349: Added null coalescing operator for related programs rating field
- Added defensive programming for `agency_name`, `initiative_name`, `initiative_number`, `initiative_description`, and submission achievement fields
- All htmlspecialchars() calls now use null coalescing operators to prevent deprecated warnings

### app/views/admin/programs/delete_program.php

- Removed reference to non-existent `sectors` table from SQL query

### app/views/admin/programs/edit_program_backup.php

- Removed reference to non-existent `sectors` table from SQL query

### app/lib/rating_helpers.php

- Added null/empty value check at the beginning of `convert_legacy_rating()` function
- Fixed deprecated strtolower() warning by adding proper type casting to string
- Enhanced function to handle null values gracefully and return 'not_started' as default

### app/views/agency/initiatives/view_initiative.php

- Added null coalescing operators for all `convert_legacy_rating($program['rating'])` calls
- Fixed three instances where rating field could be null causing deprecated warnings

### app/lib/status_helpers.php

- Added null coalescing operator in `get_status_display_name()` function
- Enhanced function to handle null status parameters gracefully

## Additional Fixes Applied

### SQL Query Fixes (Related Issue)

Fixed multiple SQL queries that were incorrectly trying to select `agency_name` from the `users` table. The correct approach is to JOIN the `users` table with the `agency` table since `agency_name` exists in the `agency` table.

**Files Fixed:**

- `app/views/admin/programs/edit_program.php` - Fixed two SQL queries (lines 511 and 373)
- `app/views/admin/programs/edit_program_backup.php` - Fixed agencies query
- `app/views/admin/programs/assign_programs.php` - Fixed agency name lookup query
- `app/lib/admins/agencies.php` - Fixed get_all_agency_users function

**Query Pattern Fixed:**

```sql
-- BEFORE (Incorrect)
SELECT user_id, agency_name FROM users WHERE...

-- AFTER (Correct)
SELECT u.user_id, a.agency_name
FROM users u
JOIN agency a ON u.agency_id = a.agency_id
WHERE...
```

## Context Notes

- The sectors functionality has been removed/disabled from the system as per system_context.txt
- The current implementation focuses exclusively on the Forestry sector
- Multi-sector functionality remains in codebase but is disabled via MULTI_SECTOR_ENABLED flag
