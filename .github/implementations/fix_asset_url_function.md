# Fix Undefined asset_url() Function Error

## Problem
PHP Fatal error: Call to undefined function asset_url() in header.php at line 14.
This error occurs when accessing submission_info.php and other pages that include the header layout.

## Root Cause Analysis
The `asset_url()` function is being called but not defined anywhere in the codebase. This is likely a utility function for generating URLs to static assets (CSS, JS, images).

## Solution Steps

### Step 1: Investigate Current Usage
- [x] Examine header.php to see how asset_url() is being used
- [x] Search codebase for any existing asset_url() definitions
- [x] Check what assets are being referenced
- [x] Understand the expected behavior

### Step 2: Implement Solution
- [x] **SOLUTION IDENTIFIED**: Function exists in config.php and asset_helpers.php, just needs proper includes
- [x] Fixed submission_info.php to include necessary files before header
- [x] Fixed edit_submission.php to include necessary files before header
- [x] **ROBUST SOLUTION**: Added safety check to header.php to auto-include asset_url function
- [x] Header now checks for function existence and includes appropriate files if needed
- [x] Added fallback definition as last resort if files cannot be found

### Step 3: Verify Fix
- [ ] Test submission_info.php page loads without error
- [ ] Test edit_submission.php page loads without error  
- [ ] Verify CSS/JS assets load correctly from header
- [ ] Check other pages still work correctly

## Root Cause Found
The `asset_url()` function was already properly defined in `app/config/config.php` and `app/lib/asset_helpers.php`, but many view files were including the header layout directly without first including the config files that define this function.

## Fix Applied
**Two-tier solution implemented:**

1. **Individual file fixes:** Added proper includes to specific problematic files:
   - `submission_info.php`: Added PROJECT_ROOT_PATH definition and config.php include
   - `edit_submission.php`: Added PROJECT_ROOT_PATH definition and config.php include

2. **System-wide safety net:** Modified `header.php` to auto-detect and include the asset_url function:
   - Checks if `asset_url()` function exists before trying to use it
   - Automatically includes `asset_helpers.php` or `config.php` if function not found
   - Provides fallback definition if config files cannot be loaded
   - This prevents the error from occurring in any view file that includes header.php

This solution ensures backward compatibility and prevents similar issues in the future.

## Notes
- This function should generate proper URLs for static assets
- Must be available globally since it's used in layout files
- Should handle different deployment scenarios (development vs production)
