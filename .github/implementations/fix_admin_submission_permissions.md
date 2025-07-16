# Fix Admin Submission Editing Permissions

## Problem

When admin users try to edit submissions, they get "Access denied. Agency login required" error. Admins should have full access to edit any submission regardless of agency permissions.

## Root Cause Analysis

The submission editing functionality is likely checking for agency-level permissions instead of recognizing admin privileges.

## Solution Steps

### ✅ Step 1: Identify the Permission Check Issue

- [x] Examine the edit submission page for permission checks
- [x] Check AJAX endpoints for submission data loading
- [x] Identify where "Agency login required" error is generated
- [x] Found issues in multiple AJAX endpoints checking only for `is_agency()`

### ✅ Step 2: Update Permission Logic

- [x] Modified permission checks to allow admin access
- [x] Ensure admin users bypass agency-specific restrictions
- [x] Updated AJAX endpoints that load submission data
- [x] Updated program details fetching to use admin functions when needed

### ⬜ Step 3: Test Admin Access

- [ ] Verify admin can edit submissions from any agency
- [ ] Ensure no unintended access is granted to non-admin users
- [ ] Test various submission states (draft, submitted, etc.)

### ⬜ Step 4: Clean Up and Document

- [ ] Remove any debug files
- [ ] Update documentation if needed

## Files Modified

- `app/ajax/get_submission_by_period.php` - Updated to allow admin access
- `app/ajax/save_submission.php` - Updated to allow admin access
- `app/ajax/get_program_submission.php` - Updated to allow admin access
- `app/ajax/get_target_progress.php` - Updated to allow admin access
- `app/ajax/get_reporting_periods.php` - Updated to allow admin access

All files now:

- Include `lib/admins/core.php` for `is_admin()` function
- Include `lib/admins/statistics.php` for `get_admin_program_details()` function
- Check for both `is_agency()` and `is_admin()` permissions
- Use appropriate program details function based on user role

## Expected Outcome

Admin users should be able to edit any submission from any agency without permission restrictions.
