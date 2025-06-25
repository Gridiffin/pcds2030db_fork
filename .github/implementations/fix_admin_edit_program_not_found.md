# Fix Admin Edit Program "Program Not Found" Error

## Problem Description
When clicking the edit button for programs in the admin interface, the error "Program not found" appears, preventing access to the edit program functionality.

## Investigation Tasks
- [x] Check how the edit button links are generated
- [x] Verify the program ID parameter is being passed correctly
- [x] Examine the `get_program_details()` function with cross-agency access
- [x] Check if the program ID validation is working correctly
- [x] Test the database query that retrieves program details

## Root Cause Identified ✅
The issue was that the admin edit program was using `get_program_details($program_id, true)` from `app/lib/agencies/programs.php`, but this function has a check `if (!is_agency())` that returns false for admin users. Admin users are not agencies, so the function was failing and returning false.

## Solution Applied ✅
- [x] Added include for `app/lib/admins/statistics.php` 
- [x] Changed from `get_program_details($program_id, true)` to `get_admin_program_details($program_id)`
- [x] The admin function has no agency restrictions and works for admin users

## Files Modified
- [x] `app/views/admin/programs/edit_program.php` - Fixed function call and added proper include

## Solution Steps
- [x] Identify the root cause of the "Program not found" error
- [x] Fix the underlying issue
- [x] Test the edit functionality
- [x] Clean up any test files

## Issue Resolution Complete ✅
The "Program not found" error has been fixed by using the correct admin-specific function instead of the agency-specific function. The admin edit program functionality should now work correctly.

## Recommendation
Other admin program-related files should be checked to ensure they also use `get_admin_program_details()` instead of `get_program_details()` where appropriate. This includes:
- `app/views/admin/programs/view_program.php`
- `app/views/admin/programs/reopen_program.php` 
- Any other admin files that access program details

## Key Takeaway
- **Agency functions** (in `app/lib/agencies/`) have `is_agency()` checks and should only be used by agency users
- **Admin functions** (in `app/lib/admins/`) are designed for admin users and don't have agency restrictions
- Always use the appropriate function for the user type accessing the functionality
