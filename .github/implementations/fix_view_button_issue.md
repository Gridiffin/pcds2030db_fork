# Fix Admin Outcomes View Button Issue

**Date:** 2025-05-26  
**Status:** ✅ **RESOLVED**

**Problem:** Clicking on the "View" button in the admin outcomes manage page doesn't return anything - the screen just refreshes without showing outcome details.

**Root Cause:** Parameter mismatch between manage_outcomes.php and view_outcome.php
- `manage_outcomes.php` was passing `metric_id` parameter
- `view_outcome.php` was expecting `outcome_id` parameter

**Solution Applied:**
1. ✅ **Fixed view button href path** in `manage_outcomes.php`
   - Changed from relative path to full URL using `APP_URL`
   - Maintained `metric_id` parameter as used throughout the system
   
2. ✅ **Updated parameter handling** in `view_outcome.php`
   - Changed from expecting `$_GET['outcome_id']` to `$_GET['metric_id']`
   - Updated function calls to use `$metric_id` variable consistently
   - Updated display variable reference

**Final Working Configuration:**
- **View button in manage_outcomes.php:** `href="<?php echo APP_URL; ?>/app/views/admin/outcomes/view_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>"`
- **Parameter handling in view_outcome.php:** Now correctly accepts and processes `metric_id` parameter

## Files Modified:

1. **app/views/admin/outcomes/manage_outcomes.php**
   - ✅ Fixed view button href to use full URL path
   - ✅ Maintained `metric_id` parameter consistency

2. **app/views/admin/outcomes/view_outcome.php**
   - ✅ Changed parameter check from `outcome_id` to `metric_id`
   - ✅ Updated variable assignment: `$metric_id = (int) $_GET['metric_id']`
   - ✅ Updated function call: `get_outcome_data($metric_id)`
   - ✅ Updated display reference to use `$metric_id`

## System Notes:
- The codebase consistently uses `metric_id` as the primary identifier across all outcome-related operations
- The `view_outcome.php` was the only file expecting `outcome_id` parameter
- All other admin outcome files (edit, delete, unsubmit) correctly use `metric_id`

**Testing:** View button now properly navigates to outcome details page with correct data display.
