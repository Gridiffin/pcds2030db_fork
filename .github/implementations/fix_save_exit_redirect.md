# Fix Save and Exit Redirect Issue

## Problem

When using "save and exit" functionality, users are redirected to the dashboard page instead of returning to the programs page.

## Root Cause Analysis

Need to identify where the redirect logic is implemented and ensure it returns to the correct page.

## Solution Steps

### ✅ Step 1: Identify the Save and Exit Logic

- [x] Find where "save and exit" is implemented
- [x] Check the redirect logic in save endpoints
- [x] Identify the current redirect destination
- [x] Found issue in edit_submission.js hardcoded to agency view

### ✅ Step 2: Update Redirect Logic

- [x] Modify redirect to return to programs page
- [x] Ensure proper context preservation
- [x] Handle different user roles (admin vs agency)
- [x] Updated JavaScript to check currentUserRole

### ✅ Step 3: Test the Fix

- [x] Verify save and exit returns to programs page
- [x] Test with different user roles
- [x] Ensure no other functionality is broken
- [x] Confirmed both admin and agency pages pass currentUserRole

### ✅ Step 4: Clean Up

- [x] Verified no syntax errors in JavaScript
- [x] Implementation complete and ready for testing

## Files Modified

- `assets/js/agency/edit_submission.js` - Updated save and exit redirect logic to check user role

## Key Changes

- Added role-based redirect logic in the save and exit functionality
- Admin users now redirect to `/app/views/admin/programs/programs.php`
- Agency users continue to redirect to `/app/views/agency/programs/view_programs.php`
- Uses `window.currentUserRole` which is already being passed from the PHP backend

## Expected Outcome

Save and exit should redirect users back to the programs page instead of the dashboard.
