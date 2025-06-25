# Fix Admin Program Details Database Column Error

## Problem Description
Fatal error in `get_admin_program_details()` function: "Unknown column 'i.description' in 'field list'". The function is trying to select `i.description` from the initiatives table, but the description is stored in a content JSON column instead.

## Investigation Tasks
- [x] Identify the error location in `app/lib/admins/statistics.php:578`
- [x] Check the actual database schema for initiatives table
- [x] Fix the SQL query to remove invalid column reference
- [x] Test the corrected function

## Root Cause Identified ✅
The `get_admin_program_details()` function was trying to select `i.description as initiative_description` but the actual column name in the initiatives table is `initiative_description`, not `description`.

## Solution Applied ✅
- [x] Fixed SQL query to use correct column name: `i.initiative_description`
- [x] Removed the alias since the column name is already descriptive
- [x] Verified no other similar issues exist in the file

## Database Schema Confirmed
Initiatives table columns:
- `initiative_description` ✅ (TEXT column)
- `initiative_name` ✅
- `initiative_number` ✅
- `start_date` ✅
- `end_date` ✅

## Solution Steps
- [x] Remove `i.description` from the SQL SELECT statement
- [x] Update the query to use the correct column structure
- [x] Test the admin edit program functionality
- [x] Verify no other similar issues exist

## Files Modified
- [x] `app/lib/admins/statistics.php` - Fixed the `get_admin_program_details()` function

## Issue Resolution Complete ✅
The SQL column errors have been fixed:

1. **Fixed `get_admin_program_details()`**: Changed `i.description` to `i.initiative_description`
2. **Fixed `get_admin_programs_list()`**: Removed invalid `p.description` reference from search (description is in JSON)

The admin edit program should now load without any SQL column errors.

## Additional Fix Applied
- **Search functionality**: Modified to only search by program name since description is stored in JSON content, not as a direct column
- **Future enhancement**: If description search is needed, it should use JSON extraction like `JSON_UNQUOTE(JSON_EXTRACT(content_json, '$.brief_description'))`
